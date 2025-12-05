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
    // URLの直後にある閉じ括弧や引用符を含めないように修正
    const urlRegex = /(https?:\/\/[^\s<>"')]+)/g;
    let formatted = text.replace(urlRegex, (url) => {
        return `<a href="${url}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full text-xs font-bold hover:bg-blue-100 transition-colors mx-1">🔗 詳細を見る</a>`;
    });

    // 2. 改行を <br> に変換
    formatted = formatted.replace(/\n/g, "<br>");

    // 3. 見出しの整形（✨などの絵文字で終わる行、または特定のキーワードで始まる行）
    formatted = formatted.replace(
        /(?:^|<br>)(.*?(?:✨|💡|📍|🗓️|♨️|🍷))(?=<br>|$)/g,
        '<div class="font-bold text-indigo-800 mt-4 mb-2 text-base border-b-2 border-indigo-100 pb-1 inline-block">$1</div>'
    );

    formatted = formatted.replace(
        /(?:^|<br>)(?:■|●)?\s*(ポイント|推奨|主な観光|モデル日程|なぜこのプラン|提案の理由).*?(?=<br>|$)/g,
        (match) => {
            // <br>が含まれている場合は除去してdivでラップ
            const content = match.replace(/^<br>/, "");
            return `<div class="font-bold text-indigo-800 mt-4 mb-2 text-base border-b-2 border-indigo-100 pb-1 inline-block">${content}</div>`;
        }
    );

    // 4. リスト記号（・、-、*）を箇条書きスタイルに変換
    formatted = formatted.replace(
        /(?:<br>|^)(?:・|-|\*)\s*(.*?)(?=<br>|$)/g,
        (match, content) => {
            return `<div class="flex items-start gap-2 mt-1 mb-1 pl-2"><span class="text-indigo-400 mt-1.5 text-[10px] shrink-0">●</span><span>${content}</span></div>`;
        }
    );

    // 4. 【】で囲まれた部分を強調
    formatted = formatted.replace(
        /【(.*?)】/g,
        '<span class="font-bold text-indigo-700 bg-indigo-50 px-1 rounded mx-1">$1</span>'
    );

    return formatted;
};
// Parse Content (JSON or String)
const parseContent = (content) => {
    if (!content) return null;
    if (typeof content === "object") {
        return { type: "json", data: content };
    }
    try {
        const parsed = JSON.parse(content);
        if (typeof parsed === "object" && parsed !== null) {
            return { type: "json", data: parsed };
        }
    } catch (e) {
        // Not JSON, treat as string
    }
    return { type: "text", content: content };
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
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div
                    class="bg-white rounded-[2rem] shadow-xl overflow-hidden border border-gray-100"
                >
                    <!-- Hero Section -->
                    <div
                        class="bg-gradient-to-br from-indigo-600 to-purple-700 p-10 md:p-16 text-center text-white relative overflow-hidden"
                    >
                        <div
                            class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"
                        ></div>
                        <div
                            class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"
                        ></div>

                        <div class="relative z-10">
                            <div class="flex justify-center gap-2 mb-6">
                                <span
                                    class="bg-white/20 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full"
                                >
                                    {{
                                        new Date(
                                            suggestion.created_at
                                        ).toLocaleDateString()
                                    }}
                                </span>
                                <span
                                    class="bg-yellow-400/20 backdrop-blur-sm text-yellow-300 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1"
                                >
                                    <span>★</span>
                                    {{ suggestion.recommendation_score }}.0
                                </span>
                            </div>
                            <h1
                                class="text-3xl md:text-5xl font-bold mb-6 tracking-tight leading-tight"
                            >
                                {{ suggestion.title }}
                            </h1>

                            <!-- Structured JSON Description (Hero) -->
                            <div
                                v-if="
                                    parseContent(suggestion.content)?.type ===
                                    'json'
                                "
                                class="text-lg md:text-xl text-indigo-100 max-w-2xl mx-auto leading-relaxed"
                            >
                                <div
                                    v-if="
                                        parseContent(suggestion.content).data
                                            .title
                                    "
                                    class="font-bold mb-2 text-white"
                                >
                                    {{
                                        parseContent(suggestion.content).data
                                            .title
                                    }}
                                </div>
                                <div
                                    v-html="
                                        formatTextWithLinks(
                                            parseContent(suggestion.content)
                                                .data.description
                                        )
                                    "
                                ></div>
                            </div>

                            <!-- Legacy Text Description (Hero) -->
                            <div
                                v-else
                                class="text-lg md:text-xl text-indigo-100 max-w-2xl mx-auto leading-relaxed"
                                v-html="formatTextWithLinks(suggestion.content)"
                            ></div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="p-8 md:p-12 bg-gray-50/50">
                        <!-- Info Grid -->
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12"
                        >
                            <div
                                class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm"
                            >
                                <h4
                                    class="font-bold text-gray-900 mb-4 flex items-center gap-2"
                                >
                                    <span>🏨</span> おすすめの宿
                                </h4>
                                <div
                                    class="text-gray-600 leading-relaxed space-y-2"
                                    v-html="
                                        formatTextWithLinks(
                                            suggestion.accommodation
                                        )
                                    "
                                ></div>
                            </div>
                            <div
                                class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm"
                            >
                                <h4
                                    class="font-bold text-gray-900 mb-4 flex items-center gap-2"
                                >
                                    <span>🍽️</span> 名産・グルメ
                                </h4>
                                <div
                                    class="text-gray-600 leading-relaxed space-y-2"
                                    v-html="
                                        formatTextWithLinks(
                                            suggestion.local_food
                                        )
                                    "
                                ></div>
                            </div>
                        </div>

                        <!-- Structured Reason Points -->
                        <div
                            v-if="
                                parseContent(suggestion.content)?.type ===
                                    'json' &&
                                parseContent(suggestion.content).data.points
                            "
                            class="mb-12"
                        >
                            <h4
                                class="font-bold text-gray-900 mb-6 flex items-center gap-2 text-xl"
                            >
                                <span>✨</span> おすすめポイント
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div
                                    v-for="(point, pIndex) in parseContent(
                                        suggestion.content
                                    ).data.points"
                                    :key="pIndex"
                                    class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm hover:shadow-md transition"
                                >
                                    <div
                                        class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold text-lg mb-4"
                                    >
                                        {{ pIndex + 1 }}
                                    </div>
                                    <h5
                                        class="font-bold text-gray-900 mb-2 text-lg"
                                    >
                                        {{ point.title }}
                                    </h5>
                                    <p
                                        class="text-gray-600 leading-relaxed text-sm"
                                    >
                                        {{ point.description }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Itinerary Timeline -->
                        <div
                            class="bg-white rounded-3xl p-8 md:p-10 shadow-sm border border-gray-100"
                        >
                            <h4
                                class="font-bold text-gray-900 mb-8 flex items-center gap-2 text-xl"
                            >
                                <span>🗓️</span> モデル日程
                            </h4>
                            <div class="relative">
                                <!-- Vertical Line -->
                                <div
                                    class="absolute left-4 top-4 bottom-4 w-0.5 bg-indigo-100"
                                ></div>

                                <div class="space-y-12">
                                    <div
                                        v-for="(
                                            day, dIndex
                                        ) in suggestion.itinerary_data"
                                        :key="dIndex"
                                        class="relative pl-10"
                                    >
                                        <!-- Day Header -->
                                        <div class="mb-6">
                                            <div
                                                class="absolute left-0 top-0 w-9 h-9 bg-indigo-500 text-white rounded-full flex items-center justify-center border-4 border-white shadow-sm z-10 font-bold text-sm"
                                            >
                                                {{ day.day }}
                                            </div>
                                            <h5
                                                class="font-bold text-indigo-900 text-xl ml-2"
                                            >
                                                Day {{ day.day }}
                                            </h5>
                                        </div>

                                        <!-- Loop through Spots -->
                                        <div class="space-y-6">
                                            <div
                                                v-for="(
                                                    spot, sIndex
                                                ) in day.spots"
                                                :key="sIndex"
                                                class="bg-gray-50 rounded-2xl p-5 border border-gray-200 hover:border-indigo-200 transition group"
                                            >
                                                <div
                                                    class="flex flex-col md:flex-row md:justify-between md:items-start gap-2"
                                                >
                                                    <div
                                                        class="font-bold text-gray-900 text-lg flex items-center gap-3"
                                                    >
                                                        <span
                                                            class="text-indigo-600 bg-indigo-50 px-2 py-1 rounded text-sm font-mono"
                                                            >{{
                                                                spot.time
                                                            }}</span
                                                        >
                                                        {{ spot.name }}
                                                    </div>
                                                    <a
                                                        v-if="spot.url"
                                                        :href="spot.url"
                                                        target="_blank"
                                                        class="text-sm text-white bg-indigo-500 hover:bg-indigo-600 px-4 py-1.5 rounded-full font-bold transition-colors shadow-sm opacity-0 group-hover:opacity-100 transform translate-y-1 group-hover:translate-y-0 duration-200"
                                                    >
                                                        公式サイト &rarr;
                                                    </a>
                                                </div>
                                                <div
                                                    class="text-gray-600 mt-2 leading-relaxed"
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

                    <!-- Footer Actions -->
                    <div class="bg-gray-50 p-8 flex justify-center">
                        <button
                            @click="toggleStatus"
                            class="w-full md:w-auto px-8 py-4 rounded-full text-lg font-bold transition-all flex items-center justify-center gap-3 shadow-lg transform hover:-translate-y-1"
                            :class="
                                suggestion.is_visited
                                    ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white hover:shadow-green-200'
                                    : 'bg-gradient-to-r from-gray-700 to-gray-900 text-white hover:shadow-gray-300'
                            "
                        >
                            <span v-if="suggestion.is_visited" class="text-2xl"
                                >✅</span
                            >
                            <span v-else class="text-2xl">📍</span>
                            <span v-if="suggestion.is_visited"
                                >この場所は「行った（思い出）」リストに入っています</span
                            >
                            <span v-else
                                >この場所を「行った（思い出）」にする</span
                            >
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
