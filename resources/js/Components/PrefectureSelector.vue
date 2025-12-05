<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from "vue";

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update:modelValue"]);

const prefectures = [
    "北海道",
    "青森県",
    "岩手県",
    "宮城県",
    "秋田県",
    "山形県",
    "福島県",
    "茨城県",
    "栃木県",
    "群馬県",
    "埼玉県",
    "千葉県",
    "東京都",
    "神奈川県",
    "新潟県",
    "富山県",
    "石川県",
    "福井県",
    "山梨県",
    "長野県",
    "岐阜県",
    "静岡県",
    "愛知県",
    "三重県",
    "滋賀県",
    "京都府",
    "大阪府",
    "兵庫県",
    "奈良県",
    "和歌山県",
    "鳥取県",
    "島根県",
    "岡山県",
    "広島県",
    "山口県",
    "徳島県",
    "香川県",
    "愛媛県",
    "高知県",
    "福岡県",
    "佐賀県",
    "長崎県",
    "熊本県",
    "大分県",
    "宮崎県",
    "鹿児島県",
    "沖縄県",
];

const searchQuery = ref("");
const isOpen = ref(false);
const inputRef = ref(null);
const containerRef = ref(null);

// Filtered list based on search query
const filteredPrefectures = computed(() => {
    if (!searchQuery.value) return prefectures;
    return prefectures.filter((pref) => pref.includes(searchQuery.value));
});

// Add prefecture
const addPrefecture = (pref) => {
    if (!props.modelValue.includes(pref)) {
        emit("update:modelValue", [...props.modelValue, pref]);
    }
    searchQuery.value = "";
    isOpen.value = false;
    inputRef.value?.focus();
};

// Remove prefecture
const removePrefecture = (pref) => {
    emit(
        "update:modelValue",
        props.modelValue.filter((p) => p !== pref)
    );
};

// Handle backspace to remove last item
const handleKeydown = (e) => {
    if (
        e.key === "Backspace" &&
        searchQuery.value === "" &&
        props.modelValue.length > 0
    ) {
        removePrefecture(props.modelValue[props.modelValue.length - 1]);
    }
};

// Click outside to close
const handleClickOutside = (e) => {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <div ref="containerRef" class="relative">
        <div
            class="w-full border-gray-200 rounded-xl focus-within:ring-2 focus-within:ring-black focus-within:border-transparent transition py-2 px-3 bg-gray-50 focus-within:bg-white border min-h-[50px] flex flex-wrap gap-2 items-center"
            @click="inputRef?.focus()"
        >
            <!-- Selected Tags -->
            <div
                v-for="pref in modelValue"
                :key="pref"
                class="bg-black text-white text-sm px-3 py-1 rounded-full flex items-center gap-1"
            >
                <span>{{ pref }}</span>
                <button
                    @click.stop="removePrefecture(pref)"
                    class="hover:text-gray-300 focus:outline-none"
                >
                    <svg
                        class="w-3 h-3"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        ></path>
                    </svg>
                </button>
            </div>

            <!-- Input -->
            <input
                ref="inputRef"
                v-model="searchQuery"
                type="text"
                class="flex-1 bg-transparent border-none focus:ring-0 p-0 text-sm min-w-[100px]"
                placeholder="都道府県を検索..."
                @focus="isOpen = true"
                @keydown="handleKeydown"
            />
        </div>

        <!-- Dropdown -->
        <div
            v-if="isOpen && filteredPrefectures.length > 0"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-100 rounded-xl shadow-lg max-h-60 overflow-y-auto"
        >
            <ul class="py-1">
                <li
                    v-for="pref in filteredPrefectures"
                    :key="pref"
                    @click="addPrefecture(pref)"
                    class="px-4 py-2 hover:bg-gray-50 cursor-pointer text-sm flex justify-between items-center"
                    :class="{
                        'bg-gray-50 text-gray-400 cursor-not-allowed':
                            modelValue.includes(pref),
                    }"
                >
                    <span>{{ pref }}</span>
                    <span v-if="modelValue.includes(pref)" class="text-xs"
                        >選択済み</span
                    >
                </li>
            </ul>
        </div>
    </div>
</template>
