<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    trips: Array,
});

const attributes = computed(() => [
    ...props.trips.map((trip) => ({
        key: trip.id,
        highlight: {
            color: "blue",
            fillMode: "light",
        },
        dates: { start: new Date(trip.start), end: new Date(trip.end) },
        customData: trip,
        popover: {
            label: trip.title,
        },
    })),
]);

const showModal = ref(false);
const selectedDate = ref(null);

const onDayClick = (day) => {
    const dayStr = day.id; // 'YYYY-MM-DD'

    // Check if day has a trip
    const trip = props.trips.find((t) => {
        return dayStr >= t.start && dayStr <= t.end;
    });

    if (trip) {
        router.visit(route("trips.show", trip.id));
        return;
    }

    selectedDate.value = dayStr;

    // Check if it's a weekend (1 = Sunday, 7 = Saturday in v-calendar)
    if (day.weekday === 1 || day.weekday === 7) {
        showModal.value = true;
    } else {
        // Weekday: Go directly to create trip
        router.visit(route("trips.create", { date: dayStr }));
    }
};

const closeModal = () => {
    showModal.value = false;
    selectedDate.value = null;
};

const createTrip = () => {
    router.visit(route("trips.create", { date: selectedDate.value }));
    closeModal();
};

const planWithAi = () => {
    router.visit(route("suggestions.index"));
    closeModal();
};

const isStart = (day, trip) => day.id === trip.start;
const isEnd = (day, trip) => day.id === trip.end;
</script>

<template>
    <AppLayout title="思い出カレンダー">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                思い出カレンダー 🗓️
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 relative"
                >
                    <VCalendar
                        expanded
                        :attributes="attributes"
                        class="custom-calendar"
                    >
                        <template #day-content="{ day, attributes }">
                            <div
                                class="flex flex-col h-full w-full relative cursor-pointer hover:bg-gray-50 transition group"
                                @click="onDayClick(day)"
                            >
                                <span
                                    class="day-label text-sm font-medium text-gray-700 mt-1 ml-1"
                                    >{{ day.day }}</span
                                >

                                <div
                                    class="flex-grow flex flex-col gap-0.5 mt-1 relative"
                                >
                                    <div
                                        v-for="attr in attributes"
                                        :key="attr.key"
                                        class="text-[10px] h-5 flex items-center text-white bg-blue-500 relative shadow-sm"
                                        :class="{
                                            'rounded-l-md ml-1': isStart(
                                                day,
                                                attr.customData
                                            ),
                                            'rounded-r-md mr-1': isEnd(
                                                day,
                                                attr.customData
                                            ),
                                            '-ml-[1px]': !isStart(
                                                day,
                                                attr.customData
                                            ),
                                            '-mr-[1px]': !isEnd(
                                                day,
                                                attr.customData
                                            ),
                                            'z-20': true,
                                        }"
                                    >
                                        <!-- Thumbnail Background (only show on start or if single day) -->
                                        <div
                                            v-if="attr.customData.thumbnail_url"
                                            class="absolute inset-0 bg-cover bg-center opacity-40 z-0"
                                            :style="{
                                                backgroundImage: `url(${attr.customData.thumbnail_url})`,
                                            }"
                                        ></div>

                                        <!-- Title (only show on start or if space permits) -->
                                        <span
                                            v-if="
                                                isStart(day, attr.customData) ||
                                                day.weekday === 1
                                            "
                                            class="relative z-10 font-bold drop-shadow-md px-1 whitespace-nowrap"
                                        >
                                            {{ attr.customData.title }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </VCalendar>

                    <div class="mt-4 text-sm text-gray-500">
                        <p>
                            💡 ヒント:
                            旅行のない週末（土日）をタップすると、AIが旅行プランを提案します。
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom styles for calendar cells if needed */
:deep(.vc-day) {
    min-height: 100px;
    border: 1px solid #f3f4f6;
}
:deep(.vc-day-content) {
    /* Reset default centering */
    width: 100%;
    height: 100%;
    align-items: flex-start;
    justify-content: flex-start;
    border-radius: 0;
}
:deep(.vc-day-content:hover) {
    background-color: #f9fafb;
}
</style>
