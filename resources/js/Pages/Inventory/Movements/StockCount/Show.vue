<script setup>
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { computed } from "vue";

const props = defineProps({
    stockCount: Object,
});

const count = computed(() => props.stockCount || {});
</script>

<template>
    <Head title="View Stock Count" />
    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h1 class="text-2xl font-semibold mb-6">Stock Count Details</h1>
                    
                    <p class="mb-4 text-gray-600">
                        This page is currently under development. Please check back later.
                    </p>
                    
                    <div class="mt-6 flex gap-2">
                        <a
                            :href="route('logistics.counts')"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded shadow"
                        >
                            Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
