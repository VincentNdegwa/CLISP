<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import InventoryList from "./InventoryList.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        InventoryList,
    },
    data() {
        return {};
    },
};
</script>

<template>
    <Head title="Inventory" />
    <AuthenticatedLayout>
        <div class="h-[88vh] w-full hide-overflow">
            <InventoryList />
        </div>
    </AuthenticatedLayout>
</template>
<style></style>
