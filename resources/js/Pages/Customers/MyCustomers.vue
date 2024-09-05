<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

export default {
    components: {
        AuthenticatedLayout,
        Head,
    },
};
</script>

<template>
    <Head title="Customers" />
    <AuthenticatedLayout> my costomers </AuthenticatedLayout>
</template>
