<script setup>
import { ref, computed } from "vue";
import { Head, Link, useForm, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    trip: Object,
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const photoForm = useForm({
    photo: null,
    caption: "",
});

const submitPhoto = () => {
    photoForm.post(route("photos.store", props.trip.id), {
        onSuccess: () => {
            photoForm.reset();
            // プレビュー画像などをクリアする処理があればここ
        },
    });
};

const deleteTrip = () => {
    if (
        confirm(
            "本当にこの旅行の思い出を削除しますか？\n関連する写真もすべて削除されます。"
        )
    ) {
        router.delete(route("trips.destroy", props.trip.id));
    }
};

const summarizeTrip = () => {
    if (
        confirm(
            "AIに要約を依頼します。メモの内容がAIに送信されますが、よろしいですか？"
        )
    ) {
        router.post(route("trips.summarize", props.trip.id));
    }
};
const formatDate = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("ja-JP", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    });
};

// --- Photo Modal & Comments ---
const selectedPhoto = ref(null);
const commentForm = useForm({
    comment: "",
});

const openPhotoModal = (photo) => {
    selectedPhoto.value = photo;
    document.body.style.overflow = "hidden"; // Prevent background scrolling
};

const closePhotoModal = () => {
    selectedPhoto.value = null;
    document.body.style.overflow = "";
    commentForm.reset();
};

const submitComment = () => {
    if (!selectedPhoto.value) return;

    commentForm.post(route("photo-comments.store", selectedPhoto.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset();
            // Update the selectedPhoto's comments locally to reflect changes immediately without full reload if possible,
            // but Inertia reload will handle it.
            // Since we are preserving state, we might need to update the local selectedPhoto reference from the fresh props.
            // However, Inertia's preserveState might keep the old props.
            // Actually, preserveScroll: true is fine.
            // We need to ensure selectedPhoto is updated with the new comment.
            // A simple way is to find the updated photo in the new props.trip.photos
            const updatedPhoto = props.trip.photos.find(
                (p) => p.id === selectedPhoto.value.id
            );
            if (updatedPhoto) {
                selectedPhoto.value = updatedPhoto;
            }
        },
    });
};

const deleteComment = (commentId) => {
    if (!confirm("コメントを削除しますか？")) return;

    router.delete(route("photo-comments.destroy", commentId), {
        preserveScroll: true,
        onSuccess: () => {
            const updatedPhoto = props.trip.photos.find(
                (p) => p.id === selectedPhoto.value.id
            );
            if (updatedPhoto) {
                selectedPhoto.value = updatedPhoto;
            }
        },
    });
};
</script>

