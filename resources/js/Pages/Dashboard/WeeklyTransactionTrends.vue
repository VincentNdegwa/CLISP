<template>
    <Card >
        <template #title> Weekly Transaction Trend </template>
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
    props: ["trendData"],
    components: {
        Chart,
        Card,
    },
    setup(props) {
        // Set chart data based on incoming props
        const chartData = computed(() => {
            const incommingData = Object.values(props.trendData.Incomming);
            const outgoingData = Object.values(props.trendData.Outgoing);
            const labels = Object.keys(props.trendData.Incomming);

            return {
                labels: labels,
                datasets: [
                    {
                        label: "Incomming",
                        backgroundColor: "#f43f5e",
                        borderColor: "#e11d48",
                        data: incommingData,
                    },
                    {
                        label: "Outgoing",
                        backgroundColor: "#38bdf8",
                        borderColor: "#0ea5e9",
                        data: outgoingData,
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
