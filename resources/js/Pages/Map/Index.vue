<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import "leaflet/dist/leaflet.css";
import {
    LMap,
    LTileLayer,
    LGeoJson,
    LMarker,
    LTooltip,
    LIcon,
} from "@vue-leaflet/vue-leaflet";
import japanGeoJson from "../../data/japan_map.json";
import worldGeoJson from "../../data/world_map.json";
import L from "leaflet";

const props = defineProps({
    mapData: Object, // {'JP-01': {count: 5, dates: [...]}, ...}
    pinnedCodes: Array, // ['JP-01', 'JP-47']
});

const zoom = ref(5);
const center = ref([38.0, 137.0]); // Center of Japan
const bounds = ref(null);
const prefectureCenters = ref({});
const prefectureNames = ref({}); // Store names for tooltips on markers
const currentMap = ref("JP"); // 'JP' or 'World'

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
    // const isPinned = props.pinnedCodes.includes(code); // Pins are now markers

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

    if (props.pinnedCodes.includes(code)) {
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

// Helper to generate tooltip HTML
const getTooltipContent = (code, name) => {
    const data = props.mapData[code];
    const count = data ? data.count : 0;
    const dates = data ? data.dates : [];
    const isPinned = props.pinnedCodes.includes(code);

    // Build Tooltip HTML
    let datesHtml = "";
    if (dates.length > 0) {
        datesHtml = '<div class="mt-2 text-xs text-gray-500 border-t pt-1">';
        dates.forEach((date, index) => {
            datesHtml += `<div>${index + 1}回目: ${date}</div>`;
        });
        datesHtml += "</div>";
    }

    // Pin button only for Japan
    let pinButtonHtml = "";
    if (currentMap.value === "JP") {
        pinButtonHtml = `
            <div class="mt-2 pt-2 border-t border-gray-100 text-xs font-bold cursor-pointer hover:text-amber-600 transition-colors">
                ${isPinned ? "★ ピン留め中" : "☆ 行きたい場所に追加"}
            </div>
        `;
    }

    return `
        <div class="text-center min-w-[120px]">
            <div class="font-bold text-lg mb-1 flex items-center justify-center gap-2">
                ${name}
            </div>
            <div class="text-sm mb-1">
                ${
                    count > 0
                        ? `<span class="text-blue-600 font-bold">${count}回</span> 訪問`
                        : '<span class="text-gray-400">未訪問</span>'
                }
            </div>
            ${datesHtml}
            ${pinButtonHtml}
        </div>
    `;
};

// Tooltip content & Interaction
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

    const tooltipContent = getTooltipContent(code, name);

    layer.bindTooltip(tooltipContent, {
        permanent: false,
        sticky: false,
        direction: "top",
        className: "custom-tooltip",
        interactive: true,
    });

    // Handle Click on Layer (Prefecture)
    layer.on({
        mouseover: (e) => {
            const layer = e.target;
            layer.setStyle({
                weight: 2,
                color: "#666",
                fillOpacity: 1,
            });
            layer.bringToFront();
        },
        mouseout: (e) => {
            const layer = e.target;
            // Reset style
            // We need to re-evaluate style because pin status might have changed (though unlikely during hover)
            // Ideally use a reactive style, but for now:
            const style = styleFunction(feature);
            layer.setStyle(style);
        },
        click: (e) => {
            // Toggle Pin on click
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

                        <!-- Star Markers for Pinned Locations -->
                        <l-marker
                            v-if="currentMap === 'JP'"
                            v-for="code in pinnedCodes"
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
                            <l-tooltip
                                :content="
                                    getTooltipContent(
                                        code,
                                        prefectureNames[code] || ''
                                    )
                                "
                                direction="top"
                                class-name="custom-tooltip"
                                :interactive="true"
                            />
                        </l-marker>
                    </l-map>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
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
