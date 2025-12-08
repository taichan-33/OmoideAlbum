<script setup>
import { useForm, router } from "@inertiajs/vue3";

const props = defineProps({
    photo: Object,
    currentUser: Object,
});

const emit = defineEmits(["close", "photo-updated"]);

const commentForm = useForm({
    comment: "",
});

const close = () => {
    emit("close");
    commentForm.reset();
};

const submitComment = () => {
    if (!props.photo) return;

    commentForm.post(route("photo-comments.store", props.photo.id), {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset();
            emit("photo-updated");
        },
    });
};

const deleteComment = (commentId) => {
    if (!confirm("コメントを削除しますか？")) return;

    router.delete(route("photo-comments.destroy", commentId), {
        preserveScroll: true,
        onSuccess: () => {
            emit("photo-updated");
        },
    });
};
</script>

<template>
    <Teleport to="body">
        <div
            v-if="photo"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        >
            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-black/90 backdrop-blur-sm transition-opacity"
                @click="close"
            ></div>

            <!-- Modal Content -->
            <div
                class="relative bg-black rounded-2xl overflow-hidden shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col md:flex-row"
                @click.stop
            >
                <!-- Close Button -->
                <button
                    @click="close"
                    class="absolute top-4 right-4 z-10 text-white/70 hover:text-white bg-black/20 hover:bg-black/40 rounded-full p-2 transition"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        ></path>
                    </svg>
                </button>

                <!-- Image Section -->
                <div
                    class="flex-1 bg-black flex items-center justify-center min-h-[40vh] md:min-h-0"
                >
                    <img
                        :src="photo.path"
                        class="max-w-full max-h-[85vh] object-contain"
                    />
                </div>

                <!-- Sidebar Section (Comments) -->
                <div
                    class="w-full md:w-96 bg-white flex flex-col h-[50vh] md:h-auto border-l border-gray-800"
                >
                    <!-- Header -->
                    <div
                        class="p-4 border-b border-gray-100 flex items-center justify-between bg-white z-10"
                    >
                        <h3 class="font-bold text-gray-900">コメント</h3>
                        <span class="text-xs text-gray-500"
                            >{{
                                photo.comments ? photo.comments.length : 0
                            }}件</span
                        >
                    </div>

                    <!-- Comments List -->
                    <div
                        class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50"
                    >
                        <div
                            v-if="photo.caption"
                            class="bg-white p-3 rounded-xl shadow-sm border border-gray-100"
                        >
                            <p class="text-sm text-gray-800">
                                {{ photo.caption }}
                            </p>
                        </div>

                        <div
                            v-if="photo.comments && photo.comments.length > 0"
                            class="space-y-4"
                        >
                            <div
                                v-for="comment in photo.comments"
                                :key="comment.id"
                                class="flex gap-3"
                            >
                                <img
                                    :src="comment.user_profile_photo_url"
                                    :alt="comment.user_name"
                                    class="flex-shrink-0 w-8 h-8 rounded-full object-cover border border-gray-200"
                                />
                                <div class="flex-1">
                                    <div
                                        class="bg-white p-3 rounded-r-xl rounded-bl-xl shadow-sm border border-gray-100"
                                    >
                                        <div
                                            class="flex justify-between items-start mb-1"
                                        >
                                            <span
                                                class="text-xs font-bold text-gray-900"
                                                >{{ comment.user_name }}</span
                                            >
                                            <span
                                                class="text-[10px] text-gray-400"
                                                >{{ comment.created_at }}</span
                                            >
                                        </div>
                                        <p
                                            class="text-sm text-gray-700 whitespace-pre-wrap"
                                        >
                                            {{ comment.comment }}
                                        </p>
                                    </div>
                                    <div
                                        v-if="
                                            currentUser &&
                                            currentUser.id === comment.user_id
                                        "
                                        class="flex justify-end mt-1"
                                    >
                                        <button
                                            @click="deleteComment(comment.id)"
                                            class="text-[10px] text-red-400 hover:text-red-600"
                                        >
                                            削除
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="text-center py-8 text-gray-400 text-sm"
                        >
                            まだコメントはありません。<br />最初のコメントを投稿しましょう！
                        </div>
                    </div>

                    <!-- Input Area -->
                    <div class="p-4 border-t border-gray-100 bg-white">
                        <form
                            @submit.prevent="submitComment"
                            class="flex gap-2"
                        >
                            <input
                                v-model="commentForm.comment"
                                type="text"
                                placeholder="コメントを入力..."
                                class="flex-1 border-gray-200 rounded-full text-sm focus:ring-black focus:border-black bg-gray-50 px-4"
                                required
                            />
                            <button
                                type="submit"
                                :disabled="
                                    commentForm.processing ||
                                    !commentForm.comment
                                "
                                class="bg-black text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-800 transition disabled:opacity-50"
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
                                        d="M5 12h14M12 5l7 7-7 7"
                                    ></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
