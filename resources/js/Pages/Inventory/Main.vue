<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import InventoryList from "./InventoryList.vue";
import InventoryListModular from "./InventoryListModular.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        InventoryList,
        InventoryListModular,
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
            <InventoryListModular />
        </div>
    </AuthenticatedLayout>
</template>
<style></style>
