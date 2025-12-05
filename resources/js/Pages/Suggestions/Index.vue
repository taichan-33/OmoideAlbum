<script setup>
import { ref, watch, computed } from "vue";
import { useForm, Head, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { debounce } from "lodash";

const props = defineProps({
    suggestions: Array,
    filters: Object,
});

// Search & Sort
const search = ref(props.filters.keyword || "");
const sort = ref(props.filters.sort || "created_at_desc");
const source = ref(props.filters.source || "all");

watch(
    [search, sort, source],
    debounce(() => {
        router.get(
            route("suggestions.index"),
            { keyword: search.value, sort: sort.value, source: source.value },
            { preserveState: true, replace: true }
        );
    }, 300)
);

// Generate Modal
const showModal = ref(false);
const isGenerating = ref(false);

const form = useForm({
    optional_destination: "",
    optional_season: "",
    optional_budget: "",
    optional_interest: "",
    optional_memo: "",
});

const submitGenerate = () => {
    showModal.value = false;
    isGenerating.value = true;

    form.post(route("suggestions.store"), {
        onFinish: () => {
            isGenerating.value = false;
            form.reset();
        },
        onError: () => {
            isGenerating.value = false;
            alert("エラーが発生しました。もう一度お試しください。");
        },
    });
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

// Delete
const deleteSuggestion = (id) => {
    if (confirm("本当にこの提案を削除しますか？")) {
        router.delete(route("suggestions.destroy", id));
    }
};

// Toggle Details
const activeSuggestion = ref(null);
const toggleDetails = (id) => {
    activeSuggestion.value = activeSuggestion.value === id ? null : id;
};
</script>

<template>
    <Head title="AI旅行プランナー" />

    <AppLayout>
        <!-- Loading Overlay -->
        <div
            v-if="isGenerating"
            class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white/90 backdrop-blur-sm"
        >
            <div class="text-6xl mb-4 animate-bounce">✈️</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                AIが最高の旅行プランを考え中...
            </h2>
            <p class="text-gray-500">
                過去の思い出を分析しています。最大2分ほどお待ちください。
            </p>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Hero Section -->
            <div
                class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-[2.5rem] shadow-xl overflow-hidden mb-12 relative text-white"
            >
                <div
                    class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"
                ></div>
                <div
                    class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-white/10 rounded-full blur-3xl"
                ></div>

                <div class="relative z-10 p-10 md:p-16 text-center">
                    <h1
                        class="text-4xl md:text-5xl font-bold mb-6 tracking-tight"
                    >
                        AI旅行プランナー
                    </h1>
                    <p
                        class="text-lg md:text-xl text-indigo-100 mb-10 max-w-2xl mx-auto leading-relaxed"
                    >
                        これまでの旅の思い出をAIが分析し、<br
                            class="hidden md:block"
                        />
                        あなたにぴったりの「次の旅行先」を提案します。
                    </p>
                    <button
                        @click="showModal = true"
                        class="bg-white text-indigo-600 px-8 py-4 rounded-full font-bold text-lg shadow-lg hover:shadow-xl hover:bg-indigo-50 transition transform hover:-translate-y-1 flex items-center gap-2 mx-auto"
                    >
                        <span>✨</span> 新しいプランを提案してもらう
                    </button>
                </div>
            </div>

            <!-- Controls -->
            <div
                class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8"
            >
                <h2
                    class="text-2xl font-bold text-gray-900 flex items-center gap-2"
                >
                    <span>💡</span> 提案一覧
                </h2>
                <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                    <div class="flex bg-gray-100 p-1 rounded-xl">
                        <button
                            @click="source = 'all'"
                            class="px-4 py-2 rounded-lg text-sm font-bold transition"
                            :class="
                                source === 'all'
                                    ? 'bg-white shadow text-black'
                                    : 'text-gray-500 hover:text-gray-700'
                            "
                        >
                            すべて
                        </button>
                        <button
                            @click="source = 'planner'"
                            class="px-4 py-2 rounded-lg text-sm font-bold transition"
                            :class="
                                source === 'planner'
                                    ? 'bg-white shadow text-black'
                                    : 'text-gray-500 hover:text-gray-700'
                            "
                        >
                            AIプランナー
                        </button>
                        <button
                            @click="source = 'chat'"
                            class="px-4 py-2 rounded-lg text-sm font-bold transition"
                            :class="
                                source === 'chat'
                                    ? 'bg-white shadow text-black'
                                    : 'text-gray-500 hover:text-gray-700'
                            "
                        >
                            チャット提案
                        </button>
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="キーワード検索..."
                        class="w-full md:w-64 border-gray-200 rounded-xl focus:ring-black focus:border-black"
                    />
                    <select
                        v-model="sort"
                        class="border-gray-200 rounded-xl focus:ring-black focus:border-black"
                    >
                        <option value="created_at_desc">新しい順</option>
                        <option value="score_desc">おすすめ度順</option>
                    </select>
                </div>
            </div>

            <!-- Suggestions List -->
            <div v-if="suggestions.length" class="space-y-6">
                <div
                    v-for="suggestion in suggestions"
                    :key="suggestion.id"
                    class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden transition hover:shadow-md"
                >
                    <!-- Header (Click to expand) -->
                    <div
                        @click="toggleDetails(suggestion.id)"
                        class="p-6 md:p-8 cursor-pointer flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:bg-gray-50 transition"
                    >
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span
                                    class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md"
                                >
                                    {{
                                        new Date(
                                            suggestion.created_at
                                        ).toLocaleDateString()
                                    }}
                                </span>
                                <div class="flex text-yellow-400 text-sm">
                                    <span v-for="i in 5" :key="i">{{
                                        i <= suggestion.recommendation_score
                                            ? "★"
                                            : "☆"
                                    }}</span>
                                </div>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">
                                {{ suggestion.title }}
                            </h3>
                        </div>
                        <div class="flex items-center gap-2 text-gray-400">
                            <span class="text-sm font-medium">{{
                                activeSuggestion === suggestion.id
                                    ? "閉じる"
                                    : "詳細を見る"
                            }}</span>
                            <svg
                                class="w-5 h-5 transition transform"
                                :class="{
                                    'rotate-180':
                                        activeSuggestion === suggestion.id,
                                }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 9l-7 7-7-7"
                                ></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Details (Collapsible) -->
                    <div
                        v-show="activeSuggestion === suggestion.id"
                        class="border-t border-gray-100 bg-gray-50/50"
                    >
                        <div class="p-6 md:p-8 space-y-8">
                            <!-- Itinerary -->
                            <div
                                v-if="suggestion.itinerary_data"
                                class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm"
                            >
                                <h4
                                    class="font-bold text-gray-900 mb-4 flex items-center gap-2"
                                >
                                    <span>🗓️</span> モデル日程
                                </h4>
                                <div class="space-y-8 relative pl-4">
                                    <div
                                        class="absolute left-4 top-2 bottom-2 w-0.5 bg-gray-100"
                                    ></div>

                                    <!-- Loop through Days -->
                                    <div
                                        v-for="(
                                            day, dIndex
                                        ) in suggestion.itinerary_data"
                                        :key="dIndex"
                                        class="relative pl-8"
                                    >
                                        <!-- Day Header -->
                                        <div class="mb-4">
                                            <div
                                                class="absolute left-0 top-0 w-8 h-8 bg-indigo-500 text-white rounded-full flex items-center justify-center border-4 border-white shadow-sm z-10 font-bold text-xs"
                                            >
                                                {{ day.day }}
                                            </div>
                                            <h5
                                                class="font-bold text-indigo-900 text-lg ml-2"
                                            >
                                                Day {{ day.day }}
                                            </h5>
                                        </div>

                                        <!-- Loop through Spots -->
                                        <div class="space-y-4 ml-2">
                                            <div
                                                v-for="(
                                                    spot, sIndex
                                                ) in day.spots"
                                                :key="sIndex"
                                                class="bg-gray-50 rounded-lg p-3 border border-gray-200"
                                            >
                                                <div
                                                    class="flex justify-between items-start"
                                                >
                                                    <div
                                                        class="font-bold text-gray-900 text-sm"
                                                    >
                                                        <span
                                                            class="text-indigo-600 mr-2"
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
                                                        class="text-xs text-blue-500 hover:underline whitespace-nowrap ml-2"
                                                    >
                                                        詳細 &rarr;
                                                    </a>
                                                </div>
                                                <div
                                                    class="text-xs text-gray-600 mt-1"
                                                >
                                                    {{ spot.description }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                            <!-- Content -->
                            <div
                                class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm"
                            >
                                <h4
                                    class="font-bold text-gray-900 mb-4 flex items-center gap-2"
                                >
                                    <span>📝</span> 提案の理由
                                </h4>
                                <div
                                    class="prose max-w-none text-gray-600 leading-relaxed"
                                    v-html="
                                        formatTextWithLinks(suggestion.content)
                                    "
                                ></div>
                            </div>

                            <!-- Delete -->
                            <div class="flex justify-end pt-4">
                                <button
                                    @click="deleteSuggestion(suggestion.id)"
                                    class="text-red-500 hover:text-red-700 text-sm font-medium flex items-center gap-2 px-4 py-2 hover:bg-red-50 rounded-lg transition"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        ></path>
                                    </svg>
                                    この提案を削除
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="text-center py-20 bg-gray-50 rounded-3xl border border-dashed border-gray-200"
            >
                <p class="text-gray-400 text-lg">
                    まだ提案がありません。<br />上のボタンから新しいプランを作ってみましょう！
                </p>
            </div>
        </div>

        <!-- Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0"
            >
                <div
                    class="fixed inset-0 bg-gray-500/75 transition-opacity"
                    @click="showModal = false"
                    aria-hidden="true"
                ></div>

                <div
                    class="relative bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full"
                            >
                                <h3
                                    class="text-lg leading-6 font-bold text-gray-900 mb-4"
                                    id="modal-title"
                                >
                                    AIへの追加リクエスト (任意)
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                            >希望の行き先</label
                                        >
                                        <input
                                            v-model="form.optional_destination"
                                            type="text"
                                            placeholder="例: 東北, 沖縄"
                                            class="w-full border-gray-200 rounded-lg focus:ring-black focus:border-black"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                            >希望の季節</label
                                        >
                                        <input
                                            v-model="form.optional_season"
                                            type="text"
                                            placeholder="例: 次の夏"
                                            class="w-full border-gray-200 rounded-lg focus:ring-black focus:border-black"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                            >予算感</label
                                        >
                                        <select
                                            v-model="form.optional_budget"
                                            class="w-full border-gray-200 rounded-lg focus:ring-black focus:border-black"
                                        >
                                            <option value="">指定なし</option>
                                            <option value="リッチな旅行">
                                                リッチな旅行
                                            </option>
                                            <option value="普通の旅行">
                                                普通の旅行
                                            </option>
                                            <option value="節約ぎみの旅行">
                                                節約ぎみの旅行
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                            >主な目的</label
                                        >
                                        <input
                                            v-model="form.optional_interest"
                                            type="text"
                                            placeholder="例: グルメ, のんびり"
                                            class="w-full border-gray-200 rounded-lg focus:ring-black focus:border-black"
                                        />
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                            >その他メモ</label
                                        >
                                        <textarea
                                            v-model="form.optional_memo"
                                            rows="3"
                                            placeholder="例: 運転はしたくない、記念日です"
                                            class="w-full border-gray-200 rounded-lg focus:ring-black focus:border-black"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"
                    >
                        <button
                            @click="submitGenerate"
                            type="button"
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-black text-base font-medium text-white hover:bg-gray-800 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            提案を作成する
                        </button>
                        <button
                            @click="showModal = false"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            キャンセル
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
