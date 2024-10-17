<script>
import Dashboard from "@/Layouts/Dashboard.vue";
import { computed, onMounted, ref } from "vue";
import Chart from "primevue/chart";
import { useDashboardStore } from "@/Store/DashboardStore";

export default {
    props: ["business_id"],
    data() {},
    setup(props) {
        const dashboardStore = useDashboardStore();

        const dashboardData = computed(() => dashboardStore.dashboardData);
        const loading = computed(() => dashboardStore.loading);
        const error = computed(() => dashboardStore.error);

        onMounted(() => {
            dashboardStore.fetchDashboardDetails(props.business_id);
        });

        return { dashboardData, loading, error };
    },
    components: {
        Dashboard,
        Chart,
    },
    mounted() {
        console.log(this.dashboardData);
    },
};
</script>

<template>
    <Dashboard> </Dashboard>
</template>

<style scoped></style>
