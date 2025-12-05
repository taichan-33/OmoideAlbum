<script setup>
import { Head } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from "chart.js";
import { Bar } from "vue-chartjs";

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend
);

const props = defineProps({
    stats: Object,
});

// Chart Data for Monthly Trips
const monthlyChartData = {
    labels: [
        "1月",
        "2月",
        "3月",
        "4月",
        "5月",
        "6月",
        "7月",
        "8月",
        "9月",
        "10月",
        "11月",
        "12月",
    ],
    datasets: [
        {
            label: "旅行回数",
            backgroundColor: "#f59e0b",
            data: props.stats.monthlyData,
            borderRadius: 6,
        },
    ],
};

// Chart Data for Yearly Trips
const yearlyChartData = {
    labels: props.stats.yearlyCounts.map((item) => item.year + "年"),
    datasets: [
        {
            label: "旅行回数",
            backgroundColor: "#3b82f6",
            data: props.stats.yearlyCounts.map((item) => item.count),
            borderRadius: 6,
        },
    ],
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
            },
        },
        x: {
            grid: {
                display: false,
            },
        },
    },
};
</script>

<template>
    <Head title="旅行統計" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header -->
            <div class="mb-12 text-center">
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                    Travel Statistics
                </h1>
                <p class="text-gray-500 mt-2">二人の旅の軌跡を数字で振り返る</p>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                <div
                    class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden"
                >
                    <div
                        class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"
                    ></div>
                    <div class="relative z-10">
                        <div class="text-blue-100 font-medium mb-1">
                            総旅行回数
                        </div>
                        <div class="text-5xl font-bold tracking-tight">
                            {{ stats.totalTrips
                            }}<span class="text-2xl ml-1">回</span>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden"
                >
                    <div
                        class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"
                    ></div>
                    <div class="relative z-10">
                        <div class="text-amber-100 font-medium mb-1">
                            総宿泊数
                        </div>
                        <div class="text-5xl font-bold tracking-tight">
                            {{ stats.totalNights
                            }}<span class="text-2xl ml-1">泊</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                <!-- Top Prefectures -->
                <div
                    class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8"
                >
                    <h3
                        class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2"
                    >
                        <span class="text-xl">🏆</span> よく行く都道府県 Top 5
                    </h3>
                    <div class="space-y-4">
                        <div
                            v-for="(count, pref, index) in stats.topPrefectures"
                            :key="pref"
                            class="flex items-center gap-4"
                        >
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm"
                                :class="
                                    index === 0
                                        ? 'bg-yellow-100 text-yellow-700'
                                        : index === 1
                                        ? 'bg-gray-100 text-gray-700'
                                        : index === 2
                                        ? 'bg-orange-100 text-orange-700'
                                        : 'bg-white text-gray-500'
                                "
                            >
                                {{
                                    Object.keys(stats.topPrefectures).indexOf(
                                        pref
                                    ) + 1
                                }}
                            </div>
                            <div class="flex-grow">
                                <div
                                    class="flex justify-between items-center mb-1"
                                >
                                    <span class="font-medium text-gray-800">{{
                                        pref
                                    }}</span>
                                    <span class="text-sm text-gray-500"
                                        >{{ count }}回</span
                                    >
                                </div>
                                <div
                                    class="w-full bg-gray-100 rounded-full h-2"
                                >
                                    <div
                                        class="bg-blue-500 h-2 rounded-full"
                                        :style="{
                                            width:
                                                (count /
                                                    Math.max(
                                                        ...Object.values(
                                                            stats.topPrefectures
                                                        )
                                                    )) *
                                                    100 +
                                                '%',
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="
                                Object.keys(stats.topPrefectures).length === 0
                            "
                            class="text-center text-gray-400 py-4"
                        >
                            まだデータがありません
                        </div>
                    </div>
                </div>

                <!-- Top Photo Trips -->
                <div
                    class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8"
                >
                    <h3
                        class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2"
                    >
                        <span class="text-xl">📸</span> 写真が多い旅行 Top 5
                    </h3>
                    <div class="space-y-4">
                        <div
                            v-for="(trip, index) in stats.topPhotoTrips"
                            :key="trip.id"
                            class="flex items-center gap-4"
                        >
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm"
                                :class="
                                    index === 0
                                        ? 'bg-yellow-100 text-yellow-700'
                                        : index === 1
                                        ? 'bg-gray-100 text-gray-700'
                                        : index === 2
                                        ? 'bg-orange-100 text-orange-700'
                                        : 'bg-white text-gray-500'
                                "
                            >
                                {{ index + 1 }}
                            </div>
                            <div class="flex-grow">
                                <div
                                    class="flex justify-between items-center mb-1"
                                >
                                    <span
                                        class="font-medium text-gray-800 line-clamp-1"
                                        >{{ trip.title }}</span
                                    >
                                    <span
                                        class="text-sm text-gray-500 flex-shrink-0"
                                        >{{ trip.photos_count }}枚</span
                                    >
                                </div>
                                <div
                                    class="w-full bg-gray-100 rounded-full h-2"
                                >
                                    <div
                                        class="bg-green-500 h-2 rounded-full"
                                        :style="{
                                            width:
                                                (trip.photos_count /
                                                    Math.max(
                                                        ...stats.topPhotoTrips.map(
                                                            (t) =>
                                                                t.photos_count
                                                        )
                                                    )) *
                                                    100 +
                                                '%',
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <div
                            v-if="stats.topPhotoTrips.length === 0"
                            class="text-center text-gray-400 py-4"
                        >
                            まだデータがありません
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
                <!-- Monthly Chart -->
                <div
                    class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8"
                >
                    <h3
                        class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2"
                    >
                        <span class="text-xl">📊</span> 月別旅行回数
                    </h3>
                    <div class="h-64">
                        <Bar :data="monthlyChartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Yearly Chart -->
                <div
                    class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8"
                >
                    <h3
                        class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2"
                    >
                        <span class="text-xl">📈</span> 年別推移
                    </h3>
                    <div class="h-64">
                        <Bar :data="yearlyChartData" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <!-- Top Tags -->
            <div
                class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8"
            >
                <h3
                    class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2"
                >
                    <span class="text-xl">🏷️</span> よく使うタグ
                </h3>
                <div class="flex flex-wrap gap-3">
                    <span
                        v-for="tag in stats.topTags"
                        :key="tag.name"
                        class="px-4 py-2 rounded-full text-sm font-medium bg-gray-50 text-gray-700 border border-gray-200 flex items-center gap-2"
                    >
                        #{{ tag.name }}
                        <span
                            class="bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded-full text-xs"
                        >
                            {{ tag.count }}
                        </span>
                    </span>
                    <div
                        v-if="stats.topTags.length === 0"
                        class="text-gray-400"
                    >
                        まだタグが使用されていません
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
