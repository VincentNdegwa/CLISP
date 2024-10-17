<script>
import Dashboard from "@/Layouts/Dashboard.vue";
import { computed, onMounted } from "vue";
import Chart from "primevue/chart";
import Card from "primevue/card";
import Badge from "primevue/badge";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import { useDashboardStore } from "@/Store/DashboardStore";

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
        <div
            v-if="dashboardData && !loading"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6"
        >
            <!-- Total Revenue Card -->
            <Card>
                <template #title>Total Revenue</template>
                <template #content>
                    <div class="text-2xl font-extrabold text-green-600">
                        ${{ dashboardData.totalRevenue }}
                    </div>
                </template>
            </Card>

            <!-- New Customers Badge -->
            <Card>
                <template #title>New Customers</template>
                <template #content>
                    <div class="flex justify-between items-center">
                        <Badge value="New" class="p-badge-success"></Badge>
                        <span class="text-2xl font-extrabold text-blue-600">
                            {{ dashboardData.newCustomers }}
                        </span>
                    </div>
                </template>
            </Card>

            <!-- Selling Transactions by Type (Pie Chart) -->
            <Card>
                <template #title>Selling Transactions by Type</template>
                <template #content>
                    <Chart
                        type="pie"
                        :data="{
                            labels: dashboardData.sellingTransactionsByType?.map(
                                (t) => t.type
                            ),
                            datasets: [
                                {
                                    data: dashboardData.sellingTransactionsByType?.map(
                                        (t) => t.total
                                    ),
                                    backgroundColor: [
                                        '#42A5F5',
                                        '#66BB6A',
                                        '#FFA726',
                                        '#AB47BC',
                                    ],
                                },
                            ],
                        }"
                        class="w-full mt-4"
                    />
                </template>
            </Card>

            <!-- Buying Transactions by Type (Bar Chart) -->
            <Card>
                <template #title>Buying Transactions by Type</template>
                <template #content>
                    <Chart
                        type="bar"
                        :data="{
                            labels: dashboardData.buyingTransactionsByType?.map(
                                (t) => t.type
                            ),
                            datasets: [
                                {
                                    label: 'Buying Transactions',
                                    data: dashboardData.buyingTransactionsByType?.map(
                                        (t) => t.total
                                    ),
                                    backgroundColor: '#29B6F6',
                                },
                            ],
                        }"
                        class="w-full mt-4"
                    />
                </template>
            </Card>

            <!-- Low Stock Items Table (PrimeVue DataTable) -->
            <Card>
                <template #title>Low Stock Items</template>
                <template #content>
                    <DataTable
                        :value="dashboardData.lowstockItems"
                        class="mt-4"
                    >
                        <Column field="item_id" header="Item ID"></Column>
                        <Column field="quantity" header="Quantity"></Column>
                        <Column field="source" header="Source"></Column>
                        <Column
                            field="created_at"
                            header="Created At"
                            :body="
                                (data) =>
                                    new Date(
                                        data.created_at
                                    ).toLocaleDateString()
                            "
                        ></Column>
                    </DataTable>
                </template>
            </Card>

            <!-- Revenue Trends Line Chart -->
            <Card>
                <template #title>Revenue Trends</template>
                <template #content>
                    <Chart
                        type="line"
                        :data="{
                            labels: dashboardData.revenueTrends?.map(
                                (r) => r.month
                            ),
                            datasets: [
                                {
                                    label: 'Revenue',
                                    data: dashboardData.revenueTrends?.map(
                                        (r) => r.total_revenue
                                    ),
                                    fill: false,
                                    borderColor: '#42A5F5',
                                },
                            ],
                        }"
                        class="w-full mt-4"
                    />
                </template>
            </Card>

            <!-- Revenue by Type (Grouped Bar Chart) -->
            <Card>
                <template #title>Revenue by Type</template>
                <template #content>
                    <Chart
                        type="bar"
                        :data="{
                            labels: dashboardData.revenueByType?.map(
                                (r) => r.type
                            ),
                            datasets: [
                                {
                                    label: 'Revenue',
                                    data: dashboardData.revenueByType?.map(
                                        (r) => r.revenue
                                    ),
                                    backgroundColor: '#66BB6A',
                                },
                            ],
                        }"
                        class="w-full mt-4"
                    />
                </template>
            </Card>
        </div>
    </Dashboard>
</template>

<style scoped></style>
