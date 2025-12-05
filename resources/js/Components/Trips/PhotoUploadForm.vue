<script setup>
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    tripId: {
        type: Number,
        required: true,
    },
});

const photoForm = useForm({
    photo: null,
    caption: "",
});

const submitPhoto = () => {
    photoForm.post(route("photos.store", props.tripId), {
        onSuccess: () => {
            photoForm.reset();
        },
    });
};
</script>

<template>
    <div
        class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 sticky top-8"
    >
        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
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
                        @input="photoForm.photo = $event.target.files[0]"
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
                        <span class="text-2xl block mb-2">📤</span>
                        <span class="text-sm">クリックして選択</span>
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
</template>
