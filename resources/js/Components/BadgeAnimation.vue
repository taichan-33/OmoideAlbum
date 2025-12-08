<script setup>
import { ref, onMounted, watch } from "vue";
import { usePage } from "@inertiajs/vue3";

const show = ref(false);
const badges = ref([]);
const currentBadgeIndex = ref(0);

const page = usePage();

// ページプロパティの変更を監視
watch(
    () => page.props.flash.new_badges,
    (newBadges) => {
        if (newBadges && newBadges.length > 0) {
            badges.value = newBadges;
            currentBadgeIndex.value = 0;
            show.value = true;
            playAnimation();
        }
    },
    { immediate: true }
);

const playAnimation = () => {
    // ここで紙吹雪などのアニメーションを発火させても良い
    // 今回はシンプルにモーダル表示
};

const nextBadge = () => {
    if (currentBadgeIndex.value < badges.value.length - 1) {
        currentBadgeIndex.value++;
    } else {
        close();
    }
};

const close = () => {
    show.value = false;
    badges.value = [];
    // フラッシュメッセージをクリアするためのリクエストを送るか、
    // 単に表示を消すだけにする（リロードすると消えるのでOK）
};
</script>

<template>
    <Transition
        enter-active-class="ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0"
        >
            <div class="fixed inset-0 transform transition-all" @click="close">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div
                class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-lg p-6 text-center relative z-10"
            >
                <div class="mb-4">
                    <span class="text-6xl animate-bounce inline-block">🏆</span>
                </div>

                <h3 class="text-2xl font-bold text-gray-900 mb-2">
                    称号獲得！
                </h3>

                <div v-if="badges[currentBadgeIndex]" class="py-4">
                    <div class="text-8xl mb-4">
                        {{ badges[currentBadgeIndex].icon_path }}
                    </div>
                    <h4 class="text-xl font-bold text-yellow-600 mb-2">
                        {{ badges[currentBadgeIndex].name }}
                    </h4>
                    <p class="text-gray-600">
                        {{ badges[currentBadgeIndex].description }}
                    </p>
                </div>

                <div class="mt-6">
                    <button
                        @click="nextBadge"
                        class="inline-flex justify-center w-full rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-500 text-base font-medium text-white hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:text-sm"
                    >
                        {{
                            currentBadgeIndex < badges.length - 1
                                ? "次へ"
                                : "閉じる"
                        }}
                    </button>
                </div>

                <!-- Confetti Effect (CSS only for simplicity) -->
                <div
                    class="absolute top-0 left-0 w-full h-full pointer-events-none overflow-hidden"
                >
                    <div
                        v-for="n in 20"
                        :key="n"
                        class="confetti"
                        :style="{
                            left: Math.random() * 100 + '%',
                            animationDelay: Math.random() * 2 + 's',
                            backgroundColor: [
                                '#FFD700',
                                '#FF6347',
                                '#40E0D0',
                                '#FF69B4',
                            ][Math.floor(Math.random() * 4)],
                        }"
                    ></div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.confetti {
    position: absolute;
    top: -10px;
    width: 10px;
    height: 10px;
    animation: fall 3s linear infinite;
}

@keyframes fall {
    to {
        transform: translateY(100vh) rotate(720deg);
    }
}
</style>
