<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PostCreator from "@/Components/Timeline/PostCreator.vue";
import PostItem from "@/Components/Timeline/PostItem.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    posts: Object,
    currentTab: String,
    userStatus: Object,
    partnerStatus: Object,
});

const postCreatorRef = ref(null);
const isEditingStatus = ref(false);
const statusForm = useForm({
    status: "",
});

const startEditStatus = () => {
    statusForm.status = props.userStatus?.status || "";
    isEditingStatus.value = true;
};

const saveStatus = () => {
    statusForm.post(route("timeline.status.update"), {
        preserveScroll: true,
        onSuccess: () => {
            isEditingStatus.value = false;
        },
    });
};

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
                <!-- Status Bar -->
                <div
                    class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-4 mb-6 shadow-sm border border-indigo-100"
                >
                    <div
                        class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center"
                    >
                        <!-- My Status -->
                        <div class="flex-1 w-full">
                            <div
                                class="text-xs font-bold text-indigo-500 mb-1 flex items-center gap-1"
                            >
                                <i class="bi bi-person-circle"></i>
                                あなたの今の気分
                            </div>
                            <div
                                v-if="!isEditingStatus"
                                class="flex items-center gap-2"
                            >
                                <div class="text-gray-800 font-medium text-lg">
                                    {{
                                        userStatus?.status ||
                                        "ステータスを設定してみよう！"
                                    }}
                                </div>
                                <button
                                    @click="startEditStatus"
                                    class="text-gray-400 hover:text-indigo-600 transition"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                            </div>
                            <div v-else class="flex gap-2 items-center">
                                <input
                                    v-model="statusForm.status"
                                    type="text"
                                    class="border-gray-300 rounded-md text-sm w-full focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="例: 温泉に行きたい！"
                                    @keydown.enter="saveStatus"
                                />
                                <button
                                    @click="saveStatus"
                                    class="bg-indigo-600 text-white px-3 py-1.5 rounded-md text-xs font-bold hover:bg-indigo-700 transition whitespace-nowrap"
                                    :disabled="statusForm.processing"
                                >
                                    保存
                                </button>
                                <button
                                    @click="isEditingStatus = false"
                                    class="text-gray-500 hover:text-gray-700 text-xs whitespace-nowrap"
                                >
                                    キャンセル
                                </button>
                            </div>
                        </div>

                        <!-- Partner Status -->
                        <div
                            v-if="partnerStatus"
                            class="flex-1 w-full sm:border-l sm:pl-4 border-indigo-100"
                        >
                            <div
                                class="text-xs font-bold text-pink-500 mb-1 flex items-center gap-1"
                            >
                                <i class="bi bi-heart-fill"></i>
                                {{ partnerStatus.name }}さんの気分
                            </div>
                            <div class="text-gray-800 font-medium text-lg">
                                {{
                                    partnerStatus.status ||
                                    "まだ設定されていません"
                                }}
                            </div>
                            <div
                                class="text-xs text-gray-400 mt-1"
                                v-if="partnerStatus.status_updated_at"
                            >
                                {{
                                    new Date(
                                        partnerStatus.status_updated_at
                                    ).toLocaleDateString()
                                }}
                                更新
                            </div>
                        </div>
                    </div>
                </div>

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
