<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PostCreator from "@/Components/Timeline/PostCreator.vue";
import PostItem from "@/Components/Timeline/PostItem.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    posts: Object,
    currentTab: String,
});

const postCreatorRef = ref(null);

const handleQuote = (post) => {
    // 引用リツイート: PostCreatorにセット
    postCreatorRef.value?.setAttachment("App\\Models\\Post", post);
    // スクロールトップ
    window.scrollTo({ top: 0, behavior: "smooth" });
};

const handleReply = (post) => {
    // 返信: PostCreatorにセット
    postCreatorRef.value?.setReplyTo(post);
    // スクロールトップ
    window.scrollTo({ top: 0, behavior: "smooth" });
};
</script>

<template>
    <AppLayout title="タイムライン">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                タイムライン
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Post Creator -->
                <PostCreator ref="postCreatorRef" />

                <!-- Tabs -->
                <div
                    class="flex border-b border-gray-200 mb-6 bg-white rounded-t-lg"
                >
                    <Link
                        :href="route('timeline.index', { tab: 'timeline' })"
                        class="flex-1 py-3 text-center font-medium text-sm transition rounded-tl-lg hover:bg-gray-50"
                        :class="
                            currentTab === 'timeline'
                                ? 'border-b-2 border-indigo-600 text-indigo-600'
                                : 'text-gray-500'
                        "
                    >
                        みんなの投稿
                    </Link>
                    <Link
                        :href="route('timeline.index', { tab: 'my_posts' })"
                        class="flex-1 py-3 text-center font-medium text-sm transition hover:bg-gray-50"
                        :class="
                            currentTab === 'my_posts'
                                ? 'border-b-2 border-indigo-600 text-indigo-600'
                                : 'text-gray-500'
                        "
                    >
                        自分の投稿
                    </Link>
                    <Link
                        :href="route('timeline.index', { tab: 'my_replies' })"
                        class="flex-1 py-3 text-center font-medium text-sm transition rounded-tr-lg hover:bg-gray-50"
                        :class="
                            currentTab === 'my_replies'
                                ? 'border-b-2 border-indigo-600 text-indigo-600'
                                : 'text-gray-500'
                        "
                    >
                        自分の返信
                    </Link>
                </div>

                <!-- Posts Feed -->
                <div class="space-y-4">
                    <PostItem
                        v-for="post in posts.data"
                        :key="post.id"
                        :post="post"
                        @quote="handleQuote"
                        @reply="handleReply"
                    />

                    <!-- Pagination (Simple) -->
                    <div
                        v-if="posts.links.length > 3"
                        class="flex justify-center mt-6"
                    >
                        <!-- Pagination links implementation if needed -->
                    </div>

                    <div
                        v-if="posts.data.length === 0"
                        class="text-center text-gray-500 py-10"
                    >
                        投稿がありません。
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
