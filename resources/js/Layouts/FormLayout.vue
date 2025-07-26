<template>
    <div class="max-w-4xl mx-auto dark:bg-slate-800 text-slate-900 dark:text-slate-50  dark:backdrop-blur-sm bg-slate-50 rounded-lg shadow-lg p-6 border border-slate-200 dark:border-white/10" >
        <h2 v-if="props.title" class="text-xl font-bold mb-4">{{ props.title }}</h2>

        <slot/>
    </div>
</template>

<script setup >
const props = defineProps({
    title: {
        type: String,
        required: false
    }
});
</script>