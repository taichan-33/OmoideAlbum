<script setup>
defineProps({
    story: Object, // { id, title, thumbnail_url, date, photo_count }
});
</script>

<template>
    <div
        class="flex flex-col items-center gap-1 cursor-pointer group"
        @click="$emit('click')"
    >
        <div
            class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-full p-[3px] bg-gradient-to-tr from-yellow-400 to-fuchsia-600 group-hover:scale-105 transition-transform duration-300"
        >
            <div
                class="w-full h-full rounded-full border-2 border-white overflow-hidden bg-gray-100"
            >
                <img
                    v-if="story.thumbnail_url"
                    :src="story.thumbnail_url"
                    class="w-full h-full object-cover"
                    alt="Story Thumbnail"
                />
                <div
                    v-else
                    class="w-full h-full flex items-center justify-center text-gray-300"
                >
                    <i class="bi bi-image text-2xl"></i>
                </div>
            </div>
        </div>
        <span
            class="text-xs text-gray-600 truncate max-w-[80px] text-center group-hover:text-gray-900"
        >
            {{ story.title }}
        </span>
    </div>
</template>
