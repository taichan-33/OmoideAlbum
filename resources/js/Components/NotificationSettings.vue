<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps(['className']);
const page = usePage();
const user = computed(() => page.props.auth.user);
const vapidPublicKey = page.props.vapidPublicKey;

const isPushEnabled = ref(false);
const isLoading = ref(false);
const notificationPermission = ref('default');

// Preferences
const preferences = ref({
    post_interacted: true, // Reply, Like, Quote, etc.
    trip_updated: true,    // Trip updates
    badge_earned: true,    // Badge earned
    ...user.value.notification_preferences
});

const urlBase64ToUint8Array = (base64String) => {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

const checkSubscription = async () => {
    console.log('Checking subscription...');
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        console.error('Service Worker or Push Manager not supported');
        return;
    }

    try {
        const reg = await navigator.serviceWorker.ready;
        console.log('Service Worker ready:', reg);
        const sub = await reg.pushManager.getSubscription();
        console.log('Current Subscription:', sub);

        if (sub) {
            isPushEnabled.value = true;
        }
        notificationPermission.value = Notification.permission;
        console.log('Notification Permission:', notificationPermission.value);
    } catch (e) {
        console.error('Error checking subscription:', e);
    }
};

const togglePush = async () => {
    console.log('Toggle push clicked. Current state:', isPushEnabled.value);
    if (isLoading.value) return;
    isLoading.value = true;

    try {
        if (isPushEnabled.value) {
            // Unsubscribe
            console.log('Unsubscribing...');
            const reg = await navigator.serviceWorker.ready;
            const sub = await reg.pushManager.getSubscription();
            if (sub) {
                await sub.unsubscribe();
                await axios.post(route('push.unsubscribe'), { endpoint: sub.endpoint });
                isPushEnabled.value = false;
                console.log('Unsubscribed successfully');
            }
        } else {
            // Subscribe
            console.log('Subscribing...');
            if (Notification.permission === 'denied') {
                alert('通知がブロックされています。ブラウザの設定から許可してください。');
                isLoading.value = false;
                return;
            }

            const permission = await Notification.requestPermission();
            console.log('Permission result:', permission);
            notificationPermission.value = permission;

            if (permission === 'granted') {
                const reg = await navigator.serviceWorker.ready;
                console.log('SW Ready for subscribe', reg);
                
                console.log('VAPID Key:', vapidPublicKey);
                const sub = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
                });
                console.log('Subscription object:', sub);

                await axios.post(route('push.subscribe'), sub);
                isPushEnabled.value = true;
                console.log('Subscribed successfully');
            } else {
                console.log('Permission not granted');
            }
        }
    } catch (e) {
        console.error('Push toggle failed:', e);
        alert('設定の変更に失敗しました: ' + e.message);
    } finally {
        isLoading.value = false;
    }
};

// Update preferences when changed
watch(preferences, async (newVal) => {
    if (!isPushEnabled.value) return; // Don't sync if not subscribed? Actually sync anyway.
    try {
        await axios.post(route('push.preferences'), { preferences: newVal });
    } catch (e) {
        console.error('Failed to update preferences:', e);
    }
}, { deep: true });

onMounted(() => {
    checkSubscription();
});
</script>

<template>
    <div :class="['bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6', className]">
        <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                プッシュ通知設定
            </h3>

            <div v-if="!vapidPublicKey" class="text-red-500 text-sm">
                VAPIDキーが設定されていません。管理者に連絡してください。
            </div>

            <div v-else>
                <!-- Master Toggle -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <div class="font-bold text-gray-700">通知を受け取る</div>
                        <div class="text-sm text-gray-500">
                            この端末でプッシュ通知を有効にします
                        </div>
                    </div>
                    <button 
                        @click="togglePush" 
                        :disabled="isLoading"
                        :class="[
                            'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500',
                            isPushEnabled ? 'bg-indigo-600' : 'bg-gray-200'
                        ]"
                    >
                        <span 
                            aria-hidden="true" 
                            :class="[
                                'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200',
                                isPushEnabled ? 'translate-x-5' : 'translate-x-0'
                            ]"
                        ></span>
                    </button>
                </div>

                <!-- Detailed Preferences (Only visible if permission granted roughly, but always editable is fine) -->
                <div v-if="isPushEnabled" class="border-t border-gray-100 pt-4 mt-4">
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" v-model="preferences.trip_updated" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">旅行の更新（写真追加など）</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="preferences.post_interacted" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">タイムラインへの反応（コメント、いいね、引用）</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="preferences.badge_earned" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">バッジ獲得のお知らせ</span>
                        </label>
                    </div>
                </div>

                <div v-if="notificationPermission === 'denied'" class="mt-4 p-4 bg-red-50 text-red-700 text-sm rounded">
                    ⚠️ 通知の権限がブロックされています。ブラウザのアドレスバーから許可に変更してください。
                </div>
            </div>
        </div>
    </div>
</template>
