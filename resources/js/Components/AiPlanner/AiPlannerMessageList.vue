<script setup>
import { marked } from "marked";

defineProps({
    messages: {
        type: Array,
        required: true,
    },
    activeMenuMessageId: {
        type: [Number, String],
        default: null,
    },
    menuPosition: {
        type: Object,
        required: true,
    },
    isAiProcessing: Boolean,
    parseMessage: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits([
    "toggle-menu",
    "close-menu",
    "create-plan",
    "save-plan",
]);
</script>

<template>
    <div v-for="(msg, idx) in messages" :key="idx" class="mb-3">
        <div
            v-if="msg.role === 'system'"
            class="text-xs text-gray-400 text-center mb-2"
        >
            {{ msg.content }}
        </div>

        <div v-else-if="msg.is_me" class="flex justify-end items-end gap-1">
            <div class="max-w-[80%]">
                <div
                    class="bg-blue-600 text-white rounded-2xl rounded-tr-none py-2 px-3 text-sm shadow-sm"
                >
                    {{ msg.content }}
                </div>
                <div class="text-[10px] text-gray-400 text-right mt-0.5">
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
                    class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-sm shrink-0 cursor-pointer hover:opacity-80 transition-opacity"
                    title="クリックしてメニューを表示"
                    @click="emit('toggle-menu', $event, idx)"
                >
                    AI
                </div>

                <Teleport to="body">
                    <div v-if="activeMenuMessageId === idx">
                        <div
                            class="fixed inset-0 z-[10000]"
                            @click="emit('close-menu')"
                        ></div>

                        <div
                            :id="`planner-menu-${idx}`"
                            class="fixed bg-white rounded-lg shadow-xl border border-gray-100 overflow-hidden z-[10001] w-48"
                            :style="{
                                top: `${menuPosition.top}px`,
                                left: `${menuPosition.left}px`,
                            }"
                        >
                            <button
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors flex items-center gap-2"
                                @click="emit('create-plan', msg.content)"
                            >
                                <span>📝</span>
                                引用してプラン作成
                            </button>
                        </div>
                    </div>
                </Teleport>
            </div>

            <div class="max-w-[90%]">
                <div class="text-xs text-gray-500 mb-0.5 font-bold">
                    AI Planner
                </div>
                <div
                    class="bg-white border border-indigo-100 text-gray-800 rounded-2xl rounded-tl-none py-3 px-4 text-sm shadow-sm"
                >
                    <div
                        v-for="(segment, sIdx) in parseMessage(msg.content)"
                        :key="sIdx"
                    >
                        <div
                            v-if="segment.type === 'text'"
                            class="prose prose-sm max-w-none prose-indigo"
                            v-html="marked.parse(segment.content)"
                        ></div>

                        <div
                            v-else-if="segment.type === 'json'"
                            class="mt-3 space-y-2"
                        >
                            <div
                                v-if="segment.data.type === 'plan'"
                                class="bg-indigo-50 rounded-xl p-4 border border-indigo-200"
                            >
                                <div
                                    class="flex items-start justify-between mb-3"
                                >
                                    <div>
                                        <h3
                                            class="font-bold text-indigo-800 text-lg"
                                        >
                                            {{ segment.data.title }}
                                        </h3>
                                        <p class="text-xs text-indigo-600 mt-1">
                                            AI提案プラン
                                        </p>
                                    </div>
                                    <button
                                        class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 rounded-full hover:bg-indigo-700 transition-colors flex items-center gap-1"
                                        @click="emit('save-plan', segment.data)"
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

                                <div class="space-y-3">
                                    <div
                                        v-for="(day, dIdx) in segment.data.itinerary"
                                        :key="dIdx"
                                        class="bg-white rounded-lg p-3 border border-indigo-100"
                                    >
                                        <div
                                            class="font-bold text-sm text-indigo-700 mb-2"
                                        >
                                            {{ day.day }}日目
                                        </div>
                                        <div class="space-y-2">
                                            <div
                                                v-for="(spot, spIdx) in day.spots"
                                                :key="spIdx"
                                                class="flex gap-2 text-sm"
                                            >
                                                <div
                                                    class="font-mono text-gray-500 text-xs pt-0.5 w-10 shrink-0"
                                                >
                                                    {{ spot.time }}
                                                </div>
                                                <div>
                                                    <div
                                                        class="font-bold text-gray-800"
                                                    >
                                                        {{ spot.name }}
                                                    </div>
                                                    <div
                                                        class="text-xs text-gray-500"
                                                    >
                                                        {{ spot.description }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-if="Array.isArray(segment.data)"
                                class="grid gap-2"
                            >
                                <div
                                    v-for="(item, iIdx) in segment.data"
                                    :key="iIdx"
                                    class="bg-gray-50 rounded-lg p-3 border border-gray-200 hover:bg-gray-100 transition-colors"
                                >
                                    <div class="font-bold text-indigo-700 mb-1">
                                        {{ item.name }}
                                    </div>
                                    <div class="text-xs text-gray-600 mb-1">
                                        {{ item.description }}
                                    </div>
                                    <div v-if="item.url" class="text-right">
                                        <a
                                            :href="item.url"
                                            target="_blank"
                                            class="text-xs text-blue-500 hover:underline"
                                        >
                                            詳細を見る &rarr;
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

    <div v-if="isAiProcessing" class="flex justify-start items-end gap-2">
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
</template>
