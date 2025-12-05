<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { marked } from "marked";

const props = defineProps({
    suggestion: Object,
});

const toggleStatus = () => {
    router.patch(
        route("suggestions.toggleStatus", props.suggestion.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                // Optional: Notification
            },
        }
    );
};

const formatTextWithLinks = (text) => {
    if (!text) return "";

    // 1. URLを検出してリンクに変換
    const urlRegex = /(https?:\/\/[^\s]+)/g;
    let formatted = text.replace(urlRegex, (url) => {
        return `<a href="${url}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full text-xs font-bold hover:bg-blue-100 transition-colors mx-1">🔗 詳細を見る</a>`;
    });

    // 2. 改行を <br> に変換
    formatted = formatted.replace(/\n/g, "<br>");

    // 3. リスト記号（・、-、*）を箇条書きスタイルに変換
    // 行頭の記号を検出して、インデントとアイコンを付ける
    formatted = formatted.replace(
        /(?:<br>|^)(?:・|-|\*)\s*(.*?)(?=<br>|$)/g,
        (match, content) => {
            return `<div class="flex items-start gap-2 mt-2 mb-1 pl-2"><span class="text-indigo-400 mt-1.5 text-[10px]">●</span><span>${content}</span></div>`;
        }
    );

    // 4. 【】で囲まれた部分を強調
    formatted = formatted.replace(
        /【(.*?)】/g,
        '<span class="font-bold text-indigo-700 bg-indigo-50 px-1 rounded mx-1">$1</span>'
    );

    return formatted;
};
</script>

