<script setup>
import { marked } from "marked";
import { ref, computed, onMounted, watch } from "vue";

// ... (existing code)

// Helper to parse message content into text and JSON segments
const parseMessage = (content) => {
    if (!content) return [];

    // Regex to find JSON code blocks: ```json ... ```
    const regex = /```json\s*([\s\S]*?)\s*```/g;
    const segments = [];
    let lastIndex = 0;
    let match;

    while ((match = regex.exec(content)) !== null) {
        // Add text before the JSON block
        if (match.index > lastIndex) {
            segments.push({
                type: "text",
                content: content.slice(lastIndex, match.index),
            });
        }

        // Try to parse JSON
        try {
            const jsonData = JSON.parse(match[1]);
            segments.push({
                type: "json",
                data: jsonData,
            });
        } catch (e) {
            // If parse fails, treat as text
            segments.push({
                type: "text",
                content: match[0],
            });
        }

        lastIndex = regex.lastIndex;
    }

    // Add remaining text
    if (lastIndex < content.length) {
        segments.push({
            type: "text",
            content: content.slice(lastIndex),
        });
    }

    return segments;
};

import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import "leaflet/dist/leaflet.css";
import {
    LMap,
    LTileLayer,
    LGeoJson,
    LMarker,
    LTooltip,
    LIcon,
    LPopup,
} from "@vue-leaflet/vue-leaflet";
import japanGeoJson from "../../data/japan_map.json";
import worldGeoJson from "../../data/world_map.json";
import L from "leaflet";

