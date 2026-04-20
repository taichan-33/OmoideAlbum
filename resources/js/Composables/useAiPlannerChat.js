import axios from "axios";
import { router } from "@inertiajs/vue3";
import {
    computed,
    nextTick,
    onBeforeUnmount,
    ref,
    watch,
} from "vue";

export function useAiPlannerChat({ show, selectedPrefecture, user }) {
    const aiChatMessages = ref([]);
    const aiChatInput = ref("");
    const chatInputTextarea = ref(null);
    const isAiProcessing = ref(false);
    const activeMenuMessageId = ref(null);
    const menuPosition = ref({ top: 0, left: 0 });
    const showPlanRequestModal = ref(false);
    const planRequestQuote = ref("");
    const planRequestAdditional = ref("");
    const chatContainer = ref(null);
    const modalSizeIndex = ref(0);
    const chatPollingInterval = ref(null);

    const modalWidthClass = computed(() => {
        switch (modalSizeIndex.value) {
            case 0:
                return "sm:max-w-lg";
            case 1:
                return "sm:max-w-2xl";
            case 2:
                return "sm:max-w-4xl";
            default:
                return "sm:max-w-lg";
        }
    });

    const chatHeightClass = computed(() => {
        switch (modalSizeIndex.value) {
            case 0:
                return "h-64";
            case 1:
                return "h-96";
            case 2:
                return "h-[500px]";
            default:
                return "h-64";
        }
    });

    const parseMessage = (content) => {
        if (!content) return [];

        const regex = /```json\s*([\s\S]*?)\s*```/g;
        const segments = [];
        let lastIndex = 0;
        let match;

        while ((match = regex.exec(content)) !== null) {
            if (match.index > lastIndex) {
                segments.push({
                    type: "text",
                    content: content.slice(lastIndex, match.index),
                });
            }

            try {
                segments.push({
                    type: "json",
                    data: JSON.parse(match[1]),
                });
            } catch {
                segments.push({
                    type: "text",
                    content: match[0],
                });
            }

            lastIndex = regex.lastIndex;
        }

        if (lastIndex < content.length) {
            segments.push({
                type: "text",
                content: content.slice(lastIndex),
            });
        }

        return segments;
    };

    const stopPolling = () => {
        if (chatPollingInterval.value) {
            clearInterval(chatPollingInterval.value);
            chatPollingInterval.value = null;
        }
    };

    const resetState = () => {
        stopPolling();
        aiChatMessages.value = [];
        aiChatInput.value = "";
        isAiProcessing.value = false;
        activeMenuMessageId.value = null;
        showPlanRequestModal.value = false;
        planRequestQuote.value = "";
        planRequestAdditional.value = "";
    };

    const adjustTextareaHeight = () => {
        const textarea = chatInputTextarea.value;
        if (textarea) {
            textarea.style.height = "auto";
            textarea.style.height = textarea.scrollHeight + "px";
        }
    };

    const increaseModalSize = () => {
        if (modalSizeIndex.value < 2) modalSizeIndex.value++;
    };

    const decreaseModalSize = () => {
        if (modalSizeIndex.value > 0) modalSizeIndex.value--;
    };

    const scrollToBottom = () => {
        if (chatContainer.value) {
            setTimeout(() => {
                chatContainer.value.scrollTop =
                    chatContainer.value.scrollHeight;
            }, 100);
        }
    };

    const fetchChatHistory = async (code, name, shouldScroll = false) => {
        try {
            const response = await axios.get(
                route("ai-planner.index", { prefectureCode: code })
            );
            const history = response.data;
            const isNewMessage = history.length > aiChatMessages.value.length;

            if (history.length === 0 && aiChatMessages.value.length === 0) {
                aiChatMessages.value = [
                    {
                        role: "system",
                        content: `「${name}」への旅行計画を立てましょう！どのような旅行にしたいですか？`,
                    },
                ];
            } else {
                aiChatMessages.value = history;
            }

            if (shouldScroll) {
                scrollToBottom();
            } else if (isNewMessage && chatContainer.value) {
                const { scrollTop, scrollHeight, clientHeight } =
                    chatContainer.value;

                if (scrollHeight - scrollTop - clientHeight < 100) {
                    scrollToBottom();
                }
            }
        } catch (error) {
            console.error("Failed to fetch chat history", error);
        }
    };

    const startPolling = (code, name) => {
        stopPolling();
        chatPollingInterval.value = setInterval(() => {
            if (show.value && selectedPrefecture.value) {
                fetchChatHistory(code, name, false);
            }
        }, 5000);
    };

    const createPlanFromMessage = (content) => {
        planRequestQuote.value = content;
        planRequestAdditional.value = "";
        showPlanRequestModal.value = true;
    };

    const closeMenu = () => {
        activeMenuMessageId.value = null;
    };

    const closePlanRequestModal = () => {
        showPlanRequestModal.value = false;
    };

    const setPlanRequestAdditional = (value) => {
        planRequestAdditional.value = value;
    };

    const sendAiMessage = async (triggerAi = true) => {
        if (!aiChatInput.value.trim() || !selectedPrefecture.value) return;

        const message = aiChatInput.value;
        aiChatInput.value = "";

        if (chatInputTextarea.value) {
            chatInputTextarea.value.style.height = "auto";
        }

        aiChatMessages.value.push({
            id: `temp-${Date.now()}`,
            user_id: user.value?.id,
            user_name: user.value?.name,
            message,
            content: message,
            is_ai: false,
            is_me: true,
            created_at: new Date().toISOString(),
        });

        scrollToBottom();

        if (triggerAi) {
            isAiProcessing.value = true;
            scrollToBottom();
        }

        try {
            await axios.post(route("ai-planner.store"), {
                message,
                trigger_ai: triggerAi,
                prefectureCode: selectedPrefecture.value.code,
            });

            await fetchChatHistory(
                selectedPrefecture.value.code,
                selectedPrefecture.value.name
            );
        } catch (error) {
            console.error("Failed to send message:", error);
        } finally {
            if (triggerAi) {
                isAiProcessing.value = false;
            }
        }
    };

    const submitPlanRequest = () => {
        aiChatInput.value = `以下の提案内容を元に、詳細な旅程表（プラン）をJSON形式で作成してください。\n\n引用：\n${planRequestQuote.value}\n\n追加の要望：\n${planRequestAdditional.value}`;
        showPlanRequestModal.value = false;
        sendAiMessage(true);
    };

    const savePlan = (planData) => {
        if (!selectedPrefecture.value) return;
        if (!confirm("このプランを保存しますか？")) return;

        router.post(
            route("suggestions.storeFromChat"),
            {
                title: planData.title,
                content: planData.content,
                accommodation: planData.accommodation,
                local_food: planData.local_food,
                itinerary: planData.itinerary,
                prefecture_code: selectedPrefecture.value.code,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    alert("プランを保存しました！");
                },
                onError: (errors) => {
                    console.error("Failed to save plan:", errors);
                    alert("プランの保存に失敗しました。");
                },
            }
        );
    };

    const handleChatKeydown = (e) => {
        if (e.isComposing) return;

        if (e.key === "Enter" && (e.metaKey || e.ctrlKey)) {
            e.preventDefault();
            sendAiMessage(true);
        }
    };

    const toggleMenu = async (event, idx) => {
        if (activeMenuMessageId.value === idx) {
            closeMenu();
            return;
        }

        const trigger = event.currentTarget;
        if (!trigger || typeof trigger.getBoundingClientRect !== "function") {
            return;
        }

        const rect = trigger.getBoundingClientRect();
        const viewportPadding = 8;

        activeMenuMessageId.value = idx;
        menuPosition.value = {
            top: rect.bottom + 8,
            left: rect.left,
        };

        await nextTick();

        const menuEl = document.getElementById(`planner-menu-${idx}`);
        if (!menuEl || typeof menuEl.getBoundingClientRect !== "function") {
            return;
        }

        const menuRect = menuEl.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.bottom;

        let top = rect.bottom + 8;
        if (spaceBelow < menuRect.height + 12) {
            top = rect.top - menuRect.height - 8;
        }
        top = Math.max(viewportPadding, top);
        top = Math.min(
            top,
            window.innerHeight - menuRect.height - viewportPadding
        );

        let left = rect.left;
        if (left + menuRect.width > window.innerWidth - viewportPadding) {
            left = window.innerWidth - menuRect.width - viewportPadding;
        }
        if (left < viewportPadding) {
            left = viewportPadding;
        }

        menuPosition.value = { top, left };
    };

    watch(aiChatInput, () => {
        nextTick(() => {
            adjustTextareaHeight();
        });
    });

    watch(
        [show, selectedPrefecture],
        async ([isVisible, prefecture]) => {
            if (!isVisible || !prefecture) {
                resetState();
                return;
            }

            aiChatMessages.value = [];
            await fetchChatHistory(prefecture.code, prefecture.name, true);
            startPolling(prefecture.code, prefecture.name);
        },
        { immediate: true }
    );

    onBeforeUnmount(() => {
        stopPolling();
    });

    return {
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
    };
}
