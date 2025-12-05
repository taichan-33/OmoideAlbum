<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import axios from "axios";

const page = usePage();
const notifications = ref([]);
const unreadCount = ref(page.props.auth.unreadNotificationsCount || 0);
const isOpen = ref(false);
let pollingInterval = null;

const fetchNotifications = async () => {
    try {
        const response = await axios.get(route("notifications.index"));
        notifications.value = response.data.notifications;
        unreadCount.value = response.data.unreadCount;
    } catch (error) {
        console.error("Failed to fetch notifications", error);
    }
};

const markAsRead = async (id) => {
    try {
        await axios.post(route("notifications.read", id));
        fetchNotifications(); // Refresh list
    } catch (error) {
        console.error("Failed to mark as read", error);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post(route("notifications.readAll"));
        fetchNotifications();
    } catch (error) {
        console.error("Failed to mark all as read", error);
    }
};

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        fetchNotifications();
    }
};

onMounted(() => {
    // 初回ロード
    fetchNotifications();

    // ポーリング (30秒ごと)
    pollingInterval = setInterval(fetchNotifications, 30000);
});

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
</script>

<template>
    <div class="relative">
        <!-- Bell Icon -->
        <button
            @click="toggleDropdown"
            class="relative p-2 text-gray-400 hover:text-gray-600 focus:outline-none"
        >
            <span class="text-2xl">🔔</span>
            <span
                v-if="unreadCount > 0"
                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full"
            >
                {{ unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl overflow-hidden z-50 border border-gray-100"
        >
            <div class="fixed inset-0 z-0" @click="isOpen = false"></div>
            <div class="relative z-10">
                <div
                    class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50"
                >
                    <h3 class="text-sm font-semibold text-gray-700">
                        お知らせ
                    </h3>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="text-xs text-blue-600 hover:text-blue-800"
                    >
                        すべて既読にする
                    </button>
                </div>
                <div class="max-h-96 overflow-y-auto">
                    <div
                        v-if="notifications.length === 0"
                        class="px-4 py-6 text-center text-gray-500 text-sm"
                    >
                        お知らせはありません
                    </div>
                    <div v-else>
                        <div
                            v-for="notification in notifications"
                            :key="notification.id"
                            class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition duration-150 ease-in-out"
                            :class="{ 'bg-blue-50': !notification.read_at }"
                        >
                            <a
                                :href="notification.data.url"
                                @click.prevent="
                                    markAsRead(notification.id);
                                    $inertia.visit(notification.data.url);
                                "
                                class="flex items-start gap-3"
                            >
                                <div class="flex-shrink-0 text-xl mt-1">
                                    {{ notification.data.icon || "🔔" }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ notification.data.message }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ notification.created_at }}
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
