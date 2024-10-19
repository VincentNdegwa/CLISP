<template>
    <Card>
        <template #title>Revenue by Transaction Type</template>
        <template #content>
            <Chart
                type="bar"
                :options="chartOptions"
                :data="{
                    labels: revenueByType?.map((r) => r.type),
                    datasets: [
                        {
                            label: 'Revenue',
                            data: revenueByType?.map((r) => r.revenue),
                            backgroundColor: '#66BB6A',
                        },
                    ],
                }"
                class="h-[30rem]"
            />
        </template>
    </Card>
</template>
<script>
import Card from "primevue/card";
import Chart from "primevue/chart";

export default {
    components: {
        Card,
        Chart,
    },
    props: ["revenueByType"],
    data() {
        return {
            chartOptions: {
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            color: "#333",
                        },
                        grid: {
                            display: true,
                        },
                    },
                    y: {
                        ticks: {
                            color: "#333",
                        },
                        grid: {
                            display: true,
                        },
                    },
                },
                plugins: {
                    legend: {
                        labels: {
                            color: "#333",
                        },
                    },
                },
            },
        };
    },
};
</script>
