<script setup>
import { ref, onMounted } from "vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";

const form = useForm({
    content: "",
    attachment_type: null,
    attachment_id: null,
    attachment_preview: null, // UI表示用
    parent_post_id: null, // 返信用
    reply_to_user: null, // UI表示用
});

const isModalOpen = ref(false);
const activeTab = ref("trips");
const attachables = ref({
    trips: [],
    photos: [],
    suggestions: [],
});

const props = defineProps({
    placeholder: {
        type: String,
        default: "今何してる？あの旅行どうだった？",
    },
});

const selectedTripId = ref("");

const fetchAttachables = async (tripId = null) => {
    try {
        const params = tripId ? { trip_id: tripId } : {};
        const response = await axios.get(route("timeline.attachables"), {
            params,
        });
        // If filtering by trip, only update photos
        if (tripId) {
            attachables.value.photos = response.data.photos;
        } else {
            attachables.value = response.data;
        }
    } catch (error) {
        console.error("Failed to fetch attachables:", error);
    }
};

const handleTripFilterChange = () => {
    fetchAttachables(selectedTripId.value);
};

const openModal = (tab) => {
    activeTab.value = tab;
    isModalOpen.value = true;
    if (attachables.value.trips.length === 0) {
        fetchAttachables();
    }
};

const selectAttachment = (type, item) => {
    form.attachment_type = type;
    form.attachment_id = item.id;
    form.attachment_preview = item;
    isModalOpen.value = false;
};

const clearAttachment = () => {
    form.attachment_type = null;
    form.attachment_id = null;
    form.attachment_preview = null;
};

const clearReply = () => {
    form.parent_post_id = null;
    form.reply_to_user = null;
};

const submit = () => {
    form.post(route("timeline.store"), {
        onSuccess: () => {
            form.reset();
            clearAttachment();
            clearReply();
        },
    });
};

// URLパラメータから初期値をセット (他画面からのシェア用)
onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const shareType = params.get("share_type");
    const shareId = params.get("share_id");

    if (shareType && shareId) {
        form.attachment_type = shareType;
        form.attachment_id = parseInt(shareId);
    }
});
const setAttachment = (type, item) => {
    form.attachment_type = type;
    form.attachment_id = item.id;
    form.attachment_preview = item;
};

const setReplyTo = (post) => {
    form.parent_post_id = post.id;
    form.reply_to_user = post.user;
    // 返信時は添付をクリアする（仕様によるが、今回はシンプルに）
    clearAttachment();
};

defineExpose({ setAttachment, setReplyTo });
</script>