const props = defineProps({
    mapData: Object, // {'JP-01': {count: 5, dates: [...]}, ...}
    pinnedLocations: Object, // {'JP-01': {users: ['Taisuke', 'Hanako'], has_me: true}, ...}
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const zoom = ref(5);
const center = ref([38.0, 137.0]); // Center of Japan
const bounds = ref(null);
const prefectureCenters = ref({});
const prefectureNames = ref({}); // Store names for tooltips on markers
const currentMap = ref("JP"); // 'JP' or 'World'

// AI Planner Modal State
const showAiPlanner = ref(false);
const selectedPrefectureForAi = ref(null);
const aiChatMessages = ref([]);
const aiChatInput = ref("");
const isAiProcessing = ref(false);

// Calculate centers for markers
const calculateCenters = (geojson) => {
    const centers = {};
    const names = {};
    geojson.features.forEach((feature) => {
        const id = feature.properties.id; // For Japan
        const iso = feature.id; // For World (ISO 3166-1 alpha-3 usually, but let's check data)

        let code = "";
        let name = "";
        if (currentMap.value === "JP") {
            code = "JP-" + String(id).padStart(2, "0");
            name = feature.properties.nam_ja;
        } else {
            code = iso; // Use ISO code for world
            name = feature.properties.name;
        }

        names[code] = name;

        // Simple centroid calculation
        let lat = 0,
            lng = 0,
            points = 0;
        const processPolygon = (coords) => {
            coords.forEach((coord) => {
                lng += coord[0];
                lat += coord[1];
                points++;
            });
        };

        if (feature.geometry.type === "Polygon") {
            feature.geometry.coordinates.forEach((ring) =>
                processPolygon(ring)
            );
        } else if (feature.geometry.type === "MultiPolygon") {
            feature.geometry.coordinates.forEach((polygon) => {
                polygon.forEach((ring) => processPolygon(ring));
            });
        }

        if (points > 0) {
            centers[code] = [lat / points, lng / points];
        }
    });
    return { centers, names };
};

onMounted(() => {
    const result = calculateCenters(japanGeoJson);
    prefectureCenters.value = result.centers;
    prefectureNames.value = result.names;
});

// Watch for map switch
watch(currentMap, (newVal) => {
    if (newVal === "JP") {
        zoom.value = 5;
        center.value = [38.0, 137.0];
        const result = calculateCenters(japanGeoJson);
        prefectureCenters.value = result.centers;
        prefectureNames.value = result.names;
    } else {
        zoom.value = 2;
        center.value = [20.0, 0.0];
        const result = calculateCenters(worldGeoJson);
        prefectureCenters.value = result.centers;
        prefectureNames.value = result.names;
    }
});

const currentGeoJson = computed(() => {
    return currentMap.value === "JP" ? japanGeoJson : worldGeoJson;
});

// Map style function
const styleFunction = (feature) => {
    let code = "";
    if (currentMap.value === "JP") {
        const id = feature.properties.id;
        code = "JP-" + String(id).padStart(2, "0");
    } else {
        code = feature.id;
    }

    const data = props.mapData[code];
    const count = data ? data.count : 0;

    let fillColor = getColor(count);
    let weight = 1;
    let color = "white";
    let fillOpacity = 0.9;

    return {
        weight: weight,
        color: color,
        opacity: 1,
        fillColor: fillColor,
        fillOpacity: fillOpacity,
    };
};

// Color scale based on visit count
const getColor = (count) => {
    if (count === 0) return "#f3f4f6"; // Gray-100 (Unvisited)
    if (count === 1) return "#93c5fd"; // Blue-300
    if (count === 2) return "#60a5fa"; // Blue-400
    if (count === 3) return "#3b82f6"; // Blue-500
    if (count >= 4) return "#fbbf24"; // Amber-400 (Master)
    return "#f3f4f6";
};

// Toggle Pin
const togglePin = (code) => {
    // Only support pins for Japan for now as DB schema expects JP-xx
    if (currentMap.value !== "JP") return;

    const pinInfo = props.pinnedLocations[code];
    const hasMe = pinInfo ? pinInfo.has_me : false;

    if (hasMe) {
        router.delete(route("map.pin.destroy"), {
            data: { prefecture_code: code },
            preserveScroll: true,
            preserveState: true,
        });
    } else {
        router.post(
            route("map.pin.store"),
            {
                prefecture_code: code,
            },
            {
                preserveScroll: true,
                preserveState: true,
            }
        );
    }
};

const chatPollingInterval = ref(null);
const chatContainer = ref(null);
const modalSizeIndex = ref(0); // 0: Small, 1: Medium, 2: Large

const modalWidthClass = computed(() => {
    switch (modalSizeIndex.value) {
        case 0:
            return "sm:max-w-lg";
        case 1:
            return "sm:max-w-2xl";
        case 2:
            return "sm:max-w-4xl";
        default:
            return "sm:max-w-lg";
    }
});

const chatHeightClass = computed(() => {
    switch (modalSizeIndex.value) {
        case 0:
            return "h-64";
        case 1:
            return "h-96";
        case 2:
            return "h-[500px]";
        default:
            return "h-64";
    }
});

const increaseModalSize = () => {
    if (modalSizeIndex.value < 2) modalSizeIndex.value++;
};

const decreaseModalSize = () => {
    if (modalSizeIndex.value > 0) modalSizeIndex.value--;
};

// Scroll to bottom
const scrollToBottom = () => {
    if (chatContainer.value) {
        setTimeout(() => {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }, 100);
    }
};

// Fetch Chat History
const fetchChatHistory = async (code) => {
    try {
        const response = await axios.get(
            route("ai-planner.index", { prefectureCode: code })
        );
        const history = response.data;

        if (history.length === 0 && aiChatMessages.value.length === 0) {
            // Initial greeting if no history
            aiChatMessages.value = [
                {
                    role: "system",
                    content: `「${selectedPrefectureForAi.value.name}」への旅行計画を立てましょう！どのような旅行にしたいですか？`,
                },
            ];
        } else if (history.length > 0) {
            // Update messages, preserving local optimistic updates if needed, but for now just replace
            // To avoid jumping, maybe only append? But simple replacement is safer for sync.
            aiChatMessages.value = history;
        }
        scrollToBottom();
    } catch (error) {
        console.error("Failed to fetch chat history", error);
    }
};

// Open AI Planner
const openAiPlanner = async (code, name) => {
    selectedPrefectureForAi.value = { code, name };
    showAiPlanner.value = true;
    aiChatMessages.value = [];

    await fetchChatHistory(code);

    // Start Polling
    if (chatPollingInterval.value) clearInterval(chatPollingInterval.value);
    chatPollingInterval.value = setInterval(() => {
        if (showAiPlanner.value && selectedPrefectureForAi.value) {
            fetchChatHistory(selectedPrefectureForAi.value.code);
        }
    }, 5000); // Poll every 5 seconds
};

// Close AI Planner
const closeAiPlanner = () => {
    showAiPlanner.value = false;
    if (chatPollingInterval.value) {
        clearInterval(chatPollingInterval.value);
        chatPollingInterval.value = null;
    }
    selectedPrefectureForAi.value = null;
};

// Send Message to AI
const sendAiMessage = async (triggerAi = true) => {
    if (!aiChatInput.value.trim()) return;

    const message = aiChatInput.value;
    aiChatInput.value = ""; // Clear input immediately

    // Optimistic UI update
    aiChatMessages.value.push({
        id: "temp-" + Date.now(),
        user_id: user.value.id,
        user_name: user.value.name,
        message: message,
        content: message, // For display compatibility
        is_ai: false,
        is_me: true,
        created_at: new Date().toISOString(),
    });

    scrollToBottom();

    if (triggerAi) {
        isAiProcessing.value = true;
        scrollToBottom();
    }

    try {
        await axios.post(
            route("ai-planner.store", {
                prefectureCode: selectedPrefectureForAi.value.code,
            }),
            {
                message: message,
                trigger_ai: triggerAi,
            }
        );

        // Fetch latest history to get AI response and sync
        await fetchChatHistory(selectedPrefectureForAi.value.code);
    } catch (error) {
        console.error("Failed to send message:", error);
        // Revert optimistic update or show error?
        // For now, just logging.
    } finally {
        if (triggerAi) {
            isAiProcessing.value = false;
        }
        scrollToBottom();
    }
};

// Helper to generate tooltip HTML
const getTooltipContent = (code, name) => {
    const data = props.mapData[code];
    const count = data ? data.count : 0;
    const dates = data ? data.dates : [];

    const pinInfo = props.pinnedLocations[code];
    const hasMe = pinInfo ? pinInfo.has_me : false;
    const users = pinInfo ? pinInfo.users : [];

    // Build Tooltip HTML
    let datesHtml = "";
    if (dates.length > 0) {
        datesHtml = '<div class="mt-2 text-xs text-gray-500 border-t pt-1">';
        dates.forEach((date, index) => {
            datesHtml += `<div>${index + 1}回目: ${date}</div>`;
        });
        datesHtml += "</div>";
    }

    // Pin Info & Buttons
    let pinHtml = "";
    if (currentMap.value === "JP") {
        // Who wants to go?
        if (users.length > 0) {
            pinHtml +=
                '<div class="mt-2 pt-1 border-t border-gray-100 text-xs">';
            pinHtml +=
                '<div class="font-bold text-amber-600 mb-1">行きたい人:</div>';
            pinHtml += '<div class="flex flex-wrap gap-1">';
            users.forEach((user) => {
                pinHtml += `<span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded-full text-[10px]">${user}</span>`;
            });
            pinHtml += "</div></div>";
        }

        // Action Buttons
        pinHtml += '<div class="mt-2 flex flex-col gap-2">';

        // Pin/Unpin Button
        const pinBtnText = hasMe
            ? "★ ピンを外す"
            : users.length > 0
            ? "🙋‍♂️ 私も行きたい！"
            : "☆ 行きたい場所に追加";
        const pinBtnClass = hasMe
            ? "bg-gray-100 text-gray-600 hover:bg-gray-200"
            : "bg-amber-50 text-amber-600 hover:bg-amber-100";

        // Note: We can't easily bind @click in HTML string for Leaflet tooltip.
        // We rely on the marker click for toggling pin, but for specific buttons inside tooltip, it's tricky.
        // For now, we'll keep the marker click as the main toggle, but maybe we can add a hint.
        // Actually, user requested "Click star -> Tooltip -> Click button".
        // Let's use a Popup instead of Tooltip for the star marker? Or just keep Tooltip interactive.
        // Since we are using `bindTooltip` with `interactive: true`, links/buttons inside should work if we can attach event listeners.
        // But Vue event binding won't work in raw HTML string.
        // A workaround is to use `window.handlePinClick` etc., but that's messy.
        // Alternative: Use `LPopup` component inside `LMarker` in the template! Much better.
    }

    return ""; // We will use LPopup in template instead of bindTooltip string for complex interaction
};

// We will switch to using LPopup in the template for the markers to allow Vue components inside.
// But for the geojson layer (prefectures), we still use bindTooltip for simple info.
const onEachFeature = (feature, layer) => {
    let code = "";
    let name = "";

    if (currentMap.value === "JP") {
        const id = feature.properties.id;
        code = "JP-" + String(id).padStart(2, "0");
        name = feature.properties.nam_ja;
    } else {
        code = feature.id;
        name = feature.properties.name;
    }

    // Simple tooltip for hovering over the map (prefecture shapes)
    const data = props.mapData[code];
    const count = data ? data.count : 0;

    const simpleContent = `
        <div class="text-center">
            <div class="font-bold">${name}</div>
            <div class="text-xs">${
                count > 0 ? count + "回訪問" : "未訪問"
            }</div>
        </div>
    `;

    layer.bindTooltip(simpleContent, {
        permanent: false,
        sticky: true,
        direction: "top",
        className: "simple-tooltip",
    });

    // Handle Click on Layer -> Toggle Pin (Quick action)
    layer.on({
        mouseover: (e) => {
            const layer = e.target;
            layer.setStyle({ weight: 2, color: "#666", fillOpacity: 1 });
            layer.bringToFront();
        },
        mouseout: (e) => {
            const layer = e.target;
            const style = styleFunction(feature);
            layer.setStyle(style);
        },
        click: (e) => {
            // If clicking the prefecture map, just toggle pin for me (quick action)
            togglePin(code);
        },
    });
};
</script>

<template>
    <Head title="制覇マップ" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Hero / Header -->
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-3xl mb-8 border border-gray-100"
                >
                    <div
                        class="p-8 md:p-12 bg-gradient-to-r from-blue-50 to-indigo-50 relative overflow-hidden"
                    >
                        <div
                            class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-blue-200 rounded-full blur-3xl opacity-30"
                        ></div>
                        <div
                            class="absolute bottom-0 left-0 -mb-10 -ml-10 w-64 h-64 bg-indigo-200 rounded-full blur-3xl opacity-30"
                        ></div>

                        <div
                            class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-6"
                        >
                            <div>
                                <h1
                                    class="text-3xl md:text-4xl font-bold text-gray-800 mb-2 tracking-tight"
                                >
                                    <span class="text-blue-600">JAPAN</span>
                                    Conquest Map
                                </h1>
                                <p class="text-gray-600 text-lg">
                                    地図をクリックして「行きたい場所」をピン留めできます。<br />
                                    <span class="text-sm text-gray-500"
                                        >※
                                        訪問済みの場所は青色、行きたい場所は星マーク<span
                                            class="text-amber-400 text-xl"
                                            >★</span
                                        >で表示されます。</span
                                    >
                                </p>
                            </div>

                            <!-- Map Toggle & Legend -->
                            <div class="flex flex-col gap-4 items-end">
                                <!-- Toggle Switch -->
                                <div
                                    class="bg-gray-100 p-1 rounded-lg flex items-center"
                                >
                                    <button
                                        @click="currentMap = 'JP'"
                                        class="px-4 py-2 rounded-md text-sm font-bold transition-all"
                                        :class="
                                            currentMap === 'JP'
                                                ? 'bg-white text-blue-600 shadow-sm'
                                                : 'text-gray-500 hover:text-gray-700'
                                        "
                                    >
                                        日本地図
                                    </button>
                                    <button
                                        @click="currentMap = 'World'"
                                        class="px-4 py-2 rounded-md text-sm font-bold transition-all"
                                        :class="
                                            currentMap === 'World'
                                                ? 'bg-white text-blue-600 shadow-sm'
                                                : 'text-gray-500 hover:text-gray-700'
                                        "
                                    >
                                        世界地図
                                    </button>
                                </div>

                                <!-- Legend -->
                                <div
                                    class="flex flex-wrap gap-3 bg-white/60 backdrop-blur-sm p-4 rounded-2xl shadow-sm"
                                >
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="w-4 h-4 rounded bg-gray-100"
                                        ></span>
                                        <span class="text-xs text-gray-600"
                                            >未訪問</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-amber-400 text-lg leading-none"
                                            >★</span
                                        >
                                        <span class="text-xs text-gray-600"
                                            >行きたい</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="w-4 h-4 rounded bg-blue-300"
                                        ></span>
                                        <span class="text-xs text-gray-600"
                                            >1回</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="w-4 h-4 rounded bg-blue-400"
                                        ></span>
                                        <span class="text-xs text-gray-600"
                                            >2回</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="w-4 h-4 rounded bg-blue-500"
                                        ></span>
                                        <span class="text-xs text-gray-600"
                                            >3回</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="w-4 h-4 rounded bg-amber-400"
                                        ></span>
                                        <span class="text-xs text-gray-600"
                                            >4回+</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Container -->
                <div
                    class="bg-white shadow-xl sm:rounded-3xl overflow-hidden h-[600px] md:h-[700px] relative z-0"
                >
                    <l-map
                        ref="map"
                        v-model:zoom="zoom"
                        v-model:center="center"
                        :use-global-leaflet="false"
                        :options="{ scrollWheelZoom: false }"
                        class="z-0"
                    >
                        <!-- Tile Layer (Optional, for context) -->
                        <l-tile-layer
                            url="https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png"
                            layer-type="base"
                            name="CartoDB Positron"
                            attribution="&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors &copy; <a href='https://carto.com/attributions'>CARTO</a>"
                        ></l-tile-layer>

                        <!-- GeoJSON Layer -->
                        <l-geo-json
                            :geojson="currentGeoJson"
                            :options="{
                                style: styleFunction,
                                onEachFeature: onEachFeature,
                            }"
                        ></l-geo-json>

                        <!-- Star Markers for Pinned Locations (Using v-for on pinnedLocations) -->
                        <!-- Star Markers for Pinned Locations (Using v-for on pinnedLocations) -->
                        <l-marker
                            v-if="currentMap === 'JP'"
                            v-for="(pinInfo, code) in pinnedLocations"
                            :key="code"
                            :lat-lng="prefectureCenters[code] || [0, 0]"
                        >
                            <l-icon
                                :icon-size="[40, 40]"
                                :icon-anchor="[20, 20]"
                                class-name="star-marker-container"
                            >
                                <div class="star-marker-content">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        class="w-10 h-10 text-amber-400 drop-shadow-xl filter"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"
                                            clip-rule="evenodd"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 18.354L7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354z"
                                            class="stroke-white stroke-2 fill-none opacity-30"
                                        />
                                    </svg>
                                </div>
                            </l-icon>

                            <!-- Use LPopup for interactive content -->
                            <l-popup
                                :options="{ minWidth: 200, maxWidth: 300 }"
                                class="custom-popup-wrapper"
                            >
                                <div class="text-center p-1">
                                    <div
                                        class="font-bold text-lg mb-2 flex items-center justify-center gap-2"
                                    >
                                        {{ prefectureNames[code] }}
                                    </div>

                                    <!-- Visit Info -->
                                    <div class="text-sm mb-2">
                                        <span
                                            v-if="
                                                mapData[code] &&
                                                mapData[code].count > 0
                                            "
                                            class="text-blue-600 font-bold"
                                        >
                                            {{ mapData[code].count }}回 訪問
                                        </span>
                                        <span v-else class="text-gray-400"
                                            >未訪問</span
                                        >
                                    </div>

                                    <!-- Who wants to go? -->
                                    <div
                                        v-if="
                                            pinInfo.users &&
                                            pinInfo.users.length > 0
                                        "
                                        class="mb-3 bg-amber-50 p-2 rounded-lg border border-amber-100"
                                    >
                                        <div
                                            class="text-xs font-bold text-amber-600 mb-1 flex items-center justify-center gap-1"
                                        >
                                            行きたい人
                                        </div>
                                        <div
                                            class="flex flex-wrap justify-center gap-1"
                                        >
                                            <span
                                                v-for="user in pinInfo.users"
                                                :key="user"
                                                class="px-2 py-0.5 bg-white text-amber-700 rounded-full text-xs border border-amber-200 shadow-sm"
                                            >
                                                {{ user }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex flex-col gap-2">
                                        <!-- Toggle Pin Button -->
                                        <button
                                            @click.stop="togglePin(code)"
                                            class="w-full py-1.5 px-3 rounded-lg text-xs font-bold transition-colors flex items-center justify-center gap-1"
                                            :class="
                                                pinInfo.has_me
                                                    ? 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                                    : 'bg-amber-100 text-amber-700 hover:bg-amber-200'
                                            "
                                        >
                                            <span v-if="pinInfo.has_me"
                                                >★ ピンを外す</span
                                            >
                                            <span v-else
                                                >🙋‍♂️ 私も行きたい！</span
                                            >
                                        </button>

                                        <!-- AI Chat Button (Only if 2+ users) -->
                                        <button
                                            v-if="pinInfo.users.length >= 2"
                                            @click.stop="
                                                openAiPlanner(
                                                    code,
                                                    prefectureNames[code]
                                                )
                                            "
                                            class="w-full py-1.5 px-3 rounded-lg text-xs font-bold bg-gradient-to-r from-indigo-500 to-purple-600 text-white hover:from-indigo-600 hover:to-purple-700 shadow-sm flex items-center justify-center gap-1 animate-pulse-slow"
                                        >
                                            🤖 AIチャットで相談
                                        </button>
                                    </div>
                                </div>
                            </l-popup>
                        </l-marker>
                    </l-map>
                </div>
            </div>
        </div>

        <!-- AI Planner Modal -->
        <Teleport to="body">
            <div
                v-if="showAiPlanner"
                class="fixed inset-0 z-[9999] overflow-y-auto"
                aria-labelledby="modal-title"
                role="dialog"
                aria-modal="true"
            >
                <div
                    class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
                >
                    <div
                        class="fixed inset-0 transition-opacity"
                        aria-hidden="true"
                        @click="closeAiPlanner"
                    ></div>

                    <span
                        class="hidden sm:inline-block sm:align-middle sm:h-screen"
                        aria-hidden="true"
                        >&#8203;</span
                    >

                    <div
                        class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full relative z-50"
                        :class="modalWidthClass"
                    >
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full"
                                >
                                    <div
                                        class="flex justify-between items-center mb-4"
                                    >
                                        <h3
                                            class="text-lg leading-6 font-medium text-gray-900 flex items-center gap-2"
                                            id="modal-title"
                                        >
                                            <span class="text-2xl">🤖</span>
                                            AIチャット:
                                            {{
                                                selectedPrefectureForAi?.name
                                            }}旅行
                                        </h3>
                                        <!-- Resize Controls -->
                                        <div
                                            class="flex items-center gap-1 bg-gray-100 rounded-lg p-1"
                                        >
                                            <button
                                                @click="decreaseModalSize"
                                                :disabled="modalSizeIndex === 0"
                                                class="p-1 rounded hover:bg-white hover:shadow-sm disabled:opacity-30 disabled:cursor-not-allowed transition-all text-gray-600"
                                                title="小さくする"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M20 12H4"
                                                    />
                                                </svg>
                                            </button>
                                            <span
                                                class="text-xs font-bold text-gray-500 w-4 text-center"
                                                >{{ modalSizeIndex + 1 }}</span
                                            >
                                            <button
                                                @click="increaseModalSize"
                                                :disabled="modalSizeIndex === 2"
                                                class="p-1 rounded hover:bg-white hover:shadow-sm disabled:opacity-30 disabled:cursor-not-allowed transition-all text-gray-600"
                                                title="大きくする"
                                            >
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 4v16m8-8H4"
                                                    />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <!-- Chat Area -->
                                        <div
                                            class="bg-gray-50 rounded-xl p-4 overflow-y-auto mb-4 border border-gray-100 transition-all duration-300"
                                            :class="chatHeightClass"
                                            ref="chatContainer"
                                        >
                                            <div
                                                v-for="(
                                                    msg, idx
                                                ) in aiChatMessages"
                                                :key="idx"
                                                class="mb-3"
                                            >
                                                <div
                                                    v-if="msg.role === 'system'"
                                                    class="text-xs text-gray-400 text-center mb-2"
                                                >
                                                    {{ msg.content }}
                                                </div>

                                                <!-- User Message (Me) -->
                                                <div
                                                    v-else-if="msg.is_me"
                                                    class="flex justify-end items-end gap-1"
                                                >
                                                    <div class="max-w-[80%]">
                                                        <div
                                                            class="bg-blue-600 text-white rounded-2xl rounded-tr-none py-2 px-3 text-sm shadow-sm"
                                                        >
                                                            {{ msg.content }}
                                                        </div>
                                                        <div
                                                            class="text-[10px] text-gray-400 text-right mt-0.5"
                                                        >
                                                            {{ msg.user_name }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- AI Message -->
                                                <div
                                                    v-else-if="
                                                        msg.role === 'assistant'
                                                    "
                                                    class="flex justify-start items-end gap-2"
                                                >
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-sm shrink-0"
                                                    >
                                                        AI
                                                    </div>
                                                    <div class="max-w-[90%]">
                                                        <div
                                                            class="text-xs text-gray-500 mb-0.5 font-bold"
                                                        >
                                                            AI Planner
                                                        </div>
                                                        <div
                                                            class="bg-white border border-indigo-100 text-gray-800 rounded-2xl rounded-tl-none py-3 px-4 text-sm shadow-sm"
                                                        >
                                                            <div
                                                                v-for="(
                                                                    segment,
                                                                    sIdx
                                                                ) in parseMessage(
                                                                    msg.content
                                                                )"
                                                                :key="sIdx"
                                                            >
                                                                <!-- Text Segment -->
                                                                <div
                                                                    v-if="
                                                                        segment.type ===
                                                                        'text'
                                                                    "
                                                                    v-html="
                                                                        marked.parse(
                                                                            segment.content
                                                                        )
                                                                    "
                                                                    class="prose prose-sm max-w-none prose-indigo"
                                                                ></div>

                                                                <!-- JSON Segment (Spot Recommendations) -->
                                                                <div
                                                                    v-else-if="
                                                                        segment.type ===
                                                                        'json'
                                                                    "
                                                                    class="mt-3 space-y-2"
                                                                >
                                                                    <div
                                                                        v-if="
                                                                            Array.isArray(
                                                                                segment.data
                                                                            )
                                                                        "
                                                                        class="grid gap-2"
                                                                    >
                                                                        <div
                                                                            v-for="(
                                                                                item,
                                                                                iIdx
                                                                            ) in segment.data"
                                                                            :key="
                                                                                iIdx
                                                                            "
                                                                            class="bg-gray-50 rounded-lg p-3 border border-gray-200 hover:bg-gray-100 transition-colors"
                                                                        >
                                                                            <div
                                                                                class="font-bold text-indigo-700 mb-1"
                                                                            >
                                                                                {{
                                                                                    item.name
                                                                                }}
                                                                            </div>
                                                                            <div
                                                                                class="text-xs text-gray-600 mb-1"
                                                                            >
                                                                                {{
                                                                                    item.description
                                                                                }}
                                                                            </div>
                                                                            <div
                                                                                v-if="
                                                                                    item.url
                                                                                "
                                                                                class="text-right"
                                                                            >
                                                                                <a
                                                                                    :href="
                                                                                        item.url
                                                                                    "
                                                                                    target="_blank"
                                                                                    class="text-xs text-blue-500 hover:underline"
                                                                                    >詳細を見る
                                                                                    &rarr;</a
                                                                                >
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Other User Message -->
                                            </div>

                                            <div
                                                v-if="isAiProcessing"
                                                class="flex justify-start items-end gap-2"
                                            >
                                                <div
                                                    class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-sm shrink-0"
                                                >
                                                    AI
                                                </div>
                                                <div
                                                    class="bg-gray-100 text-gray-500 rounded-2xl rounded-tl-none py-2 px-3 text-xs animate-pulse"
                                                >
                                                    AIが入力中...
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Input Area -->
                                        <div class="flex flex-col gap-2">
                                            <input
                                                v-model="aiChatInput"
                                                @keyup.enter="
                                                    sendAiMessage(false)
                                                "
                                                type="text"
                                                class="w-full appearance-none border border-gray-300 rounded-xl py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                placeholder="メッセージを入力..."
                                            />
                                            <div class="flex gap-2 justify-end">
                                                <button
                                                    @click="
                                                        sendAiMessage(false)
                                                    "
                                                    :disabled="
                                                        !aiChatInput.trim()
                                                    "
                                                    class="bg-gray-500 text-white rounded-xl px-4 py-2 font-bold hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm flex items-center gap-1"
                                                >
                                                    <span>💬</span> チャット送信
                                                </button>
                                                <button
                                                    @click="sendAiMessage(true)"
                                                    :disabled="
                                                        isAiProcessing ||
                                                        !aiChatInput.trim()
                                                    "
                                                    class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl px-4 py-2 font-bold hover:from-indigo-600 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all text-sm flex items-center gap-1 shadow-sm"
                                                >
                                                    <span
                                                        v-if="isAiProcessing"
                                                        class="animate-spin"
                                                        >🔄</span
                                                    >
                                                    <span v-else>🤖</span>
                                                    クイックンに聞く
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"
                        >
                            <button
                                type="button"
                                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                                @click="closeAiPlanner"
                            >
                                閉じる
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>

<style scoped>
/* Custom Tooltip Styling */
.custom-tooltip {
    background-color: rgba(255, 255, 255, 0.95);
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    font-family: inherit;
}
.leaflet-tooltip-left:before {
    border-left-color: rgba(255, 255, 255, 0.95);
}
.leaflet-tooltip-right:before {
    border-right-color: rgba(255, 255, 255, 0.95);
}

/* Star Marker Styling */
.star-marker-container {
    background: transparent;
    border: none;
}

.star-marker-content {
    filter: drop-shadow(0 4px 3px rgba(0, 0, 0, 0.3));
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.star-marker-content:hover {
    transform: scale(1.2) translateY(-2px);
}

/* Add a subtle bounce animation on enter */
.star-marker-content {
    animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes popIn {
    0% {
        transform: scale(0);
    }
    80% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}
</style>
```
