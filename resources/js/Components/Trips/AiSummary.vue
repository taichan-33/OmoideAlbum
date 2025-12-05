<script setup>
import { router } from "@inertiajs/vue3";

const props = defineProps({
    trip: Object,
});

const summarizeTrip = () => {
    if (
        confirm(
            "AIに要約を依頼します。メモの内容がAIに送信されますが、よろしいですか？"
        )
    ) {
        router.post(route("trips.summarize", props.trip.id));
    }
};
</script>

<template>
    <div
        class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-3xl p-8 border border-indigo-100 relative overflow-hidden"
    >
        <div
            class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-indigo-200 rounded-full opacity-20 blur-xl"
        ></div>

        <div class="flex justify-between items-start mb-4 relative z-10">
            <h3
                class="text-lg font-bold text-indigo-900 flex items-center gap-2"
            >
                <span class="text-2xl">✨</span> AIハイライト
            </h3>
            <button
                @click="summarizeTrip"
                class="text-xs bg-white text-indigo-600 px-3 py-1.5 rounded-full font-medium shadow-sm hover:shadow transition border border-indigo-100"
            >
                {{ trip.summary ? "再生成する" : "AIで要約する" }}
            </button>
        </div>

        <div
            v-if="trip.summary"
            class="prose prose-indigo text-indigo-800 leading-relaxed relative z-10"
        >
            {{ trip.summary }}
        </div>
        <div v-else class="text-indigo-400 text-sm relative z-10">
            まだ要約がありません。思い出をメモに残して、AIにまとめてもらいましょう！
        </div>
    </div>
</template>
