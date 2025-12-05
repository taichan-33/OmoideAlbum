<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    tripId: Number,
    items: Array,
});

const newItemName = ref("");
const isAdding = ref(false);

const form = useForm({
    name: "",
});

const updateForm = useForm({
    is_checked: false,
});

const addItem = () => {
    if (!newItemName.value) return;

    form.name = newItemName.value;
    form.post(route("packing-items.store", props.tripId), {
        preserveScroll: true,
        onSuccess: () => {
            newItemName.value = "";
            isAdding.value = false;
        },
    });
};

const toggleCheck = (item) => {
    updateForm.is_checked = !item.is_checked;
    // Optimistic UI update
    item.is_checked = !item.is_checked;

    updateForm.put(route("packing-items.update", item.id), {
        preserveScroll: true,
        onError: () => {
            // Revert on error
            item.is_checked = !item.is_checked;
        },
    });
};

const deleteItem = (item) => {
    if (!confirm("このアイテムを削除しますか？")) return;

    useForm({}).delete(route("packing-items.destroy", item.id), {
        preserveScroll: true,
    });
};

const addFromTemplate = (template) => {
    if (!confirm("テンプレートからアイテムを追加しますか？")) return;

    useForm({ template: template }).post(
        route("packing-items.store-batch", props.tripId),
        {
            preserveScroll: true,
        }
    );
};
</script>

<template>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="text-xl">🧳</span> 持ち物リスト
            </h3>
            <div class="relative group">
                <button
                    class="text-sm text-blue-600 font-medium hover:text-blue-800 flex items-center gap-1"
                >
                    テンプレートから追加
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
                            d="M19 9l-7 7-7-7"
                        ></path>
                    </svg>
                </button>
                <div
                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 hidden group-hover:block z-10"
                >
                    <button
                        @click="addFromTemplate('basic')"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        基本セット
                    </button>
                    <button
                        @click="addFromTemplate('onsen')"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        温泉セット
                    </button>
                    <button
                        @click="addFromTemplate('business')"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        出張セット
                    </button>
                    <button
                        @click="addFromTemplate('summer')"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        夏旅セット
                    </button>
                    <button
                        @click="addFromTemplate('winter')"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        冬旅セット
                    </button>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mb-6" v-if="items.length > 0">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>準備完了率</span>
                <span
                    >{{
                        Math.round(
                            (items.filter((i) => i.is_checked).length /
                                items.length) *
                                100
                        )
                    }}%</span
                >
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div
                    class="bg-blue-500 h-2 rounded-full transition-all duration-500"
                    :style="{
                        width:
                            (items.filter((i) => i.is_checked).length /
                                items.length) *
                                100 +
                            '%',
                    }"
                ></div>
            </div>
        </div>

        <!-- Item List -->
        <div class="space-y-2 mb-4">
            <div
                v-for="item in items"
                :key="item.id"
                class="flex items-center justify-between group p-2 rounded-lg hover:bg-gray-50 transition"
            >
                <label class="flex items-center gap-3 cursor-pointer flex-grow">
                    <div class="relative flex items-center">
                        <input
                            type="checkbox"
                            :checked="item.is_checked"
                            @change="toggleCheck(item)"
                            class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 transition"
                        />
                    </div>
                    <span
                        class="text-gray-700 transition"
                        :class="{
                            'line-through text-gray-400': item.is_checked,
                        }"
                    >
                        {{ item.name }}
                    </span>
                </label>
                <button
                    @click="deleteItem(item)"
                    class="text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition px-2"
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
                </button>
            </div>
            <div
                v-if="items.length === 0"
                class="text-center text-gray-400 py-4 text-sm"
            >
                まだ持ち物が登録されていません
            </div>
        </div>

        <!-- Add Item Form -->
        <div class="mt-4">
            <div v-if="isAdding" class="flex gap-2">
                <input
                    v-model="newItemName"
                    type="text"
                    placeholder="アイテム名を入力"
                    class="flex-grow border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                    @keydown.enter="addItem"
                    ref="newItemInput"
                />
                <button
                    @click="addItem"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition"
                >
                    追加
                </button>
                <button
                    @click="isAdding = false"
                    class="text-gray-500 px-2 hover:text-gray-700"
                >
                    ✕
                </button>
            </div>
            <button
                v-else
                @click="isAdding = true"
                class="w-full py-2 border-2 border-dashed border-gray-200 rounded-lg text-gray-500 text-sm font-medium hover:border-blue-300 hover:text-blue-600 transition flex items-center justify-center gap-2"
            >
                <span class="text-lg">+</span> アイテムを追加
            </button>
        </div>
    </div>
</template>
