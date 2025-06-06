<script setup>
defineProps({
    value: {
        type: String,
    },
    required: {
        type: Boolean,
        default: false, 
    },
});
</script>

<template>
    <label class="block mb-3 dark:text-white text-slate-900 font-medium text-sm ">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
        <span v-if="required" class="text-red-500">*</span>
    </label>
</template>
