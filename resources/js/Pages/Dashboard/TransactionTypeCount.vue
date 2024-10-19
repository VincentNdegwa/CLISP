<template>
    <Card>
        <template #title> Transactions Trends </template>
        <template #content>
            <Chart
                type="line"
                :data="chartData"
                :options="chartOptions"
                class="h-[30rem]"
            />
        </template>
    </Card>
</template>

<script>
import Chart from "primevue/chart";
import Card from "primevue/card";

export default {
    props: ["transactionTypeCount"],

    components: {
        Card,
        Chart,
    },

    data() {
        return {
            chartData: null,
            chartOptions: null,
        };
    },

    mounted() {
        this.setChartData(); // Set initial chart data
        this.chartOptions = this.setChartOptions(); // Set initial chart options
    },

    watch: {
        transactionTypeCount: {
            deep: true,
            handler() {
                this.setChartData(); // Update chart data when prop changes
            },
        },
    },

    methods: {
        setChartData() {
            const months = [
                "January",
                "February",
                "March",
                "April",
                "May",
                "June",
                "July",
                "August",
                "September",
                "October",
                "November",
                "December",
            ];

            // Prepare data for "Incomming" and "Outgoing" transaction counts
            const incomingData = months.map((month) => {
                return (
                    this.transactionTypeCount?.Incomming?.[month]?.reduce(
                        (sum, item) => sum + item.count,
                        0
                    ) || 0
                );
            });

            const outgoingData = months.map((month) => {
                return (
                    this.transactionTypeCount?.Outgoing?.[month]?.reduce(
                        (sum, item) => sum + item.count,
                        0
                    ) || 0
                );
            });

            // Define chartData with static hex colors
            this.chartData = {
                labels: months,
                datasets: [
                    {
                        label: "Incomming",
                        fill: false,
                        borderColor: "#f43f5e", // Hex color for Incomming line border
                        backgroundColor: "#fecdd3", // Hex color for Incomming line background
                        yAxisID: "y",
                        tension: 0.4,
                        data: incomingData,
                    },
                    {
                        label: "Outgoing",
                        fill: false,
                        borderColor: "#3b82f6", // Hex color for Outgoing line border
                        backgroundColor: "#dbeafe", // Hex color for Outgoing line background
                        yAxisID: "y1",
                        tension: 0.4,
                        data: outgoingData,
                    },
                ],
            };
        },

        setChartOptions() {
            return {
                stacked: false,
                maintainAspectRatio: false,
                aspectRatio: 0.6,
                plugins: {
                    legend: {
                        labels: {
                            color: "#374151", // Hex color for legend labels
                        },
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: "#6b7280", // Hex color for x-axis ticks
                        },
                        grid: {
                            color: "#e5e7eb", // Hex color for x-axis grid lines
                        },
                    },
                    y: {
                        type: "linear",
                        display: true,
                        position: "left",
                        ticks: {
                            color: "#6b7280", // Hex color for y-axis ticks (left)
                        },
                        grid: {
                            color: "#e5e7eb", // Hex color for y-axis grid lines
                        },
                    },
                    y1: {
                        type: "linear",
                        display: true,
                        position: "right",
                        ticks: {
                            color: "#6b7280", // Hex color for y1-axis ticks (right)
                        },
                        grid: {
                            drawOnChartArea: false,
                            color: "#e5e7eb", // Hex color for y1-axis grid lines (not drawn on chart)
                        },
                    },
                },
            };
        },
    },
};
</script>
