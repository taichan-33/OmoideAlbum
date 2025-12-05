<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

const props = defineProps({
    suggestion: Object,
});
</script>

<template>
    <Head :title="suggestion.title" />

    <AppLayout title="旅行プラン詳細">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ suggestion.title }}
                </h2>
                <Link
                    :route="route('suggestions.index')"
                    class="text-sm text-gray-600 hover:text-gray-900"
                >
                    &larr; 一覧に戻る
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6"
                >
                    <!-- Overview -->
                    <div class="mb-8">
                        <div class="flex items-center gap-4 mb-4">
                            <div
                                class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-bold"
                            >
                                おすすめ度:
                                {{ suggestion.recommendation_score }}/5
                            </div>
                            <div
                                v-if="suggestion.accommodation"
                                class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-bold"
                            >
                                宿泊: {{ suggestion.accommodation }}
                            </div>
                            <div
                                v-if="suggestion.local_food"
                                class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full text-sm font-bold"
                            >
                                グルメ: {{ suggestion.local_food }}
                            </div>
                        </div>

                        <div
                            class="prose max-w-none text-gray-700"
                            v-html="suggestion.content"
                        ></div>
                    </div>

                    <!-- Itinerary -->
                    <div v-if="suggestion.itinerary_data" class="border-t pt-8">
                        <h3 class="text-lg font-bold text-gray-900 mb-6">
                            モデルコース
                        </h3>

                        <div class="space-y-8">
                            <div
                                v-for="(
                                    day, index
                                ) in suggestion.itinerary_data"
                                :key="index"
                                class="relative pl-8 border-l-2 border-indigo-200"
                            >
                                <div
                                    class="absolute -left-2.5 top-0 w-5 h-5 bg-indigo-500 rounded-full border-4 border-white"
                                ></div>

                                <h4
                                    class="font-bold text-lg text-indigo-900 mb-4"
                                >
                                    {{ day.day }}日目
                                </h4>

                                <div class="space-y-4">
                                    <div
                                        v-for="(spot, sIndex) in day.spots"
                                        :key="sIndex"
                                        class="bg-gray-50 rounded-lg p-4 flex gap-4"
                                    >
                                        <div
                                            class="font-mono text-indigo-600 font-bold w-16 shrink-0 pt-0.5"
                                        >
                                            {{ spot.time }}
                                        </div>
                                        <div>
                                            <div
                                                class="font-bold text-gray-900"
                                            >
                                                {{ spot.name }}
                                            </div>
                                            <div
                                                class="text-sm text-gray-600 mt-1"
                                            >
                                                {{ spot.description }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
