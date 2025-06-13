<template>
    <div
        class="flex flex-col items-center justify-center py-12 bg-slate-50 dark:bg-transparent dark:text-slate-50 rounded-lg"
    >
        <div class="text-rose-500 text-5xl mb-4">
            <!-- You can replace this with an icon if desired -->
            <img class="w-80 h-80" src="/images/no_data.svg" />
        </div>
        <p class="text-slate-900 text-lg font-medium">No records found</p>
        <p class="text-slate-500">We couldn't find any data to display here.</p>
    </div>
</template>

<script>
export default {
    name: "NoRecords",
};
</script>

<style scoped></style>
