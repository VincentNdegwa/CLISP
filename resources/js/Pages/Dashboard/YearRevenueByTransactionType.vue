<template>
    <Card>
        <template #title> Yearly Revenue by Transaction Type </template>
        <template #content>
            <Chart
                type="bar"
                :data="chartData"
                :options="chartOptions"
                class="h-[30rem]"
            />
        </template>
    </Card>
</template>

<script>
import Card from "primevue/card";
import Chart from "primevue/chart";
import { computed } from "vue";

export default {
    props: ["revenueByTransaction"],
    components: {
        Chart,
        Card,
    },
    setup(props) {
        // Helper to group data by months and transaction type
        const groupByMonthAndType = () => {
            const months = {};
            props.revenueByTransaction.forEach((item) => {
                if (!months[item.month]) {
                    months[item.month] = {
                        purchase: 0,
                        sale: 0,
                        leasing: 0,
                        borrowing: 0,
                    };
                }
                months[item.month][item.type] += item.revenue;
            });
            return months;
        };

        // Set chart data based on incoming props
        const chartData = computed(() => {
            const groupedData = groupByMonthAndType();
            const labels = Object.keys(groupedData);

            const purchaseData = labels.map(
                (month) => groupedData[month].purchase
            );
            const saleData = labels.map((month) => groupedData[month].sale);
            const leasingData = labels.map(
                (month) => groupedData[month].leasing
            );
            const borrowingData = labels.map(
                (month) => groupedData[month].borrowing
            );

            return {
                labels: labels,
                datasets: [
                    {
                        label: "Purchase",
                        backgroundColor: "#f43f5e", // Rose color
                        borderColor: "#e11d48",
                        data: purchaseData,
                    },
                    {
                        label: "Sale",
                        backgroundColor: "#38bdf8", // Light blue color
                        borderColor: "#0ea5e9",
                        data: saleData,
                    },
                    {
                        label: "Leasing",
                        backgroundColor: "#facc15", // Yellow color
                        borderColor: "#eab308",
                        data: leasingData,
                    },
                    {
                        label: "Borrowing",
                        backgroundColor: "#34d399", // Green color
                        borderColor: "#10b981",
                        data: borrowingData,
                    },
                ],
            };
        });

        // Set chart options for better styling
        const chartOptions = computed(() => {
            const textColor = "#333333";
            const textColorSecondary = "#808080";
            const surfaceBorder = "#cccccc";

            return {
                maintainAspectRatio: false,
                aspectRatio: 0.8,
                plugins: {
                    legend: {
                        labels: {
                            color: textColor,
                        },
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: textColorSecondary,
                            font: {
                                weight: 500,
                            },
                        },
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                    },
                    y: {
                        ticks: {
                            color: textColorSecondary,
                        },
                        grid: {
                            color: surfaceBorder,
                            drawBorder: false,
                        },
                    },
                },
            };
        });

        return {
            chartData,
            chartOptions,
        };
    },
};
</script>

<style scoped>
.card {
    padding: 1rem;
}
</style>
