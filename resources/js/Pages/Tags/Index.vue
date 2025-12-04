<script setup>
import { useForm, Head } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    tags: Array,
});

const form = useForm({
    name: "",
});

const submit = () => {
    form.post(route("tags.store"), {
        onSuccess: () => form.reset(),
    });
};

const deleteTag = (id) => {
    if (confirm("本当にこのタグを削除しますか？")) {
        useForm({}).delete(route("tags.destroy", id));
    }
};
</script>

<template>
    <Head title="タグ管理" />

    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row gap-8 items-start">
                <!-- Create Tag Form -->
                <div
                    class="w-full md:w-1/3 bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sticky top-8"
                >
                    <h2
                        class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2"
                    >
                        <span class="text-xl">🏷️</span> 新しいタグ
                    </h2>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >タグ名</label
                            >
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="例: 温泉, 記念日"
                                class="w-full border-gray-200 rounded-xl focus:ring-black focus:border-black"
                                required
                            />
                            <div
                                v-if="form.errors.name"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-black text-white py-3 rounded-xl font-bold hover:bg-gray-800 transition disabled:opacity-50"
                        >
                            作成する
                        </button>
                    </form>
                </div>

                <!-- Tag List -->
                <div class="w-full md:w-2/3">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">
                        登録済みタグ
                    </h2>

                    <div
                        v-if="tags.length"
                        class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden"
                    >
                        <ul class="divide-y divide-gray-100">
                            <li
                                v-for="tag in tags"
                                :key="tag.id"
                                class="p-4 flex justify-between items-center hover:bg-gray-50 transition"
                            >
                                <div class="flex items-center gap-3">
                                    <span
                                        class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm font-medium"
                                    >
                                        #{{ tag.name }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        {{
                                            new Date(
                                                tag.created_at
                                            ).toLocaleDateString()
                                        }}
                                    </span>
                                </div>

                                <button
                                    @click="deleteTag(tag.id)"
                                    class="text-gray-400 hover:text-red-500 transition p-2 rounded-full hover:bg-red-50"
                                >
                                    <svg
                                        class="w-5 h-5"
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
                            </li>
                        </ul>
                    </div>
                    <div
                        v-else
                        class="text-center py-12 bg-gray-50 rounded-3xl border border-dashed border-gray-200"
                    >
                        <p class="text-gray-400">タグがまだありません</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
