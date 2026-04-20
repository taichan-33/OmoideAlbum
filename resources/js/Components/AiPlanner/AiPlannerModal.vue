<script setup>
import { toRef } from "vue";
import AiPlannerMessageList from "@/Components/AiPlanner/AiPlannerMessageList.vue";
import AiPlannerPlanRequestModal from "@/Components/AiPlanner/AiPlannerPlanRequestModal.vue";
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
    closeMenu,
    closePlanRequestModal,
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
    setPlanRequestAdditional,
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
                                        <AiPlannerMessageList
                                            :messages="aiChatMessages"
                                            :active-menu-message-id="
                                                activeMenuMessageId
                                            "
                                            :menu-position="menuPosition"
                                            :is-ai-processing="
                                                isAiProcessing
                                            "
                                            :parse-message="parseMessage"
                                            @toggle-menu="toggleMenu"
                                            @close-menu="closeMenu"
                                            @create-plan="
                                                (content) => {
                                                    createPlanFromMessage(
                                                        content
                                                    );
                                                    closeMenu();
                                                }
                                            "
                                            @save-plan="savePlan"
                                        />
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

                                        <AiPlannerPlanRequestModal
                                            :show="showPlanRequestModal"
                                            :quote="planRequestQuote"
                                            :additional="
                                                planRequestAdditional
                                            "
                                            @close="closePlanRequestModal"
                                            @submit="submitPlanRequest"
                                            @update:additional="
                                                setPlanRequestAdditional
                                            "
                                        />
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
