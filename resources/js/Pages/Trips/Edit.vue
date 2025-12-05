<script setup>
import { useForm, Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import RichTextEditor from "@/Components/RichTextEditor.vue";
import PrefectureSelector from "@/Components/PrefectureSelector.vue";

const props = defineProps({
    trip: Object,
    tags: Array,
});

const form = useForm({
    _method: "PUT",
    title: props.trip.title,
    prefecture: props.trip.prefecture || [], // Ensure array
    start_date: props.trip.start_date
        ? props.trip.start_date.substring(0, 10)
        : "",
    end_date: props.trip.end_date ? props.trip.end_date.substring(0, 10) : "",
    nights: props.trip.nights,
    description: props.trip.description,
    tags: props.trip.tag_ids,
    photos: [],
    delete_photos: [],
});

const submit = () => {
    form.post(route("trips.update", props.trip.id));
};
</script>

<template>
    <Head :title="`編集: ${trip.title}`" />

    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header -->
            <div class="mb-8 text-center relative">
                <div
                    class="absolute left-0 top-1/2 -translate-y-1/2 hidden md:block"
                >
                    <Link
                        :href="route('trips.show', trip.id)"
                        class="inline-flex items-center gap-2 text-gray-500 hover:text-black transition font-medium text-sm"
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
                        戻る
                    </Link>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                    旅行の思い出を編集
                </h1>
                <p class="text-gray-500 mt-2">
                    {{ trip.title }} の内容を修正します。
                </p>
            </div>

            <div
                class="bg-white rounded-[2rem] shadow-xl border border-gray-100 overflow-hidden relative"
            >
                <!-- Decorative Top Bar -->
                <div
                    class="h-2 bg-gradient-to-r from-green-400 via-teal-500 to-blue-500"
                ></div>

                <div class="p-8 md:p-12">
                    <form @submit.prevent="submit" class="space-y-10">
                        <!-- Basic Info Section -->
                        <div class="space-y-6">
                            <h2
                                class="text-lg font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-2"
                            >
                                <span
                                    class="bg-black text-white w-6 h-6 rounded-full flex items-center justify-center text-xs"
                                    >1</span
                                >
                                基本情報
                            </h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-700 mb-2"
                                        >タイトル
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        class="w-full border-gray-200 rounded-xl focus:ring-2 focus:ring-black focus:border-transparent transition py-3 px-4 bg-gray-50 focus:bg-white"
                                        required
                                    />
                                    <div
                                        v-if="form.errors.title"
                                        class="text-red-500 text-xs mt-1 font-medium"
                                    >
                                        {{ form.errors.title }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-700 mb-2"
                                        >都道府県
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <PrefectureSelector
                                        v-model="form.prefecture"
                                    />
                                    <div
                                        v-if="form.errors.prefecture"
                                        class="text-red-500 text-xs mt-1 font-medium"
                                    >
                                        {{ form.errors.prefecture }}
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-700 mb-2"
                                        >開始日
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        v-model="form.start_date"
                                        type="date"
                                        class="w-full border-gray-200 rounded-xl focus:ring-2 focus:ring-black focus:border-transparent transition py-3 px-4 bg-gray-50 focus:bg-white"
                                        required
                                    />
                                    <div
                                        v-if="form.errors.start_date"
                                        class="text-red-500 text-xs mt-1 font-medium"
                                    >
                                        {{ form.errors.start_date }}
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-700 mb-2"
                                        >終了日</label
                                    >
                                    <input
                                        v-model="form.end_date"
                                        type="date"
                                        class="w-full border-gray-200 rounded-xl focus:ring-2 focus:ring-black focus:border-transparent transition py-3 px-4 bg-gray-50 focus:bg-white"
                                    />
                                    <div
                                        v-if="form.errors.end_date"
                                        class="text-red-500 text-xs mt-1 font-medium"
                                    >
                                        {{ form.errors.end_date }}
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-bold text-gray-700 mb-2"
                                        >泊数
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <div class="relative">
                                        <input
                                            v-model="form.nights"
                                            type="number"
                                            min="0"
                                            class="w-full border-gray-200 rounded-xl focus:ring-2 focus:ring-black focus:border-transparent transition py-3 px-4 bg-gray-50 focus:bg-white"
                                            required
                                        />
                                        <span
                                            class="absolute right-4 top-3.5 text-gray-400 text-sm font-medium"
                                            >泊</span
                                        >
                                    </div>
                                    <div
                                        v-if="form.errors.nights"
                                        class="text-red-500 text-xs mt-1 font-medium"
                                    >
                                        {{ form.errors.nights }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Details Section -->
                        <div class="space-y-6">
                            <h2
                                class="text-lg font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-2"
                            >
                                <span
                                    class="bg-black text-white w-6 h-6 rounded-full flex items-center justify-center text-xs"
                                    >2</span
                                >
                                旅の記録
                            </h2>

                            <div>
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-2"
                                    >メモ (Rich Text)</label
                                >
                                <RichTextEditor
                                    v-model="form.description"
                                    placeholder="旅の思い出を自由に書いてください。"
                                />
                                <div
                                    v-if="form.errors.description"
                                    class="text-red-500 text-xs mt-1 font-medium"
                                >
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-3"
                                    >タグ</label
                                >
                                <div class="flex flex-wrap gap-2">
                                    <label
                                        v-for="tag in tags"
                                        :key="tag.id"
                                        class="cursor-pointer group"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="tag.id"
                                            v-model="form.tags"
                                            class="hidden peer"
                                        />
                                        <span
                                            class="px-4 py-2 rounded-full text-sm font-medium border border-gray-200 text-gray-600 bg-white peer-checked:bg-black peer-checked:text-white peer-checked:border-black peer-checked:shadow-md transition-all select-none group-hover:border-gray-300"
                                        >
                                            #{{ tag.name }}
                                        </span>
                                    </label>
                                </div>
                                <div
                                    v-if="form.errors.tags"
                                    class="text-red-500 text-xs mt-1 font-medium"
                                >
                                    {{ form.errors.tags }}
                                </div>
                            </div>
                        </div>

                        <!-- Photos Section -->
                        <div class="space-y-6">
                            <h2
                                class="text-lg font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-2"
                            >
                                <span
                                    class="bg-black text-white w-6 h-6 rounded-full flex items-center justify-center text-xs"
                                    >3</span
                                >
                                写真
                            </h2>

                            <!-- Existing Photos (Delete) -->
                            <div
                                v-if="trip.photos.length"
                                class="bg-gray-50 rounded-2xl p-6"
                            >
                                <label
                                    class="block text-sm font-bold text-gray-700 mb-4"
                                    >現在の写真 (選択して削除)</label
                                >
                                <div
                                    class="grid grid-cols-2 md:grid-cols-4 gap-4"
                                >
                                    <label
                                        v-for="photo in trip.photos"
                                        :key="photo.id"
                                        class="relative group cursor-pointer block aspect-square"
                                    >
                                        <input
                                            type="checkbox"
                                            :value="photo.id"
                                            v-model="form.delete_photos"
                                            class="hidden peer"
                                        />
                                        <img
                                            :src="photo.path"
                                            class="w-full h-full object-cover rounded-xl border-2 border-transparent peer-checked:border-red-500 peer-checked:opacity-50 transition shadow-sm"
                                        />
                                        <div
                                            class="absolute inset-0 flex items-center justify-center opacity-0 peer-checked:opacity-100 text-red-600 font-bold bg-white/20 backdrop-blur-sm rounded-xl"
                                        >
                                            <span
                                                class="bg-white px-2 py-1 rounded-md shadow-sm text-xs"
                                                >削除する</span
                                            >
                                        </div>
                                        <div
                                            class="absolute top-2 right-2 bg-white rounded-full p-1.5 shadow-sm opacity-0 group-hover:opacity-100 peer-checked:opacity-100 transition"
                                        >
                                            <svg
                                                class="w-4 h-4 text-red-500"
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
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block w-full cursor-pointer bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-10 text-center hover:bg-gray-100 hover:border-gray-300 transition group"
                                >
                                    <input
                                        type="file"
                                        @input="
                                            form.photos = $event.target.files
                                        "
                                        multiple
                                        accept="image/*"
                                        class="hidden"
                                    />
                                    <div class="space-y-2">
                                        <div
                                            class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mx-auto text-2xl group-hover:scale-110 transition"
                                        >
                                            📸
                                        </div>
                                        <div
                                            class="text-gray-600 font-medium group-hover:text-black transition"
                                        >
                                            新しい写真を追加
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            クリックまたはドラッグ＆ドロップ
                                        </div>
                                    </div>
                                    <div
                                        v-if="form.photos.length"
                                        class="mt-4 text-sm text-green-600 font-bold bg-green-50 inline-block px-3 py-1 rounded-full"
                                    >
                                        {{ form.photos.length }} 枚選択済み
                                    </div>
                                </label>
                                <div
                                    v-if="form.errors.photos"
                                    class="text-red-500 text-xs mt-1 font-medium"
                                >
                                    {{ form.errors.photos }}
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div
                            class="pt-6 flex items-center gap-4 border-t border-gray-100"
                        >
                            <Link
                                :href="route('trips.show', trip.id)"
                                class="w-1/3 py-4 text-center text-gray-500 font-bold hover:text-gray-800 transition rounded-xl hover:bg-gray-50"
                            >
                                キャンセル
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-2/3 bg-black text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl hover:bg-gray-800 transition transform hover:-translate-y-0.5 disabled:opacity-50 disabled:transform-none"
                            >
                                更新する
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
