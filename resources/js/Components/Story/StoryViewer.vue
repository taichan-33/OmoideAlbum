<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from "vue";
import axios from "axios";

const props = defineProps({
    storyId: Number,
});

const emit = defineEmits(["close"]);

const loading = ref(true);
const story = ref(null);
const currentIndex = ref(0);
const progress = ref(0);
const isPaused = ref(false);
let timer = null;
const DURATION = 5000; // 5 seconds per photo
const TICK = 50; // Update progress every 50ms

const currentPhoto = computed(() => {
    if (!story.value || !story.value.photos) return null;
    return story.value.photos[currentIndex.value];
});

const fetchStory = async () => {
    try {
        const response = await axios.get(route("stories.show", props.storyId));
        story.value = response.data;
        startTimer();
    } catch (error) {
        console.error("Failed to fetch story:", error);
        emit("close");
    } finally {
        loading.value = false;
    }
};

const startTimer = () => {
    clearInterval(timer);
    progress.value = 0;
    timer = setInterval(() => {
        if (isPaused.value) return;

        progress.value += (TICK / DURATION) * 100;
        if (progress.value >= 100) {
            next();
        }
    }, TICK);
};

const next = () => {
    if (currentIndex.value < story.value.photos.length - 1) {
        currentIndex.value++;
        startTimer();
    } else {
        emit("close"); // End of story
    }
};

const prev = () => {
    if (currentIndex.value > 0) {
        currentIndex.value--;
        startTimer();
    } else {
        startTimer(); // Reset current photo timer
    }
};

const pause = () => {
    isPaused.value = true;
};

const resume = () => {
    isPaused.value = false;
};

onMounted(() => {
    fetchStory();
    document.body.style.overflow = "hidden"; // Prevent background scrolling
});

onUnmounted(() => {
    clearInterval(timer);
    document.body.style.overflow = "";
});
</script>

<template>
    <div class="fixed inset-0 z-50 bg-black flex items-center justify-center">
        <!-- Close Button -->
        <button
            @click="$emit('close')"
            class="absolute top-4 right-4 z-50 text-white p-2"
        >
            <i class="bi bi-x-lg text-2xl"></i>
        </button>

        <!-- Loading State -->
        <div v-if="loading" class="text-white">
            <div
                class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-white"
            ></div>
        </div>

        <!-- Story Content -->
        <div
            v-else
            class="relative w-full h-full max-w-md mx-auto bg-black flex flex-col"
        >
            <!-- Progress Bars -->
            <div class="absolute top-2 left-0 right-0 z-20 flex gap-1 px-2">
                <div
                    v-for="(photo, index) in story.photos"
                    :key="photo.id"
                    class="h-1 flex-1 bg-gray-600 rounded-full overflow-hidden"
                >
                    <div
                        class="h-full bg-white transition-all duration-linear"
                        :style="{
                            width:
                                index < currentIndex
                                    ? '100%'
                                    : index === currentIndex
                                    ? `${progress}%`
                                    : '0%',
                        }"
                    ></div>
                </div>
            </div>

            <!-- Header Info -->
            <div
                class="absolute top-6 left-0 right-0 z-20 px-4 flex items-center gap-3"
            >
                <!-- Ideally user avatar here, but we use story title for now -->
                <div
                    class="text-white text-sm font-bold shadow-black drop-shadow-md"
                >
                    {{ story.title }}
                </div>
                <div class="text-gray-300 text-xs shadow-black drop-shadow-md">
                    {{ currentPhoto.taken_at }}
                </div>
            </div>

            <!-- Main Image -->
            <div
                class="flex-1 relative flex items-center justify-center bg-black"
                @mousedown="pause"
                @mouseup="resume"
                @touchstart="pause"
                @touchend="resume"
            >
                <img
                    :key="currentPhoto.id"
                    :src="currentPhoto.url"
                    class="max-h-full max-w-full object-contain"
                    alt="Story Photo"
                />

                <!-- Navigation Zones -->
                <!-- Prev: Left 30% -->
                <div
                    class="absolute inset-y-0 left-0 w-[30%] z-10 cursor-pointer"
                    @click.stop="prev"
                ></div>
                <!-- Next: Right 70% -->
                <div
                    class="absolute inset-y-0 right-0 w-[70%] z-10 cursor-pointer"
                    @click.stop="next"
                ></div>
            </div>

            <!-- Footer / Caption -->
            <div
                v-if="currentPhoto.caption || currentPhoto.location"
                class="absolute bottom-8 left-0 right-0 z-20 px-4 text-center"
            >
                <div
                    v-if="currentPhoto.location"
                    class="inline-block bg-black/50 text-white text-xs px-2 py-1 rounded mb-2 backdrop-blur-sm"
                >
                    <i class="bi bi-geo-alt-fill text-red-400"></i>
                    {{ currentPhoto.location }}
                </div>
                <p
                    v-if="currentPhoto.caption"
                    class="text-white text-shadow-md text-lg font-medium"
                >
                    {{ currentPhoto.caption }}
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.text-shadow-md {
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
}
</style>
