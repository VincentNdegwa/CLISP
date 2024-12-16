<template>
    <Head title="Settings" />
    <AuthenticatedLayout>
        <h1>Setting</h1>
    </AuthenticatedLayout>
</template>

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