<template>
    <Head :title="trip.title" />

    <AppLayout>
        <!-- Hero Section with Background Image (First Photo) -->
        <div class="relative h-[40vh] md:h-[50vh] bg-gray-900 overflow-hidden">
            <div v-if="trip.photos.length" class="absolute inset-0">
                <img
                    :src="trip.photos[0].path"
                    class="w-full h-full object-cover opacity-60 blur-sm scale-105"
                />
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"
                ></div>
            </div>
            <div
                v-else
                class="absolute inset-0 bg-gradient-to-br from-blue-900 to-gray-900"
            ></div>

            <!-- Back Button -->
            <div class="absolute top-6 left-6 md:top-12 md:left-12 z-20">
                <Link
                    :href="route('trips.index')"
                    class="inline-flex items-center gap-2 text-white/80 hover:text-white bg-black/20 hover:bg-black/40 backdrop-blur-md px-4 py-2 rounded-full transition duration-300 font-medium text-sm"
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
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        ></path>
                    </svg>
                    一覧に戻る
                </Link>
            </div>

            <div
                class="absolute bottom-0 left-0 right-0 p-6 md:p-12 max-w-7xl mx-auto w-full"
            >
                <div
                    class="flex flex-col md:flex-row justify-between items-end gap-6"
                >
                    <div>
                        <div
                            class="flex items-center gap-3 mb-3 text-white/80 text-sm font-medium"
                        >
                            <span
                                class="bg-white/20 backdrop-blur-md px-3 py-1 rounded-full"
                            >
                                {{ formatDate(trip.start_date) }} 〜
                                {{
                                    formatDate(trip.end_date || trip.start_date)
                                }}
                            </span>
                            <span
                                class="bg-white/20 backdrop-blur-md px-3 py-1 rounded-full"
                            >
                                {{ trip.nights }}泊
                            </span>
                            <span class="flex items-center gap-1">
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
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                                    ></path>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                    ></path>
                                </svg>
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="pref in Array.isArray(
                                            trip.prefecture
                                        )
                                            ? trip.prefecture
                                            : [trip.prefecture]"
                                        :key="pref"
                                        class="bg-white/20 backdrop-blur-md px-2 py-0.5 rounded text-xs"
                                    >
                                        {{ pref }}
                                    </span>
                                </div>
                            </span>
                        </div>
                        <h1
                            class="text-4xl md:text-5xl font-bold text-white mb-2 leading-tight"
                        >
                            {{ trip.title }}
                        </h1>
                        <div class="flex flex-wrap gap-2 mt-4">
                            <span
                                v-for="tag in trip.tags"
                                :key="tag.id"
                                class="text-xs font-medium text-white bg-blue-600/80 px-2 py-1 rounded-md backdrop-blur-sm"
                            >
                                #{{ tag.name }}
                            </span>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <Link
                            :href="route('trips.edit', trip.id)"
                            class="bg-white/10 hover:bg-white/20 text-white backdrop-blur-md px-4 py-2 rounded-lg font-medium transition flex items-center gap-2"
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
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                ></path>
                            </svg>
                            編集
                        </Link>
                        <button
                            @click="deleteTrip"
                            class="bg-red-500/80 hover:bg-red-600/80 text-white backdrop-blur-md px-4 py-2 rounded-lg font-medium transition flex items-center gap-2"
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
                            削除
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Left Column: Content -->
                <div class="lg:col-span-2 space-y-12">
                    <!-- AI Summary -->
                    <div
                        class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-3xl p-8 border border-indigo-100 relative overflow-hidden"
                    >
                        <div
                            class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-indigo-200 rounded-full opacity-20 blur-xl"
                        ></div>

                        <div
                            class="flex justify-between items-start mb-4 relative z-10"
                        >
                            <h3
                                class="text-lg font-bold text-indigo-900 flex items-center gap-2"
                            >
                                <span class="text-2xl">✨</span> AIハイライト
                            </h3>
                            <button
                                @click="summarizeTrip"
                                class="text-xs bg-white text-indigo-600 px-3 py-1.5 rounded-full font-medium shadow-sm hover:shadow transition border border-indigo-100"
                            >
                                {{
                                    trip.summary ? "再生成する" : "AIで要約する"
                                }}
                            </button>
                        </div>

                        <div
                            v-if="trip.summary"
                            class="prose prose-indigo text-indigo-800 leading-relaxed relative z-10"
                        >
                            {{ trip.summary }}
                        </div>
                        <div
                            v-else
                            class="text-indigo-400 text-sm relative z-10"
                        >
                            まだ要約がありません。思い出をメモに残して、AIにまとめてもらいましょう！
                        </div>
                    </div>

                    <!-- Description (Trix Content) -->
                    <div class="prose prose-lg max-w-none text-gray-600">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">
                            旅の記録
                        </h3>
                        <div
                            v-if="trip.description"
                            v-html="trip.description"
                            class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100"
                        ></div>
                        <div
                            v-else
                            class="text-gray-400 italic bg-gray-50 p-8 rounded-3xl border border-dashed border-gray-200 text-center"
                        >
                            まだメモがありません
                        </div>
                    </div>

                    <!-- Photo Gallery -->
                    <div>
                        <h3
                            class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2"
                        >
                            <span>📸</span> ギャラリー
                        </h3>

                        <div
                            v-if="trip.photos.length"
                            class="columns-1 sm:columns-2 gap-4 space-y-4"
                        >
                            <div
                                v-for="photo in trip.photos"
                                :key="photo.id"
                                @click="openPhotoModal(photo)"
                                class="break-inside-avoid relative group rounded-2xl overflow-hidden bg-gray-100 cursor-pointer"
                            >
                                <img
                                    :src="photo.path"
                                    class="w-full h-auto object-cover transition duration-500 group-hover:scale-105"
                                />
                                <div
                                    class="absolute bottom-0 inset-x-0 bg-black/60 backdrop-blur-sm p-3 text-white text-sm opacity-0 group-hover:opacity-100 transition duration-300"
                                >
                                    <div v-if="photo.caption" class="mb-1">
                                        {{ photo.caption }}
                                    </div>
                                    <div
                                        class="flex items-center gap-1 text-xs text-gray-300"
                                    >
                                        <svg
                                            class="w-3 h-3"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                            ></path>
                                        </svg>
                                        {{
                                            photo.comments
                                                ? photo.comments.length
                                                : 0
                                        }}
                                        コメント
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            class="text-center py-12 bg-gray-50 rounded-3xl border border-dashed border-gray-200"
                        >
                            <p class="text-gray-400">写真がまだありません</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sidebar -->
                <div class="space-y-8">
                    <!-- Upload Card -->
                    <div
                        class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sticky top-8"
                    >
                        <h3
                            class="font-bold text-gray-900 mb-4 flex items-center gap-2"
                        >
                            <svg
                                class="w-5 h-5 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                ></path>
                            </svg>
                            写真を追加
                        </h3>

                        <form @submit.prevent="submitPhoto" class="space-y-4">
                            <div>
                                <label
                                    class="block w-full cursor-pointer bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl p-8 text-center hover:bg-gray-100 transition group"
                                >
                                    <input
                                        type="file"
                                        @input="
                                            photoForm.photo =
                                                $event.target.files[0]
                                        "
                                        class="hidden"
                                        accept="image/*"
                                    />
                                    <div
                                        v-if="photoForm.photo"
                                        class="text-sm text-green-600 font-medium"
                                    >
                                        {{ photoForm.photo.name }}
                                    </div>
                                    <div
                                        v-else
                                        class="text-gray-400 group-hover:text-gray-600 transition"
                                    >
                                        <span class="text-2xl block mb-2"
                                            >📤</span
                                        >
                                        <span class="text-sm"
                                            >クリックして選択</span
                                        >
                                    </div>
                                </label>
                                <div
                                    v-if="photoForm.errors.photo"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ photoForm.errors.photo }}
                                </div>
                            </div>

                            <div>
                                <textarea
                                    v-model="photoForm.caption"
                                    placeholder="キャプション（任意）"
                                    class="w-full border-gray-200 rounded-xl text-sm focus:ring-black focus:border-black bg-gray-50"
                                ></textarea>
                            </div>

                            <button
                                type="submit"
                                :disabled="photoForm.processing"
                                class="w-full bg-black text-white py-3 rounded-xl font-medium hover:bg-gray-800 transition disabled:opacity-50"
                            >
                                アップロード
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo Modal -->
        <Teleport to="body">
            <div
                v-if="selectedPhoto"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/90 backdrop-blur-sm transition-opacity"
                    @click="closePhotoModal"
                ></div>

                <!-- Modal Content -->
                <div
                    class="relative bg-black rounded-2xl overflow-hidden shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col md:flex-row"
                    @click.stop
                >
                    <!-- Close Button -->
                    <button
                        @click="closePhotoModal"
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
                            :src="selectedPhoto.path"
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
                                    selectedPhoto.comments
                                        ? selectedPhoto.comments.length
                                        : 0
                                }}件</span
                            >
                        </div>

                        <!-- Comments List -->
                        <div
                            class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50"
                        >
                            <div
                                v-if="selectedPhoto.caption"
                                class="bg-white p-3 rounded-xl shadow-sm border border-gray-100"
                            >
                                <p class="text-sm text-gray-800">
                                    {{ selectedPhoto.caption }}
                                </p>
                            </div>

                            <div
                                v-if="
                                    selectedPhoto.comments &&
                                    selectedPhoto.comments.length > 0
                                "
                                class="space-y-4"
                            >
                                <div
                                    v-for="comment in selectedPhoto.comments"
                                    :key="comment.id"
                                    class="flex gap-3"
                                >
                                    <div
                                        class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-400 flex items-center justify-center text-white text-xs font-bold"
                                    >
                                        {{ comment.user_name.charAt(0) }}
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="bg-white p-3 rounded-r-xl rounded-bl-xl shadow-sm border border-gray-100"
                                        >
                                            <div
                                                class="flex justify-between items-start mb-1"
                                            >
                                                <span
                                                    class="text-xs font-bold text-gray-900"
                                                    >{{
                                                        comment.user_name
                                                    }}</span
                                                >
                                                <span
                                                    class="text-[10px] text-gray-400"
                                                    >{{
                                                        comment.created_at
                                                    }}</span
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
                                                currentUser.id ===
                                                    comment.user_id
                                            "
                                            class="flex justify-end mt-1"
                                        >
                                            <button
                                                @click="
                                                    deleteComment(comment.id)
                                                "
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
    </AppLayout>
</template>
