<script>
import Dashboard from "@/Layouts/Dashboard.vue";
import { computed, onMounted } from "vue";
import Chart from "primevue/chart";
import Card from "primevue/card";
import Badge from "primevue/badge";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import { useDashboardStore } from "@/Store/DashboardStore";
import RevenueSummary from "./RevenueSummary.vue";
import BusinessSummary from "./BusinessSummary.vue";
import WeeklyTransactionTrends from "./WeeklyTransactionTrends.vue";
import YearRevenueByTransactionType from "./YearRevenueByTransactionType.vue";
import TransactionTypeCount from "./TransactionTypeCount.vue";
import FullRevenueTrend from "./FullRevenueTrend.vue";
import LowStockItems from "./LowStockItems.vue";
import RevenueByTransactionType from "./RevenueByTransactionType.vue";

export default {
    props: ["business_id"],
    setup(props) {
        const dashboardStore = useDashboardStore();

        const dashboardData = computed(
            () => dashboardStore.dashboardData?.dashboardData
        );
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
        Card,
        Badge,
        DataTable,
        Column,
        RevenueSummary,
        BusinessSummary,
        WeeklyTransactionTrends,
        YearRevenueByTransactionType,
        TransactionTypeCount,
        FullRevenueTrend,
        LowStockItems,
        RevenueByTransactionType,
    },
};
</script>

<template>
    <Dashboard>
        <!-- Loading Spinner -->
        <div v-if="loading" class="flex justify-center items-center py-20">
            <i class="pi pi-spin pi-spinner text-4xl text-gray-500"></i>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="alert alert-danger text-center">
            {{ error }}
        </div>

        <!-- Dashboard Content -->
        <div v-if="dashboardData && !loading" class="p-6">
            <RevenueSummary :revenueSummary="dashboardData.revenueSummary" />
            <BusinessSummary :businessSummary="dashboardData.businessSummary" />
            <div class="flex flex-col lg:flex-row gap-4 m-4">
                <div class="w-full lg:w-2/5">
                    <WeeklyTransactionTrends
                        :trendData="dashboardData.weeklyTransactionTrends"
                    />
                </div>
                <div class="w-full lg:w-3/5">
                    <YearRevenueByTransactionType
                        :revenueByTransaction="
                            dashboardData.revenueByTransaction
                        "
                    />
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-4 m-4">
                <div class="w-full">
                    <TransactionTypeCount
                        :transactionTypeCount="
                            dashboardData.transactionTypeCount
                        "
                    />
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-4 m-4">
                <div class="w-full lg:w-7/12">
                    <!-- Revenue Trends Line Chart -->
                    <FullRevenueTrend
                        :revenueTrends="dashboardData.revenueTrends"
                    />
                </div>
                <div class="w-full lg:w-5/12">
                    <!-- Low Stock Items Table (PrimeVue DataTable) -->
                    <LowStockItems
                        :lowStockItems="dashboardData.lowStockItems"
                    />
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-4 m-4">
                <!-- Revenue by Type (Grouped Bar Chart) -->
                <div class="w-full lg:w-7/12">
                    <RevenueByTransactionType
                        :revenueByType="dashboardData.revenueByType"
                    />
                </div>
            </div>
        </div>
    </Dashboard>
</template>

<style scoped></style>
