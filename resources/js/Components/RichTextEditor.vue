<script setup>
import { onMounted, ref, watch } from "vue";
import Trix from "trix";
import "trix/dist/trix.css";

const props = defineProps({
    modelValue: String,
    placeholder: String,
});

const emit = defineEmits(["update:modelValue"]);

const editorRef = ref(null);

onMounted(() => {
    const element = editorRef.value;

    const initializeEditor = () => {
        if (props.modelValue && element.editor) {
            element.editor.loadHTML(props.modelValue);
        }
    };

    // Trixが初期化されるのを待つ
    if (element.editor) {
        initializeEditor();
    } else {
        element.addEventListener("trix-initialize", initializeEditor);
    }

    // 変更の監視
    element.addEventListener("trix-change", () => {
        emit("update:modelValue", element.value);
    });
});

// 外部からの変更を反映 (例: リセット時など)
watch(
    () => props.modelValue,
    (newValue) => {
        if (editorRef.value && newValue !== editorRef.value.value) {
            editorRef.value.editor.loadHTML(newValue || "");
        }
    }
);
</script>

<template>
    <div class="rich-text-editor">
        <input id="trix-input" type="hidden" :value="modelValue" />
        <trix-editor
            ref="editorRef"
            input="trix-input"
            class="trix-content min-h-[200px] border-gray-200 rounded-xl focus:ring-black focus:border-black bg-white prose max-w-none"
            :placeholder="placeholder"
        ></trix-editor>
    </div>
</template>

<style>
/* Trix Editor Custom Styling for Modern Look */
trix-toolbar .trix-button-group {
    border-color: #e5e7eb;
    border-radius: 0.75rem;
    margin-bottom: 0.5rem;
    background-color: #f9fafb;
}

trix-toolbar .trix-button {
    border-bottom: none;
    color: #4b5563;
    transition: all 0.2s;
}

trix-toolbar .trix-button:hover {
    color: #000;
    background-color: #f3f4f6;
}

trix-toolbar .trix-button--active {
    background-color: #e5e7eb;
    color: #000;
}

trix-editor {
    border-color: #e5e7eb !important;
    border-radius: 0.75rem !important;
    padding: 1rem !important;
    font-size: 1rem;
    line-height: 1.75;
    background-color: #fff;
    transition: border-color 0.2s, ring 0.2s;
}

trix-editor:focus {
    border-color: #000 !important;
    outline: none;
    box-shadow: 0 0 0 1px #000;
}

/* Hide file upload button if not needed, or style it */
.trix-button--icon-attach {
    display: none; /* シンプルにするため一旦隠す */
}
</style>