<template>
    <Head :title="suggestion.title" />

    <AppLayout title="旅行プラン詳細">
        <template #header>
            <div
                class="flex flex-col md:flex-row justify-between items-center gap-4"
            >
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <Link
                        :route="route('map.index')"
                        class="px-4 py-2 bg-white border border-gray-200 rounded-full text-sm font-bold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors flex items-center gap-2 shadow-sm"
                    >
                        <span>🗺️</span> マップに戻る
                    </Link>
                    <h2
                        class="font-bold text-xl text-gray-800 leading-tight truncate"
                    >
                        {{ suggestion.title }}
                    </h2>
                </div>
                <div class="flex items-center gap-4">
                    <button
                        @click="toggleStatus"
                        class="px-4 py-2 rounded-full text-sm font-bold transition-all flex items-center gap-2 shadow-sm"
                        :class="
                            suggestion.is_visited
                                ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                        "
                    >
                        <span v-if="suggestion.is_visited"
                            >✅ 行った（思い出）</span
                        >
                        <span v-else>📍 まだ（行きたい）</span>
                    </button>
                    <Link
                        :route="route('suggestions.index')"
                        class="text-sm text-gray-500 hover:text-gray-900 transition-colors"
                    >
                        &larr; 一覧
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Hero Card -->
                <div
                    class="bg-white rounded-[2rem] shadow-xl overflow-hidden mb-8 relative border border-gray-100"
                >
                    <div
                        class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 h-32 opacity-90"
                    ></div>
                    <div class="px-8 pb-10 -mt-12 relative">
                        <div class="flex justify-between items-end mb-6">
                            <div
                                class="bg-white p-4 rounded-2xl shadow-lg text-center border border-gray-100"
                            >
                                <div
                                    class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1"
                                >
                                    おすすめ度
                                </div>
                                <div
                                    class="text-3xl font-black text-indigo-600 leading-none"
                                >
                                    {{ suggestion.recommendation_score
                                    }}<span class="text-sm text-gray-300 ml-0.5"
                                        >/5</span
                                    >
                                </div>
                            </div>
                        </div>
                        <h1
                            class="text-3xl md:text-4xl font-black text-gray-900 mb-6 leading-tight tracking-tight"
                        >
                            {{ suggestion.title }}
                        </h1>
                        <div
                            class="prose prose-lg text-gray-600 max-w-none leading-relaxed"
                            v-html="formatTextWithLinks(suggestion.content)"
                        ></div>
                    </div>
                </div>

                <!-- Highlights Grid -->
                <div class="grid md:grid-cols-2 gap-6 mb-12">
                    <!-- Accommodation Card -->
                    <div
                        v-if="suggestion.accommodation"
                        class="bg-white rounded-[2rem] shadow-lg p-8 border-t-4 border-emerald-400 hover:shadow-xl transition-shadow"
                    >
                        <h3
                            class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3"
                        >
                            <span
                                class="bg-emerald-100 text-emerald-600 p-3 rounded-xl text-2xl"
                                >🏨</span
                            >
                            宿泊おすすめ
                        </h3>
                        <div
                            class="text-gray-700 leading-relaxed space-y-2"
                            v-html="
                                formatTextWithLinks(suggestion.accommodation)
                            "
                        ></div>
                    </div>

                    <!-- Gourmet Card -->
                    <div
                        v-if="suggestion.local_food"
                        class="bg-white rounded-[2rem] shadow-lg p-8 border-t-4 border-orange-400 hover:shadow-xl transition-shadow"
                    >
                        <h3
                            class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3"
                        >
                            <span
                                class="bg-orange-100 text-orange-600 p-3 rounded-xl text-2xl"
                                >🍽️</span
                            >
                            グルメ情報
                        </h3>
                        <div
                            class="text-gray-700 leading-relaxed space-y-2"
                            v-html="formatTextWithLinks(suggestion.local_food)"
                        ></div>
                    </div>
                </div>

                <!-- Itinerary -->
                <div
                    v-if="suggestion.itinerary_data"
                    class="bg-white rounded-[2rem] shadow-xl p-8 md:p-10 mb-12 border border-gray-100"
                >
                    <h3
                        class="text-2xl font-bold text-gray-900 mb-10 flex items-center gap-3"
                    >
                        <span
                            class="bg-indigo-100 text-indigo-600 p-2 rounded-lg"
                            >🗓️</span
                        >
                        モデルコース
                    </h3>

                    <div class="space-y-12">
                        <div
                            v-for="(day, index) in suggestion.itinerary_data"
                            :key="index"
                            class="relative pl-8 md:pl-12 border-l-2 border-indigo-100"
                        >
                            <div
                                class="absolute -left-3 top-0 w-6 h-6 bg-indigo-500 rounded-full border-4 border-white shadow-sm"
                            ></div>

                            <h4
                                class="font-bold text-xl text-indigo-900 mb-6 flex items-center gap-2"
                            >
                                <span class="text-indigo-400">Day</span>
                                {{ day.day }}
                            </h4>

                            <div class="space-y-4">
                                <div
                                    v-for="(spot, sIndex) in day.spots"
                                    :key="sIndex"
                                    class="group bg-gray-50 rounded-2xl p-5 flex gap-5 hover:bg-white hover:shadow-md transition-all border border-transparent hover:border-gray-100"
                                >
                                    <div
                                        class="font-mono text-indigo-500 font-bold w-16 shrink-0 pt-1 text-lg"
                                    >
                                        {{ spot.time }}
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="font-bold text-gray-900 text-lg flex items-center flex-wrap gap-2 mb-2"
                                        >
                                            {{ spot.name }}
                                            <a
                                                v-if="spot.url"
                                                :href="spot.url"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 px-3 py-1 bg-white border border-blue-100 text-blue-600 rounded-full text-xs font-bold hover:bg-blue-50 hover:border-blue-200 transition-all shadow-sm"
                                            >
                                                <span>🔗</span> 詳細
                                            </a>
                                        </div>
                                        <div
                                            class="text-gray-600 leading-relaxed"
                                        >
                                            {{ spot.description }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div class="flex justify-center pb-12">
                    <button
                        @click="toggleStatus"
                        class="group relative w-full md:w-auto px-10 py-5 rounded-full text-xl font-bold transition-all transform hover:-translate-y-1 shadow-xl hover:shadow-2xl flex items-center justify-center gap-4 overflow-hidden"
                        :class="
                            suggestion.is_visited
                                ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-green-200'
                                : 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-indigo-200'
                        "
                    >
                        <div
                            class="absolute inset-0 bg-white/20 group-hover:bg-white/10 transition-colors"
                        ></div>
                        <span
                            v-if="suggestion.is_visited"
                            class="text-3xl relative z-10"
                            >✅</span
                        >
                        <span v-else class="text-3xl relative z-10">📍</span>
                        <span class="relative z-10">{{
                            suggestion.is_visited
                                ? "行った（思い出）にする"
                                : "行った（思い出）にする"
                        }}</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
