<script setup>
import { onMounted, ref } from "vue";

const model = defineModel({
    type: String,
    required: true,
});

const props = defineProps({
    rows: {
        type: Number,
        default: 3,
    },
    autoResize: {
        type: Boolean,
        default: true,
    }
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute("autofocus")) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <textarea
        class="border-rose-500 border w-full dark:bg-slate-900 text-slate-900 dark:text-white focus:border-rose-500 focus:ring focus:ring-rose-500/20 rounded-md shadow-sm"
        v-model="model"
        :rows="rows"
        :autoResize="autoResize"
        ref="input"
    ></textarea>
</template>
