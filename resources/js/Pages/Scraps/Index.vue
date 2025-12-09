<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import ScrapCard from "@/Components/Scrap/ScrapCard.vue";
import { useForm, Head } from "@inertiajs/vue3";

const props = defineProps({
    scraps: Array,
});

const form = useForm({
    url: "",
});

const submit = () => {
    form.post(route("scraps.store"), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <AppLayout title="スクラップブック">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                スクラップブック 📌
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Input Area -->
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8 p-6"
                >
                    <form @submit.prevent="submit" class="flex gap-4">
                        <div class="flex-grow">
                            <input
                                v-model="form.url"
                                type="url"
                                placeholder="行きたい場所のURLを貼り付けてください (Instagram, 食べログ, etc.)"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                required
                            />
                            <div
                                v-if="form.errors.url"
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ form.errors.url }}
                            </div>
                        </div>
                        <button
                            type="submit"
                            class="bg-black text-white px-6 py-2 rounded-md hover:bg-gray-800 transition disabled:opacity-50 flex items-center gap-2"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing" class="animate-spin"
                                >↻</span
                            >
                            <span>保存</span>
                        </button>
                    </form>
                </div>

                <!-- Masonry Grid -->
                <div
                    v-if="scraps.length > 0"
                    class="columns-1 sm:columns-2 md:columns-3 lg:columns-4 gap-4 space-y-4"
                >
                    <ScrapCard
                        v-for="scrap in scraps"
                        :key="scrap.id"
                        :scrap="scrap"
                    />
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-20 text-gray-500">
                    <div class="text-6xl mb-4">📌</div>
                    <p class="text-lg">まだスクラップがありません。</p>
                    <p class="text-sm">
                        気になった場所のURLを保存して、次の旅のヒントにしましょう！
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
