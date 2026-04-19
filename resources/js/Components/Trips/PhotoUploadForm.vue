<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    tripId: {
        type: Number,
        required: true,
    },
});

const fileInput = ref(null);
const isDragging = ref(false);

const photoForm = useForm({
    photos: [], // 配列 (Fileオブジェクト)
    caption: "",
});

const submitPhoto = () => {
    if (photoForm.photos.length === 0) return;
    
    photoForm.post(route("photos.store", props.tripId), {
        onSuccess: () => {
            photoForm.reset();
            // inputの値をリセットしないと、同じファイルを再度選択した時にchangeイベントが発火しない
            if (fileInput.value) fileInput.value.value = "";
        },
    });
};

const addFiles = (files) => {
    const newFiles = Array.from(files);
    const validFiles = newFiles.filter(file => file.type.startsWith('image/'));
    
    // 既存のファイルと合わせて50枚制限チェック
    if (photoForm.photos.length + validFiles.length > 50) {
        alert('一度にアップロードできるのは50枚までです。');
        return;
    }

    photoForm.photos = [...photoForm.photos, ...validFiles];
};

const handleFileChange = (e) => {
    addFiles(e.target.files);
    // inputをリセットして、連続で同じファイルを追加できるようにする（または追加操作であることを明確にする）
    e.target.value = ""; 
};

const handleDrop = (e) => {
    isDragging.value = false;
    addFiles(e.dataTransfer.files);
};

const clearPhotos = () => {
    photoForm.photos = [];
    if (fileInput.value) fileInput.value.value = "";
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
            写真を追加 (複数可)
        </h3>

        <form @submit.prevent="submitPhoto" class="space-y-4">
            <div>
                <div
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                    class="relative block w-full cursor-pointer border-2 border-dashed rounded-xl p-8 text-center transition group overflow-hidden"
                    :class="[
                        isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-gray-50 hover:bg-gray-100'
                    ]"
                >
                    <input
                        ref="fileInput"
                        type="file"
                        @change="handleFileChange"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        accept="image/*"
                        multiple
                    />
                    
                    <div v-if="photoForm.photos.length > 0" class="relative z-10">
                        <div class="text-3xl font-bold text-green-600 mb-2">
                            {{ photoForm.photos.length }}
                        </div>
                        <div class="text-sm text-gray-600 font-medium">枚選択中</div>
                        <button 
                            type="button" 
                            @click.stop.prevent="clearPhotos"
                            class="mt-3 text-xs text-red-500 hover:text-red-700 underline z-20 relative pointer-events-auto"
                        >
                            選択をクリア
                        </button>
                    </div>
                    
                    <div v-else class="text-gray-400 group-hover:text-gray-600 transition">
                        <span class="text-2xl block mb-2">📤</span>
                        <span class="text-sm">クリック または ドラッグ＆ドロップ</span>
                        <span class="text-xs block mt-1">(50枚まで追加可能)</span>
                    </div>
                </div>

                <!-- エラー表示: photos全体 -->
                <div
                    v-if="photoForm.errors.photos"
                    class="text-red-500 text-xs mt-1"
                >
                    {{ photoForm.errors.photos }}
                </div>
                 <!-- エラー表示: 個別のファイル (例: photos.0) -->
                <div v-for="(error, key) in photoForm.errors" :key="key">
                    <div v-if="key.startsWith('photos.')" class="text-red-500 text-xs mt-1">
                         {{ error }}
                    </div>
                </div>
            </div>

            <div>
                <textarea
                    v-model="photoForm.caption"
                    placeholder="キャプション（全写真共通・任意）"
                    class="w-full border-gray-200 rounded-xl text-sm focus:ring-black focus:border-black bg-gray-50"
                ></textarea>
            </div>

            <button
                type="submit"
                :disabled="photoForm.processing || photoForm.photos.length === 0"
                class="w-full bg-black text-white py-3 rounded-xl font-medium hover:bg-gray-800 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span v-if="photoForm.processing">送信中...</span>
                <span v-else>アップロード ({{ photoForm.photos.length }}枚)</span>
            </button>
        </form>
    </div>
</template>
