<template>
    <div class="relative w-full h-full">
        <TableSkeleton v-if="loading" />

        <NoRecords v-else-if="!loading && data_length === 0" />

        <div v-else class="max-h-[70vh] h-[70vh] overflow-y-scroll relative">
            <table class="min-w-full relative table bg-white dark:bg-slate-800">
                <thead
                    class="sticky top-0 bg-gray-200 dark:bg-slate-700 z-[2] font-bold text-slate-950 dark:text-white w-full"
                >
                    <tr>
                        <th
                            v-for="(key, index) in keys"
                            :key="index"
                            class="py-2 px-4 border border-gray-300 dark:border-slate-600"
                        >
                            {{ key }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <slot name="row" />
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import NoRecords from "@/Components/NoRecords.vue";
import TableSkeleton from "@/Components/TableSkeleton.vue";

export default {
    components: {
        TableSkeleton,
        NoRecords,
    },
    props: {
        loading: {
            type: Boolean,
            default: false,
        },
        keys: {
            type: Array,
            required: true,
        },
        data_length: {
            type: Number,
            default: 0,
        },
    },
};
</script>

<style scoped>
/* Add any specific styles here if needed */
</style>
