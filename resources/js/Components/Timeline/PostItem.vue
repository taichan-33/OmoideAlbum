<script setup>
import { ref } from "vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import AttachmentCard from "@/Components/Timeline/AttachmentCard.vue";

const props = defineProps({
    post: Object,
});

const emit = defineEmits(["reply", "quote"]);

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = (now - date) / 1000; // seconds

    if (diff < 60) return "今";
    if (diff < 3600) return `${Math.floor(diff / 60)}分前`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}時間前`;
    return date.toLocaleDateString("ja-JP", { month: "short", day: "numeric" });
};

const toggleReaction = (type) => {
    const form = useForm({ type });
    form.post(route("timeline.reaction", props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Optimistic update could be done here if needed,
            // but Inertia will reload the props automatically.
        },
    });
};

const parseContent = (content) => {
    if (!content) return [];
    // Split by hashtags (e.g., #温泉)
    // Note: This regex is simple and might need refinement for complex cases
    const regex = /(#\S+)/g;
    return content
        .split(regex)
        .map((text) => ({
            text,
            isHashtag: text.startsWith("#"),
        }))
        .filter((segment) => segment.text);
};

const visitPost = () => {
    router.visit(route("timeline.show", props.post.id));
};
</script>

<template>
    <div class="bg-white p-4 rounded-lg shadow border border-gray-100">
        <div class="flex gap-3">
            <div class="flex-shrink-0">
                <div
                    class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-lg font-bold text-indigo-600"
                >
                    {{ post.user.name.charAt(0) }}
                </div>
            </div>
            <div class="flex-grow">
                <div class="flex justify-between items-baseline">
                    <div class="font-bold text-gray-900">
                        {{ post.user.name }}
                    </div>
                    <Link
                        :href="route('timeline.show', post.id)"
                        class="text-xs text-gray-500 hover:underline"
                    >
                        {{ formatDate(post.created_at) }}
                    </Link>
                </div>

                <div
                    class="mt-1 text-gray-800 whitespace-pre-wrap hover:bg-gray-50 p-1 -ml-1 rounded transition cursor-pointer"
                    @click="visitPost"
                >
                    <template
                        v-for="(segment, index) in parseContent(post.content)"
                        :key="index"
                    >
                        <Link
                            v-if="segment.isHashtag"
                            :href="
                                route('trips.index', {
                                    tag: segment.text.substring(1),
                                })
                            "
                            class="text-blue-600 hover:underline"
                            @click.stop
                            >{{ segment.text }}</Link
                        >
                        <template v-else>{{ segment.text }}</template>
                    </template>
                </div>

                <!-- Attachment -->
                <AttachmentCard
                    v-if="post.attachment"
                    :type="post.attachment_type"
                    :attachment="post.attachment"
                    :post="post"
                    @vote="toggleReaction"
                />

                <!-- Actions -->
                <div class="mt-3 flex gap-6 text-gray-500 text-sm">
                    <!-- Reply -->
                    <button
                        @click="$emit('reply', post)"
                        class="hover:text-blue-500 flex items-center gap-1 transition"
                    >
                        <i class="bi bi-chat"></i>
                        <span v-if="post.replies_count > 0">{{
                            post.replies_count
                        }}</span>
                    </button>

                    <!-- Quote Retweet -->
                    <button
                        @click="$emit('quote', post)"
                        class="hover:text-green-500 flex items-center gap-1 transition"
                    >
                        <i class="bi bi-arrow-repeat"></i>
                    </button>

                    <!-- Like -->
                    <button
                        @click="toggleReaction('like')"
                        class="flex items-center gap-1 transition"
                        :class="
                            post.is_liked
                                ? 'text-pink-500'
                                : 'hover:text-pink-500'
                        "
                    >
                        <i
                            :class="
                                post.is_liked
                                    ? 'bi bi-heart-fill'
                                    : 'bi bi-heart'
                            "
                        ></i>
                        <span v-if="post.likes_count > 0">{{
                            post.likes_count
                        }}</span>
                    </button>

                    <!-- Fun -->
                    <button
                        @click="toggleReaction('fun')"
                        class="flex items-center gap-1 transition"
                        :class="
                            post.is_fun
                                ? 'text-orange-500'
                                : 'hover:text-orange-500'
                        "
                    >
                        <i
                            :class="
                                post.is_fun
                                    ? 'bi bi-emoji-laughing-fill'
                                    : 'bi bi-emoji-laughing'
                            "
                        ></i>
                        <span v-if="post.funs_count > 0">{{
                            post.funs_count
                        }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
