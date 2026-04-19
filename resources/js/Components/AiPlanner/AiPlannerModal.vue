<script setup>
import { marked } from "marked";
import { toRef } from "vue";
import { useAiPlannerChat } from "@/Composables/useAiPlannerChat";

const props = defineProps({
    show: Boolean,
    prefecture: {
        type: Object,
        default: null,
    },
    user: {
        type: Object,
        default: null,
    },
});

defineEmits(["close"]);

const {
    activeMenuMessageId,
    adjustTextareaHeight,
    aiChatInput,
    aiChatMessages,
    chatContainer,
    chatHeightClass,
    chatInputTextarea,
    createPlanFromMessage,
    decreaseModalSize,
    handleChatKeydown,
    increaseModalSize,
    isAiProcessing,
    menuPosition,
    modalWidthClass,
    parseMessage,
    planRequestAdditional,
    planRequestQuote,
    savePlan,
    sendAiMessage,
    showPlanRequestModal,
    submitPlanRequest,
    toggleMenu,
} = useAiPlannerChat({
    show: toRef(props, "show"),
    selectedPrefecture: toRef(props, "prefecture"),
    user: toRef(props, "user"),
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[9999] overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
            >
                <div
                    class="fixed inset-0 z-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"
                    @click="$emit('close')"
                ></div>

                <span
                    class="hidden sm:inline-block sm:align-middle sm:h-screen"
                    aria-hidden="true"
                    >&#8203;</span
                >

                <div
                    class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle w-full"
                    :class="modalWidthClass"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <div class="flex items-center justify-between">
                                    <h3
                                        class="text-lg leading-6 font-medium text-gray-900 flex items-center gap-2"
                                        id="modal-title"
                                    >
                                        <span class="text-2xl">🤖</span>
                                        AIチャット:
                                        {{ prefecture?.name }}旅行
                                    </h3>
                                    <div
                                        class="flex items-center gap-1 bg-gray-100 rounded-lg p-1"
                                    >
                                        <button
                                            @click="decreaseModalSize"
                                            :disabled="modalWidthClass === 'sm:max-w-lg'"
                                            class="p-1 rounded hover:bg-white hover:shadow-sm disabled:opacity-30 disabled:cursor-not-allowed transition-all text-gray-600"
                                            title="小さくする"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M20 12H4"
                                                />
                                            </svg>
                                        </button>
                                        <button
                                            @click="increaseModalSize"
                                            :disabled="modalWidthClass === 'sm:max-w-4xl'"
                                            class="p-1 rounded hover:bg-white hover:shadow-sm disabled:opacity-30 disabled:cursor-not-allowed transition-all text-gray-600"
                                            title="大きくする"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 4v16m8-8H4"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <div
                                        class="bg-gray-50 rounded-xl p-4 overflow-y-auto mb-4 border border-gray-100 transition-all duration-300"
                                        :class="chatHeightClass"
                                        ref="chatContainer"
                                    >
                                        <div
                                            v-for="(msg, idx) in aiChatMessages"
                                            :key="idx"
                                            class="mb-3"
                                        >
                                            <div
                                                v-if="msg.role === 'system'"
                                                class="text-xs text-gray-400 text-center mb-2"
                                            >
                                                {{ msg.content }}
                                            </div>

                                            <div
                                                v-else-if="msg.is_me"
                                                class="flex justify-end items-end gap-1"
                                            >
                                                <div class="max-w-[80%]">
                                                    <div
                                                        class="bg-blue-600 text-white rounded-2xl rounded-tr-none py-2 px-3 text-sm shadow-sm"
                                                    >
                                                        {{ msg.content }}
                                                    </div>
                                                    <div
                                                        class="text-[10px] text-gray-400 text-right mt-0.5"
                                                    >
                                                        {{ msg.user_name }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                v-else-if="msg.role === 'assistant'"
                                                class="flex justify-start items-end gap-2"
                                            >
                                                <div class="relative">
                                                    <div
                                                        @click="toggleMenu($event, idx)"
                                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-sm shrink-0 cursor-pointer hover:opacity-80 transition-opacity"
                                                        title="クリックしてメニューを表示"
                                                    >
                                                        AI
                                                    </div>

                                                    <Teleport to="body">
                                                        <div
                                                            v-if="
                                                                activeMenuMessageId ===
                                                                idx
                                                            "
                                                        >
                                                            <div
                                                                class="fixed inset-0 z-[10000]"
                                                                @click="
                                                                    activeMenuMessageId =
                                                                        null
                                                                "
                                                            ></div>

                                                            <div
                                                                :id="`planner-menu-${idx}`"
                                                                class="fixed bg-white rounded-lg shadow-xl border border-gray-100 overflow-hidden z-[10001] w-48"
                                                                :style="{
                                                                    top:
                                                                        menuPosition.top +
                                                                        'px',
                                                                    left:
                                                                        menuPosition.left +
                                                                        'px',
                                                                }"
                                                            >
                                                                <button
                                                                    @click="
                                                                        createPlanFromMessage(
                                                                            msg.content
                                                                        );
                                                                        activeMenuMessageId =
                                                                            null;
                                                                    "
                                                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors flex items-center gap-2"
                                                                >
                                                                    <span>📝</span>
                                                                    引用してプラン作成
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </Teleport>
                                                </div>
                                                <div class="max-w-[90%]">
                                                    <div
                                                        class="text-xs text-gray-500 mb-0.5 font-bold"
                                                    >
                                                        AI Planner
                                                    </div>
                                                    <div
                                                        class="bg-white border border-indigo-100 text-gray-800 rounded-2xl rounded-tl-none py-3 px-4 text-sm shadow-sm"
                                                    >
                                                        <div
                                                            v-for="(
                                                                segment, sIdx
                                                            ) in parseMessage(
                                                                msg.content
                                                            )"
                                                            :key="sIdx"
                                                        >
                                                            <div
                                                                v-if="
                                                                    segment.type ===
                                                                    'text'
                                                                "
                                                                v-html="
                                                                    marked.parse(
                                                                        segment.content
                                                                    )
                                                                "
                                                                class="prose prose-sm max-w-none prose-indigo"
                                                            ></div>

                                                            <div
                                                                v-else-if="
                                                                    segment.type ===
                                                                    'json'
                                                                "
                                                                class="mt-3 space-y-2"
                                                            >
                                                                <div
                                                                    v-if="
                                                                        segment
                                                                            .data
                                                                            .type ===
                                                                        'plan'
                                                                    "
                                                                    class="bg-indigo-50 rounded-xl p-4 border border-indigo-200"
                                                                >
                                                                    <div
                                                                        class="flex items-start justify-between mb-3"
                                                                    >
                                                                        <div>
                                                                            <h3
                                                                                class="font-bold text-indigo-800 text-lg"
                                                                            >
                                                                                {{
                                                                                    segment
                                                                                        .data
                                                                                        .title
                                                                                }}
                                                                            </h3>
                                                                            <p
                                                                                class="text-xs text-indigo-600 mt-1"
                                                                            >
                                                                                AI提案プラン
                                                                            </p>
                                                                        </div>
                                                                        <button
                                                                            @click="
                                                                                savePlan(
                                                                                    segment.data
                                                                                )
                                                                            "
                                                                            class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 rounded-full hover:bg-indigo-700 transition-colors flex items-center gap-1"
                                                                        >
                                                                            <svg
                                                                                xmlns="http://www.w3.org/2000/svg"
                                                                                viewBox="0 0 20 20"
                                                                                fill="currentColor"
                                                                                class="w-3 h-3"
                                                                            >
                                                                                <path
                                                                                    d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z"
                                                                                />
                                                                            </svg>
                                                                            プランを保存
                                                                        </button>
                                                                    </div>

                                                                    <div
                                                                        class="space-y-3"
                                                                    >
                                                                        <div
                                                                            v-for="(
                                                                                day,
                                                                                dIdx
                                                                            ) in segment
                                                                                .data
                                                                                .itinerary"
                                                                            :key="
                                                                                dIdx
                                                                            "
                                                                            class="bg-white rounded-lg p-3 border border-indigo-100"
                                                                        >
                                                                            <div
                                                                                class="font-bold text-sm text-indigo-700 mb-2"
                                                                            >
                                                                                {{
                                                                                    day.day
                                                                                }}日目
                                                                            </div>
                                                                            <div
                                                                                class="space-y-2"
                                                                            >
                                                                                <div
                                                                                    v-for="(
                                                                                        spot,
                                                                                        spIdx
                                                                                    ) in day.spots"
                                                                                    :key="
                                                                                        spIdx
                                                                                    "
                                                                                    class="flex gap-2 text-sm"
                                                                                >
                                                                                    <div
                                                                                        class="font-mono text-gray-500 text-xs pt-0.5 w-10 shrink-0"
                                                                                    >
                                                                                        {{
                                                                                            spot.time
                                                                                        }}
                                                                                    </div>
                                                                                    <div>
                                                                                        <div
                                                                                            class="font-bold text-gray-800"
                                                                                        >
                                                                                            {{
                                                                                                spot.name
                                                                                            }}
                                                                                        </div>
                                                                                        <div
                                                                                            class="text-xs text-gray-500"
                                                                                        >
                                                                                            {{
                                                                                                spot.description
                                                                                            }}
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div
                                                                    v-if="
                                                                        Array.isArray(
                                                                            segment.data
                                                                        )
                                                                    "
                                                                    class="grid gap-2"
                                                                >
                                                                    <div
                                                                        v-for="(
                                                                            item,
                                                                            iIdx
                                                                        ) in segment.data"
                                                                        :key="
                                                                            iIdx
                                                                        "
                                                                        class="bg-gray-50 rounded-lg p-3 border border-gray-200 hover:bg-gray-100 transition-colors"
                                                                    >
                                                                        <div
                                                                            class="font-bold text-indigo-700 mb-1"
                                                                        >
                                                                            {{
                                                                                item.name
                                                                            }}
                                                                        </div>
                                                                        <div
                                                                            class="text-xs text-gray-600 mb-1"
                                                                        >
                                                                            {{
                                                                                item.description
                                                                            }}
                                                                        </div>
                                                                        <div
                                                                            v-if="
                                                                                item.url
                                                                            "
                                                                            class="text-right"
                                                                        >
                                                                            <a
                                                                                :href="
                                                                                    item.url
                                                                                "
                                                                                target="_blank"
                                                                                class="text-xs text-blue-500 hover:underline"
                                                                            >
                                                                                詳細を見る
                                                                                &rarr;
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            v-if="isAiProcessing"
                                            class="flex justify-start items-end gap-2"
                                        >
                                            <div
                                                class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-sm shrink-0"
                                            >
                                                AI
                                            </div>
                                            <div
                                                class="bg-gray-100 text-gray-500 rounded-2xl rounded-tl-none py-2 px-3 text-xs animate-pulse"
                                            >
                                                AIが入力中...
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <textarea
                                            ref="chatInputTextarea"
                                            v-model="aiChatInput"
                                            @input="adjustTextareaHeight"
                                            @keydown="handleChatKeydown"
                                            rows="1"
                                            class="w-full appearance-none border border-gray-300 rounded-xl py-2 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none overflow-hidden max-h-32"
                                            placeholder="メッセージを入力..."
                                        ></textarea>

                                        <Teleport to="body">
                                            <div
                                                v-if="showPlanRequestModal"
                                                class="fixed inset-0 z-[10002] flex items-center justify-center p-4"
                                            >
                                                <div
                                                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                                                    @click="
                                                        showPlanRequestModal = false
                                                    "
                                                ></div>
                                                <div
                                                    class="bg-white rounded-2xl shadow-xl w-full max-w-lg relative z-10 overflow-hidden flex flex-col max-h-[90vh]"
                                                >
                                                    <div
                                                        class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50"
                                                    >
                                                        <h3
                                                            class="font-bold text-gray-800"
                                                        >
                                                            プラン作成の要望を追加
                                                        </h3>
                                                        <button
                                                            @click="
                                                                showPlanRequestModal = false
                                                            "
                                                            class="text-gray-400 hover:text-gray-600"
                                                        >
                                                            ✕
                                                        </button>
                                                    </div>
                                                    <div
                                                        class="p-4 overflow-y-auto"
                                                    >
                                                        <div class="mb-4">
                                                            <label
                                                                class="block text-xs font-bold text-gray-500 mb-1"
                                                            >
                                                                引用する提案内容
                                                            </label>
                                                            <div
                                                                class="bg-gray-50 p-3 rounded-lg text-xs text-gray-600 max-h-32 overflow-y-auto border border-gray-200 whitespace-pre-wrap"
                                                            >
                                                                {{
                                                                    planRequestQuote
                                                                }}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <label
                                                                class="block text-sm font-bold text-gray-700 mb-2"
                                                            >
                                                                追加の要望（任意）
                                                            </label>
                                                            <textarea
                                                                v-model="
                                                                    planRequestAdditional
                                                                "
                                                                class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent min-h-[100px]"
                                                                placeholder="例：予算は一人3万円以内で、温泉宿がいいです。"
                                                            ></textarea>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-2"
                                                    >
                                                        <button
                                                            @click="
                                                                showPlanRequestModal = false
                                                            "
                                                            class="px-4 py-2 text-gray-600 font-bold hover:bg-gray-200 rounded-lg transition-colors"
                                                        >
                                                            キャンセル
                                                        </button>
                                                        <button
                                                            @click="
                                                                submitPlanRequest
                                                            "
                                                            class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold rounded-lg hover:shadow-lg transform hover:-translate-y-0.5 transition-all"
                                                        >
                                                            プランを作成する
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </Teleport>
                                        <div class="flex gap-2 justify-end">
                                            <button
                                                @click="sendAiMessage(false)"
                                                :disabled="!aiChatInput.trim()"
                                                class="bg-gray-500 text-white rounded-xl px-4 py-2 font-bold hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm flex items-center gap-1"
                                            >
                                                <span>💬</span> チャット送信
                                            </button>
                                            <button
                                                @click="sendAiMessage(true)"
                                                :disabled="
                                                    isAiProcessing ||
                                                    !aiChatInput.trim()
                                                "
                                                class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl px-4 py-2 font-bold hover:from-indigo-600 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all text-sm flex items-center gap-1 shadow-sm"
                                            >
                                                <span
                                                    v-if="isAiProcessing"
                                                    class="animate-spin"
                                                    >🔄</span
                                                >
                                                <span v-else>🤖</span>
                                                クイックンに聞く
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse"
                    >
                        <button
                            type="button"
                            class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm"
                            @click="$emit('close')"
                        >
                            閉じる
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
