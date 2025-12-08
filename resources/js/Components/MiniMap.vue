<script setup>
import { ref, computed, onMounted } from "vue";
import "leaflet/dist/leaflet.css";
import { LMap, LGeoJson } from "@vue-leaflet/vue-leaflet";
import japanGeoJson from "@/data/japan_map.json";

const props = defineProps({
    prefectureName: String, // e.g. "北海道"
    prefectureCode: String, // e.g. "JP-01"
});

const zoom = ref(4);
const center = ref([38.0, 137.0]); // Center of Japan

// Calculate center based on prefecture name (simplified)
// Ideally we reuse logic from Map/Index.vue but for now we just highlight
const mapOptions = {
    zoomControl: false,
    dragging: false,
    scrollWheelZoom: false,
    doubleClickZoom: false,
    boxZoom: false,
    keyboard: false,
    attributionControl: false,
};

const styleFunction = (feature) => {
    let isTarget = false;
    if (props.prefectureName) {
        isTarget = feature.properties.nam_ja === props.prefectureName;
    } else if (props.prefectureCode) {
        // JP-01 -> 1
        const id = parseInt(props.prefectureCode.split("-")[1]);
        isTarget = feature.properties.id === id;
    }

    return {
        weight: isTarget ? 2 : 0.5,
        color: isTarget ? "#f59e0b" : "#cbd5e1", // Amber-500 vs Slate-300
        fillColor: isTarget ? "#fbbf24" : "#f1f5f9", // Amber-400 vs Slate-100
        fillOpacity: isTarget ? 0.8 : 0.5,
    };
};
</script>

<template>
    <div
        class="h-32 w-full bg-blue-50 rounded-lg overflow-hidden relative pointer-events-none"
    >
        <l-map
            ref="map"
            v-model:zoom="zoom"
            v-model:center="center"
            :use-global-leaflet="false"
            :options="mapOptions"
            class="z-0"
        >
            <l-geo-json
                :geojson="japanGeoJson"
                :options="{ style: styleFunction }"
            />
        </l-map>
        <!-- Overlay Label -->
        <div
            class="absolute bottom-1 right-1 bg-white/80 px-2 py-0.5 rounded text-xs font-bold text-gray-600 z-[1000]"
        >
            {{ prefectureName }}
        </div>
    </div>
</template>
