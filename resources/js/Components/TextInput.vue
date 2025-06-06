<script setup>
import { onMounted, ref } from "vue";

const model = defineModel({
    type: String,
    required: true,
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
    <input
        class="border-rose-500 border w-full focus:border-rose-500 focus:ring focus:ring-rose-500/20 rounded-md shadow-sm"
        v-model="model"
        ref="input"
    />
</template>
