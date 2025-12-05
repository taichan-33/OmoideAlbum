<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import debounce from "lodash/debounce";

const props = defineProps({
    trips: Object,
    filters: Object,
    tags: Array,
});

// 検索用のリアクティブデータ
const search = ref(props.filters.prefecture || "");
const selectedTag = ref(props.filters.tag_id || "");

// 入力に合わせて自動検索 (Debounce)
const handleSearch = debounce(() => {
    router.get(
        route("trips.index"),
        {
            prefecture: search.value,
            tag_id: selectedTag.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
}, 300);

watch([search, selectedTag], handleSearch);
</script>

<template>
    <Head title="思い出アルバム" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header Section -->
            <div
                class="flex flex-col md:flex-row justify-between items-center mb-12 gap-4"
            >
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                        Our Journeys
                    </h1>
                    <p class="text-gray-500 mt-1">二人の大切な思い出の記録</p>
                </div>
                <Link
                    :href="route('trips.create')"
                    class="bg-black text-white px-6 py-3 rounded-full font-medium hover:bg-gray-800 transition shadow-lg flex items-center gap-2"
                >
                    <span class="text-xl">+</span> 新しい旅を記録
                </Link>
            </div>

            <!-- Search & Filter -->
            <div class="mb-10 flex flex-col sm:flex-row gap-4">
                <div class="relative flex-grow max-w-md">
                    <span
                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            ></path>
                        </svg>
                    </span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="都道府県で探す..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-full focus:ring-2 focus:ring-black focus:border-transparent transition bg-white shadow-sm"
                    />
                </div>

                <select
                    v-model="selectedTag"
                    class="border border-gray-200 rounded-full px-4 py-2 focus:ring-2 focus:ring-black focus:border-transparent bg-white shadow-sm cursor-pointer"
                >
                    <option value="">全てのタグ</option>
                    <option v-for="tag in tags" :key="tag.id" :value="tag.id">
                        #{{ tag.name }}
                    </option>
                </select>
            </div>

            <!-- Trips Grid -->
            <div
                v-if="trips.data.length"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8"
            >
                <Link
                    v-for="trip in trips.data"
                    :key="trip.id"
                    :href="route('trips.show', trip.id)"
                    class="group cursor-pointer block h-full"
                >
                    <div
                        class="relative overflow-hidden rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 bg-white h-full border border-gray-100 flex flex-col"
                    >
                        <!-- Thumbnail -->
                        <div
                            class="aspect-[4/3] overflow-hidden bg-gray-100 relative"
                        >
                            <img
                                v-if="trip.thumbnail"
                                :src="trip.thumbnail"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50"
                            >
                                <span class="text-4xl">📷</span>
                            </div>

                            <div
                                class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-gray-800 shadow-sm"
                            >
                                {{
                                    new Date(
                                        trip.start_date
                                    ).toLocaleDateString()
                                }}
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-5 flex-grow flex flex-col">
                            <div class="flex justify-between items-start mb-2">
                                <h3
                                    class="text-xl font-bold text-gray-800 group-hover:text-blue-600 transition line-clamp-1"
                                >
                                    {{ trip.title }}
                                </h3>
                                <span
                                    class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-1 rounded ml-2 whitespace-nowrap"
                                >
                                    {{ trip.nights }}泊
                                </span>
                            </div>

                            <div
                                class="flex items-center text-gray-500 text-sm mb-4"
                            >
                                <svg
                                    class="w-4 h-4 mr-1 text-gray-400"
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
                                        class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs"
                                    >
                                        {{ pref }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-auto flex flex-wrap gap-2">
                                <span
                                    v-for="tag in trip.tags"
                                    :key="tag.id"
                                    class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-md"
                                >
                                    #{{ tag.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-200"
            >
                <div class="text-6xl mb-4">✈️</div>
                <h3 class="text-xl font-medium text-gray-900">
                    まだ思い出がありません
                </h3>
                <p class="text-gray-500 mt-2 mb-6">
                    最初の旅を記録して、アルバムを作りましょう！
                </p>
                <Link
                    :href="route('trips.create')"
                    class="inline-flex items-center px-4 py-2 bg-black text-white rounded-full hover:bg-gray-800 transition"
                >
                    思い出を記録する
                </Link>
            </div>

            <!-- Pagination -->
            <div
                v-if="trips.links.length > 3"
                class="mt-12 flex justify-center"
            >
                <div class="flex gap-1">
                    <Link
                        v-for="(link, key) in trips.links"
                        :key="key"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition"
                        :class="{
                            'bg-black text-white': link.active,
                            'bg-white text-gray-700 hover:bg-gray-100':
                                !link.active,
                            'opacity-50 cursor-not-allowed': !link.url,
                        }"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
