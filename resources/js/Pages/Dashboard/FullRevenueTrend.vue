<template>
    <Card>
        <template #title>Revenue Trends</template>
        <template #content>
            <Chart
                class="h-[30rem]"
                type="line"
                :data="{
                    labels: revenueTrends?.map((r) => r.month),
                    datasets: [
                        {
                            label: 'Revenue',
                            data: revenueTrends?.map((r) => r.total_revenue),
                            fill: false,
                            borderColor: '#42A5F5',
                        },
                    ],
                }"
                :options="chartOptions"
            />
        </template>
    </Card>
</template>

<script>
import Card from "primevue/card";
import Chart from "primevue/chart";

export default {
    props: ["revenueTrends"],
    components: {
        Card,
        Chart,
    },
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
