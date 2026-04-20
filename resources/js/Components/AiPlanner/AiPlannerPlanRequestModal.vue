<script setup>
defineProps({
    show: Boolean,
    quote: {
        type: String,
        default: "",
    },
    additional: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["close", "submit", "update:additional"]);
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-[10002] flex items-center justify-center p-4"
        >
            <div
                class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                @click="emit('close')"
            ></div>
            <div
                class="bg-white rounded-2xl shadow-xl w-full max-w-lg relative z-10 overflow-hidden flex flex-col max-h-[90vh]"
            >
                <div
                    class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50"
                >
                    <h3 class="font-bold text-gray-800">
                        プラン作成の要望を追加
                    </h3>
                    <button
                        class="text-gray-400 hover:text-gray-600"
                        @click="emit('close')"
                    >
                        ✕
                    </button>
                </div>
                <div class="p-4 overflow-y-auto">
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-500 mb-1">
                            引用する提案内容
                        </label>
                        <div
                            class="bg-gray-50 p-3 rounded-lg text-xs text-gray-600 max-h-32 overflow-y-auto border border-gray-200 whitespace-pre-wrap"
                        >
                            {{ quote }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            追加の要望（任意）
                        </label>
                        <textarea
                            class="w-full border border-gray-300 rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent min-h-[100px]"
                            placeholder="例：予算は一人3万円以内で、温泉宿がいいです。"
                            :value="additional"
                            @input="
                                emit(
                                    'update:additional',
                                    $event.target.value
                                )
                            "
                        ></textarea>
                    </div>
                </div>
                <div
                    class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-2"
                >
                    <button
                        class="px-4 py-2 text-gray-600 font-bold hover:bg-gray-200 rounded-lg transition-colors"
                        @click="emit('close')"
                    >
                        キャンセル
                    </button>
                    <button
                        class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold rounded-lg hover:shadow-lg transform hover:-translate-y-0.5 transition-all"
                        @click="emit('submit')"
                    >
                        プランを作成する
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
