<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PostCreator from "@/Components/Timeline/PostCreator.vue";
import PostItem from "@/Components/Timeline/PostItem.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted, nextTick } from "vue";

const props = defineProps({
    post: Object, // The post we are "viewing" (for focus/highlight)
    rootPost: Object, // The root of the thread
    replies: Array, // All descendants
});

const postCreatorRef = ref(null);

const handleQuote = (post) => {
    postCreatorRef.value?.setAttachment("App\\Models\\Post", post);
    window.scrollTo({ top: 0, behavior: "smooth" });
};

const handleReply = (post) => {
    postCreatorRef.value?.setReplyTo(post);
    window.scrollTo({ top: 0, behavior: "smooth" });
};

// Scroll to the focused post if it's not the root
onMounted(async () => {
    if (props.post.id !== props.rootPost.id) {
        await nextTick();
        const element = document.getElementById(`post-${props.post.id}`);
        if (element) {
            element.scrollIntoView({ behavior: "smooth", block: "center" });
        }
    }
});
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
                <!-- Root Post -->
                <div class="mb-6">
                    <PostItem
                        :id="`post-${rootPost.id}`"
                        :post="rootPost"
                        class="border-indigo-200 ring-2 ring-indigo-50"
                        :class="{
                            'ring-4 ring-indigo-200': post.id === rootPost.id,
                        }"
                        @quote="handleQuote"
                        @reply="handleReply"
                    />
                </div>

                <!-- Reply Form (Always replies to the Root by default, or specific if clicked) -->
                <!-- Actually, usually we reply to the Root in this view unless specified -->
                <div class="mb-8">
                    <PostCreator
                        ref="postCreatorRef"
                        :placeholder="'返信を投稿する...'"
                        :default-reply-to="rootPost"
                    />
                </div>

                <!-- Replies (All Descendants) -->
                <div class="space-y-4">
                    <h3
                        v-if="replies.length > 0"
                        class="font-bold text-gray-600 border-b pb-2 mb-4"
                    >
                        返信一覧
                    </h3>
                    <div
                        v-for="reply in replies"
                        :key="reply.id"
                        :id="`post-${reply.id}`"
                        class="transition-all duration-500"
                        :class="{
                            'bg-indigo-50 -mx-4 px-4 py-2 rounded':
                                post.id === reply.id,
                        }"
                    >
                        <!-- Indication of parent if it's not the root -->
                        <div
                            v-if="reply.parent_post_id !== rootPost.id"
                            class="text-xs text-gray-400 mb-1 ml-2"
                        >
                            <i class="bi bi-arrow-return-right"></i>
                            Replying to @{{
                                reply.parent_post?.user?.name || "Unknown"
                            }}
                        </div>
                        <PostItem
                            :post="reply"
                            @quote="handleQuote"
                            @reply="handleReply"
                        />
                    </div>
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
