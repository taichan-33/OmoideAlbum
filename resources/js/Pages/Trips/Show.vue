<script setup>
import { ref, computed } from "vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import AiSummary from "@/Components/Trips/AiSummary.vue";
import PhotoGallery from "@/Components/Trips/PhotoGallery.vue";
import PhotoModal from "@/Components/Trips/PhotoModal.vue";
import TripHero from "@/Components/Trips/TripHero.vue";
import PhotoUploadForm from "@/Components/Trips/PhotoUploadForm.vue";

const props = defineProps({
    trip: Object,
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const deleteTrip = () => {
    if (
        confirm(
            "本当にこの旅行の思い出を削除しますか？\n関連する写真もすべて削除されます。"
        )
    ) {
        router.delete(route("trips.destroy", props.trip.id));
    }
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
        <TripHero :trip="trip" @delete="deleteTrip" />

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
                    <PhotoUploadForm :trip-id="trip.id" />
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
