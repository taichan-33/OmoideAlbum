<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PostCreator from "@/Components/Timeline/PostCreator.vue";
import AttachmentCard from "@/Components/Timeline/AttachmentCard.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    posts: Object,
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = (now - date) / 1000; // seconds

    if (diff < 60) return "今";
    if (diff < 3600) return `${Math.floor(diff / 60)}分前`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}時間前`;
    return date.toLocaleDateString("ja-JP", { month: "short", day: "numeric" });
};
</script>

<template>
    <Head title="思い出タイムライン" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                思い出タイムライン
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <!-- Post Creator -->
                <PostCreator />

                <!-- Timeline Feed -->
                <div class="space-y-4">
                    <div
                        v-for="post in posts.data"
                        :key="post.id"
                        class="bg-white p-4 rounded-lg shadow border border-gray-100"
                    >
                        <div class="flex gap-3">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-lg font-bold text-indigo-600"
                                >
                                    {{ post.user.name.charAt(0) }}
                                </div>
                            </div>
                            <div class="flex-grow">
                                <div
                                    class="flex justify-between items-baseline"
                                >
                                    <div class="font-bold text-gray-900">
                                        {{ post.user.name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ formatDate(post.created_at) }}
                                    </div>
                                </div>

                                <div
                                    v-if="post.content"
                                    class="mt-1 text-gray-800 whitespace-pre-wrap"
                                >
                                    {{ post.content }}
                                </div>

                                <!-- Attachment -->
                                <AttachmentCard
                                    v-if="post.attachment"
                                    :type="post.attachment_type"
                                    :attachment="post.attachment"
                                />

                                <!-- Actions (Placeholder) -->
                                <div
                                    class="mt-3 flex gap-6 text-gray-400 text-sm"
                                >
                                    <button
                                        class="hover:text-blue-500 flex items-center gap-1"
                                    >
                                        <i class="bi bi-chat"></i> 0
                                    </button>
                                    <button
                                        class="hover:text-green-500 flex items-center gap-1"
                                    >
                                        <i class="bi bi-arrow-repeat"></i> 0
                                    </button>
                                    <button
                                        class="hover:text-red-500 flex items-center gap-1"
                                    >
                                        <i class="bi bi-heart"></i> 0
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="posts.links.length > 3"
                        class="flex justify-center mt-6"
                    >
                        <!-- Simple pagination links implementation if needed, or use Laravel's pagination links -->
                    </div>

                    <div
                        v-if="posts.data.length === 0"
                        class="text-center text-gray-500 py-10"
                    >
                        まだ投稿がありません。<br />
                        最初の思い出をシェアしましょう！
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
