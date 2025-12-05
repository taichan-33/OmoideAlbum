<script setup>
import { ref, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import debounce from "lodash/debounce";

const props = defineProps({
    trips: Object,
    filters: Object,
    tags: Array,
    onThisDayTrips: Array, // 追加
});

const viewMode = ref("grid"); // 'grid' or 'timeline'

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

            <!-- On This Day Section -->
            <div
                v-if="onThisDayTrips && onThisDayTrips.length > 0"
                class="mb-12"
            >
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-2xl">📅</span>
                    <h2 class="text-xl font-bold text-gray-800">
                        あの日の思い出
                    </h2>
                </div>
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
                >
                    <Link
                        v-for="trip in onThisDayTrips"
                        :key="trip.id"
                        :href="route('trips.show', trip.id)"
                        class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 bg-white border border-amber-100"
                    >
                        <div
                            class="absolute top-0 left-0 w-1 h-full bg-amber-400"
                        ></div>
                        <div class="p-5 pl-7 flex gap-4">
                            <div
                                class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100"
                            >
                                <img
                                    v-if="trip.thumbnail"
                                    :src="trip.thumbnail"
                                    class="w-full h-full object-cover"
                                />
                                <div
                                    v-else
                                    class="w-full h-full flex items-center justify-center text-2xl"
                                >
                                    📷
                                </div>
                            </div>
                            <div>
                                <div
                                    class="text-xs font-bold text-amber-600 mb-1"
                                >
                                    {{ trip.years_ago }}年前の今日
                                </div>
                                <h3
                                    class="font-bold text-gray-800 group-hover:text-amber-600 transition line-clamp-1"
                                >
                                    {{ trip.title }}
                                </h3>
                                <div class="text-sm text-gray-500 mt-1">
                                    {{
                                        new Date(
                                            trip.start_date
                                        ).toLocaleDateString()
                                    }}
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Search & Filter & View Toggle -->
            <div
                class="mb-10 flex flex-col sm:flex-row gap-4 justify-between items-center"
            >
                <div
                    class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto flex-grow"
                >
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
                        <option
                            v-for="tag in tags"
                            :key="tag.id"
                            :value="tag.id"
                        >
                            #{{ tag.name }}
                        </option>
                    </select>
                </div>

                <!-- View Toggle -->
                <div
                    class="bg-gray-100 p-1 rounded-lg flex items-center flex-shrink-0"
                >
                    <button
                        @click="viewMode = 'grid'"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-all flex items-center gap-1"
                        :class="
                            viewMode === 'grid'
                                ? 'bg-white text-black shadow-sm'
                                : 'text-gray-500 hover:text-gray-700'
                        "
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
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                            ></path>
                        </svg>
                        グリッド
                    </button>
                    <button
                        @click="viewMode = 'timeline'"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-all flex items-center gap-1"
                        :class="
                            viewMode === 'timeline'
                                ? 'bg-white text-black shadow-sm'
                                : 'text-gray-500 hover:text-gray-700'
                        "
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
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            ></path>
                        </svg>
                        タイムライン
                    </button>
                </div>
            </div>

            <!-- Trips Grid -->
            <div
                v-if="trips.data.length && viewMode === 'grid'"
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

            <!-- Timeline View -->
            <div
                v-else-if="trips.data.length && viewMode === 'timeline'"
                class="relative py-8"
            >
                <!-- Center Line -->
                <div
                    class="absolute left-4 md:left-1/2 top-0 bottom-0 w-0.5 bg-gray-200 transform md:-translate-x-1/2"
                ></div>

                <div class="space-y-12">
                    <div
                        v-for="(trip, index) in trips.data"
                        :key="trip.id"
                        class="relative flex flex-col md:flex-row items-center"
                        :class="index % 2 === 0 ? 'md:flex-row-reverse' : ''"
                    >
                        <!-- Date Marker -->
                        <div
                            class="absolute left-4 md:left-1/2 w-4 h-4 bg-black rounded-full border-4 border-white shadow-sm transform -translate-x-1/2 z-10"
                        ></div>

                        <!-- Content Card -->
                        <div
                            class="w-full md:w-1/2 pl-12 md:pl-0"
                            :class="index % 2 === 0 ? 'md:pl-12' : 'md:pr-12'"
                        >
                            <Link
                                :href="route('trips.show', trip.id)"
                                class="block bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 overflow-hidden group"
                            >
                                <div class="flex flex-col sm:flex-row">
                                    <div
                                        class="sm:w-1/3 h-48 sm:h-auto relative overflow-hidden"
                                    >
                                        <img
                                            v-if="trip.thumbnail"
                                            :src="trip.thumbnail"
                                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                        />
                                        <div
                                            v-else
                                            class="w-full h-full bg-gray-100 flex items-center justify-center text-2xl"
                                        >
                                            📷
                                        </div>
                                    </div>
                                    <div
                                        class="p-5 sm:w-2/3 flex flex-col justify-center"
                                    >
                                        <div
                                            class="text-sm text-gray-500 font-mono mb-1"
                                        >
                                            {{
                                                new Date(
                                                    trip.start_date
                                                ).toLocaleDateString()
                                            }}
                                        </div>
                                        <h3
                                            class="text-lg font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition"
                                        >
                                            {{ trip.title }}
                                        </h3>
                                        <div class="flex flex-wrap gap-1 mb-3">
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
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="tag in trip.tags"
                                                :key="tag.id"
                                                class="text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded"
                                            >
                                                #{{ tag.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
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
