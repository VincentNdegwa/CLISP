<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-5">
            <div class="w-full mx-auto sm:px-4 lg:px-6">
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-2"
                >
                    <slot />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
