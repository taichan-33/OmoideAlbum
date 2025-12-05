<script setup>
import { ref, computed } from "vue";
import { Head, Link, useForm, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import AiSummary from "@/Components/Trips/AiSummary.vue";
import PhotoGallery from "@/Components/Trips/PhotoGallery.vue";
import PhotoModal from "@/Components/Trips/PhotoModal.vue";

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

const formatDate = (dateString) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return date.toLocaleDateString("ja-JP", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    });
};

// --- Photo Modal ---
const selectedPhoto = ref(null);

const openPhotoModal = (photo) => {
    selectedPhoto.value = photo;
    document.body.style.overflow = "hidden";
};

const closePhotoModal = () => {
    selectedPhoto.value = null;
    document.body.style.overflow = "";
};

const refreshSelectedPhoto = () => {
    if (selectedPhoto.value) {
        const updated = props.trip.photos.find(
            (p) => p.id === selectedPhoto.value.id
        );
        if (updated) {
            selectedPhoto.value = updated;
        }
    }
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
                    <AiSummary :trip="trip" />

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
                    <PhotoGallery
                        :photos="trip.photos"
                        @open-modal="openPhotoModal"
                    />
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
        <PhotoModal
            :photo="selectedPhoto"
            :currentUser="currentUser"
            @close="closePhotoModal"
            @photo-updated="refreshSelectedPhoto"
        />
    </AppLayout>
</template>
