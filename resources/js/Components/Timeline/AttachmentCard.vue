<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import MiniMap from "@/Components/MiniMap.vue";

const props = defineProps({
    type: String,
    attachment: Object,
    post: Object, // Optional, for reaction state
});

const emit = defineEmits(["vote"]);

const isTrip = computed(() => props.type === "App\\Models\\Trip");
const isPhoto = computed(() => props.type === "App\\Models\\Photo");
const isSuggestion = computed(() => props.type === "App\\Models\\Suggestion");
const isPost = computed(() => props.type === "App\\Models\\Post");

const formatDate = (dateString) => {
    if (!dateString) return "";
    return new Date(dateString).toLocaleDateString("ja-JP");
};
</script>

<template>
    <div
        v-if="attachment"
        class="mt-2 border rounded-lg overflow-hidden bg-gray-50 border-gray-200"
    >
        <!-- Trip -->
        <Link
            v-if="isTrip"
            :href="route('trips.show', attachment.id)"
            class="block hover:bg-gray-100 transition"
        >
            <div class="p-3">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xl">✈️</span>
                    <h3 class="font-bold text-gray-800">
                        {{ attachment.title }}
                    </h3>
                </div>
                <div class="text-sm text-gray-500">
                    {{ formatDate(attachment.start_date) }} -
                    {{ formatDate(attachment.end_date) }}
                    <span v-if="attachment.prefecture">
                        @
                        {{
                            attachment.prefecture.map((p) => p.name).join(", ")
                        }}
                    </span>
                </div>
                <!-- Mini Map -->
                <div
                    v-if="
                        attachment.prefecture &&
                        attachment.prefecture.length > 0
                    "
                    class="mt-2"
                >
                    <MiniMap :prefecture-name="attachment.prefecture[0].name" />
                </div>
            </div>
        </Link>

        <!-- Photo -->
        <div v-else-if="isPhoto" class="relative">
            <img
                :src="attachment.url"
                :alt="attachment.caption"
                class="w-full h-64 object-cover"
            />
            <div
                v-if="attachment.caption"
                class="absolute bottom-0 left-0 right-0 bg-black/50 text-white p-2 text-sm"
            >
                {{ attachment.caption }}
            </div>
            <Link
                :href="route('trips.show', attachment.trip_id)"
                class="absolute top-2 right-2 bg-white/80 p-1 rounded-full text-xs hover:bg-white"
            >
                📷 元の旅行を見る
            </Link>
        </div>

        <!-- Suggestion -->
        <Link
            v-else-if="isSuggestion"
            :href="route('suggestions.show', attachment.id)"
            class="block hover:bg-gray-100 transition"
        >
            <div class="p-3">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xl">🤖</span>
                    <h3 class="font-bold text-gray-800">
                        {{ attachment.title }}
                    </h3>
                </div>
                <div class="text-sm text-gray-600 line-clamp-2">
                    {{
                        attachment.content?.summary ||
                        "AIによる旅行プランの提案です。"
                    }}
                </div>
                <!-- Mini Map -->
                <div v-if="attachment.prefecture_code" class="mt-2">
                    <MiniMap :prefecture-code="attachment.prefecture_code" />
                </div>

                <!-- Voting Buttons (for Suggestion) -->
                <div
                    v-if="post"
                    class="mt-3 flex flex-wrap gap-2 border-t pt-2 border-gray-100"
                >
                    <button
                        @click.prevent="$emit('vote', 'want_to_go')"
                        class="flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold transition"
                        :class="
                            post.is_want_to_go
                                ? 'bg-orange-100 text-orange-600'
                                : 'bg-gray-100 text-gray-500 hover:bg-gray-200'
                        "
                    >
                        <span>🙌</span> 行きたい！
                        <span
                            v-if="post.want_to_go_count > 0"
                            class="ml-1 text-[10px]"
                            >{{ post.want_to_go_count }}</span
                        >
                    </button>
                    <button
                        @click.prevent="$emit('vote', 'interested')"
                        class="flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold transition"
                        :class="
                            post.is_interested
                                ? 'bg-blue-100 text-blue-600'
                                : 'bg-gray-100 text-gray-500 hover:bg-gray-200'
                        "
                    >
                        <span>👀</span> 気になる
                        <span
                            v-if="post.interested_count > 0"
                            class="ml-1 text-[10px]"
                            >{{ post.interested_count }}</span
                        >
                    </button>
                    <button
                        @click.prevent="$emit('vote', 'on_hold')"
                        class="flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold transition"
                        :class="
                            post.is_on_hold
                                ? 'bg-yellow-100 text-yellow-600'
                                : 'bg-gray-100 text-gray-500 hover:bg-gray-200'
                        "
                    >
                        <span>🤔</span> 保留
                        <span
                            v-if="post.on_hold_count > 0"
                            class="ml-1 text-[10px]"
                            >{{ post.on_hold_count }}</span
                        >
                    </button>
                </div>

                <div class="mt-2 text-xs text-blue-600 font-medium">
                    プラン詳細を見る &rarr;
                </div>
            </div>
        </Link>

        <!-- Post (Quote Retweet) -->
        <div v-else-if="isPost" class="p-3 bg-white">
            <div class="flex items-center gap-2 mb-2">
                <div
                    class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600"
                >
                    {{ attachment.user?.name?.charAt(0) }}
                </div>
                <span class="font-bold text-sm text-gray-800">{{
                    attachment.user?.name
                }}</span>
                <span class="text-xs text-gray-500">{{
                    formatDate(attachment.created_at)
                }}</span>
            </div>
            <div class="text-sm text-gray-800 mb-2">
                {{ attachment.content }}
            </div>
            <!-- Nested Attachment (if any) -->
            <AttachmentCard
                v-if="attachment.attachment"
                :type="attachment.attachment_type"
                :attachment="attachment.attachment"
                class="mt-2 text-xs"
            />
        </div>
    </div>
</template>
