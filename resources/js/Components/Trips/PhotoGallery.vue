<script setup>
defineProps({
    photos: Array,
});

defineEmits(["open-modal"]);
</script>

<template>
    <div>
        <h3
            class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2"
        >
            <span>📸</span> ギャラリー
        </h3>

        <div
            v-if="photos.length"
            class="columns-1 sm:columns-2 gap-4 space-y-4"
        >
            <div
                v-for="photo in photos"
                :key="photo.id"
                @click="$emit('open-modal', photo)"
                class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-gray-100 cursor-pointer"
            >
                <img
                    :src="photo.path"
                    class="w-full h-auto object-cover transition duration-500 group-hover:scale-105"
                />
                <div
                    class="absolute bottom-0 inset-x-0 bg-black/60 backdrop-blur-sm p-3 text-white text-sm opacity-0 group-hover:opacity-100 transition duration-300"
                >
                    <div v-if="photo.caption" class="mb-1">
                        {{ photo.caption }}
                    </div>
                    <div class="flex items-center gap-1 text-xs text-gray-300">
                        <svg
                            class="w-3 h-3"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                            ></path>
                        </svg>
                        {{ photo.comments ? photo.comments.length : 0 }}
                        コメント
                    </div>
                </div>
            </div>
        </div>
        <div
            v-else
            class="text-center py-12 bg-gray-50 rounded-3xl border border-dashed border-gray-200"
        >
            <p class="text-gray-400">写真がまだありません</p>
        </div>
    </div>
</template>
