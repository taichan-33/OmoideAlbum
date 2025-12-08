<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PostCreator from "@/Components/Timeline/PostCreator.vue";
import PostItem from "@/Components/Timeline/PostItem.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";

const props = defineProps({
    post: Object,
    replies: Array,
});

const postCreatorRef = ref(null);

const handleQuote = (post) => {
    postCreatorRef.value?.setAttachment("App\\Models\\Post", post);
    window.scrollTo({ top: 0, behavior: "smooth" });
};

const handleReply = (post) => {
    // スレッド詳細画面では、返信フォームにフォーカスし、返信先をセットする
    postCreatorRef.value?.setReplyTo(post);
    window.scrollTo({ top: 0, behavior: "smooth" });
};
</script>

<template>
    <AppLayout title="スレッド">
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('timeline.index')"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <i class="bi bi-arrow-left text-xl"></i>
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    スレッド
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Parent Post (Context) -->
                <div v-if="post.parent_post" class="mb-4 opacity-75">
                    <div class="ml-4 border-l-2 border-gray-300 pl-4 pb-4">
                        <PostItem
                            :post="post.parent_post"
                            @quote="handleQuote"
                            @reply="handleReply"
                        />
                    </div>
                </div>

                <!-- Main Post -->
                <div class="mb-6">
                    <PostItem
                        :post="post"
                        class="border-indigo-200 ring-2 ring-indigo-50"
                        @quote="handleQuote"
                        @reply="handleReply"
                    />
                </div>

                <!-- Reply Form -->
                <div class="mb-8">
                    <PostCreator
                        ref="postCreatorRef"
                        :placeholder="'返信を投稿する...'"
                    />
                </div>

                <!-- Replies -->
                <div class="space-y-4">
                    <h3
                        v-if="replies.length > 0"
                        class="font-bold text-gray-600 border-b pb-2 mb-4"
                    >
                        返信一覧
                    </h3>
                    <PostItem
                        v-for="reply in replies"
                        :key="reply.id"
                        :post="reply"
                        @quote="handleQuote"
                        @reply="handleReply"
                    />
                    <div
                        v-if="replies.length === 0"
                        class="text-center text-gray-400 py-4"
                    >
                        まだ返信はありません。
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
