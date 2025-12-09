<script setup>
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    scrap: Object,
});

const deleteScrap = () => {
    if (confirm("このスクラップを削除しますか？")) {
        router.delete(route("scraps.destroy", props.scrap.id));
    }
};
</script>

<template>
    <div
        class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden break-inside-avoid mb-4 border border-gray-100 group"
    >
        <!-- Image -->
        <a
            :href="scrap.url"
            target="_blank"
            class="block relative overflow-hidden"
        >
            <div v-if="scrap.image_url" class="aspect-w-16 aspect-h-9">
                <img
                    :src="scrap.image_url"
                    class="w-full h-auto object-cover transition-transform duration-500 group-hover:scale-105"
                />
            </div>
            <div
                v-else
                class="h-32 bg-gray-100 flex items-center justify-center text-gray-400"
            >
                <i class="bi bi-link-45deg text-3xl"></i>
            </div>

            <!-- Overlay Actions -->
            <div
                class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity"
            >
                <button
                    @click.prevent="deleteScrap"
                    class="bg-white/90 text-red-500 p-1.5 rounded-full hover:bg-red-50 shadow-sm transition"
                >
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </a>

        <!-- Content -->
        <div class="p-4">
            <a :href="scrap.url" target="_blank" class="block">
                <h3
                    class="font-bold text-gray-800 text-sm leading-snug mb-1 line-clamp-2 group-hover:text-blue-600 transition"
                >
                    {{ scrap.title || scrap.url }}
                </h3>
                <p
                    v-if="scrap.description"
                    class="text-xs text-gray-500 line-clamp-2 mb-2"
                >
                    {{ scrap.description }}
                </p>
                <div class="flex items-center gap-1 text-xs text-gray-400">
                    <i class="bi bi-globe"></i>
                    <span class="truncate">{{
                        scrap.site_name || new URL(scrap.url).hostname
                    }}</span>
                </div>
            </a>
        </div>
    </div>
</template>