<template>
    <div class="bg-white p-4 rounded-lg shadow mb-6 border border-gray-200">
        <div class="flex gap-3">
            <div class="flex-shrink-0">
                <img
                    :src="$page.props.auth.user.profile_photo_url"
                    :alt="$page.props.auth.user.name"
                    class="w-10 h-10 rounded-full object-cover border border-gray-200"
                />
            </div>
            <div class="flex-grow">
                <textarea
                    v-model="form.content"
                    :placeholder="placeholder"
                    class="w-full border-gray-300 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 resize-none"
                    rows="3"
                ></textarea>

                <!-- Reply Preview -->
                <div
                    v-if="form.parent_post_id"
                    class="mt-2 relative border rounded-lg p-3 bg-blue-50 border-blue-200"
                >
                    <button
                        @click="clearReply"
                        class="absolute top-2 right-2 text-gray-400 hover:text-gray-600"
                    >
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                    <div class="text-sm text-blue-600 mb-1">
                        <i class="bi bi-reply-fill"></i> 返信先:
                    </div>
                    <div class="font-bold text-gray-800">
                        @{{ form.reply_to_user?.name }}
                    </div>
                </div>

                <!-- Attachment Preview -->
                <div
                    v-if="form.attachment_id"
                    class="mt-2 relative border rounded-lg p-3 bg-gray-50"
                >
                    <button
                        @click="clearAttachment"
                        class="absolute top-2 right-2 text-gray-400 hover:text-gray-600"
                    >
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                    <div class="text-sm text-gray-500 mb-1">引用中:</div>
                    <div
                        v-if="form.attachment_preview"
                        class="font-bold text-gray-800"
                    >
                        {{
                            form.attachment_preview.title ||
                            form.attachment_preview.caption ||
                            (form.attachment_type === "App\\Models\\Post"
                                ? form.attachment_preview.user?.name +
                                  "さんの投稿"
                                : "選択されたアイテム")
                        }}
                    </div>
                    <div v-else class="text-gray-400 text-sm">
                        アイテムID: {{ form.attachment_id }} (読み込み中...)
                    </div>
                </div>

                <div class="flex justify-between items-center mt-3">
                    <div class="flex gap-2">
                        <button
                            @click="openModal('trips')"
                            class="text-indigo-600 hover:bg-indigo-50 px-3 py-1 rounded-full text-sm font-medium transition flex items-center gap-1"
                        >
                            <span>✈️</span> 旅行
                        </button>
                        <button
                            @click="openModal('photos')"
                            class="text-green-600 hover:bg-green-50 px-3 py-1 rounded-full text-sm font-medium transition flex items-center gap-1"
                        >
                            <span>📷</span> 写真
                        </button>
                        <button
                            @click="openModal('suggestions')"
                            class="text-purple-600 hover:bg-purple-50 px-3 py-1 rounded-full text-sm font-medium transition flex items-center gap-1"
                        >
                            <span>🤖</span> AIプラン
                        </button>
                    </div>
                    <button
                        @click="submit"
                        :disabled="
                            form.processing ||
                            (!form.content && !form.attachment_id)
                        "
                        class="bg-indigo-600 text-white px-4 py-2 rounded-full font-bold hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        ツイート
                    </button>
                </div>
            </div>
        </div>

        <!-- Attachment Selection Modal -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="isModalOpen = false"
        >
            <div
                class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[80vh] flex flex-col relative"
            >
                <!-- Close Button (Explicit) -->
                <button
                    @click="isModalOpen = false"
                    class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 z-10 p-2"
                >
                    <i class="bi bi-x-lg text-xl"></i>
                </button>

                <div
                    class="p-4 border-b flex justify-between items-center pr-12"
                >
                    <h3 class="font-bold text-lg">思い出を引用</h3>
                </div>

                <div class="flex border-b">
                    <button
                        v-for="tab in ['trips', 'photos', 'suggestions']"
                        :key="tab"
                        @click="activeTab = tab"
                        class="flex-1 py-3 text-sm font-medium border-b-2 transition"
                        :class="
                            activeTab === tab
                                ? 'border-indigo-600 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        "
                    >
                        {{
                            tab === "trips"
                                ? "✈️ 旅行"
                                : tab === "photos"
                                ? "📷 写真"
                                : "🤖 AIプラン"
                        }}
                    </button>
                </div>

                <!-- Filter for Photos -->
                <div
                    v-if="activeTab === 'photos'"
                    class="p-4 border-b bg-gray-50"
                >
                    <select
                        v-model="selectedTripId"
                        @change="handleTripFilterChange"
                        class="w-full border-gray-300 rounded-lg text-sm"
                    >
                        <option value="">すべての写真</option>
                        <option
                            v-for="trip in attachables.trips"
                            :key="trip.id"
                            :value="trip.id"
                        >
                            {{ trip.title }} ({{
                                new Date(trip.start_date).getFullYear()
                            }})
                        </option>
                    </select>
                </div>

                <div class="overflow-y-auto p-4 flex-grow">
                    <div
                        v-if="attachables[activeTab].length === 0"
                        class="text-center text-gray-500 py-8"
                    >
                        データがありません
                    </div>
                    <ul v-else class="space-y-2">
                        <li
                            v-for="item in attachables[activeTab]"
                            :key="item.id"
                        >
                            <button
                                @click="
                                    selectAttachment(
                                        activeTab === 'trips'
                                            ? 'App\\Models\\Trip'
                                            : activeTab === 'photos'
                                            ? 'App\\Models\\Photo'
                                            : 'App\\Models\\Suggestion',
                                        item
                                    )
                                "
                                class="w-full text-left p-3 rounded-lg border hover:bg-indigo-50 hover:border-indigo-200 transition flex items-start gap-3"
                            >
                                <img
                                    v-if="activeTab === 'photos'"
                                    :src="item.url"
                                    class="w-12 h-12 object-cover rounded"
                                />
                                <div>
                                    <div
                                        class="font-bold text-gray-800 text-sm"
                                    >
                                        {{
                                            item.title || item.caption || "無題"
                                        }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{
                                            new Date(
                                                item.created_at
                                            ).toLocaleDateString()
                                        }}
                                    </div>
                                </div>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>
