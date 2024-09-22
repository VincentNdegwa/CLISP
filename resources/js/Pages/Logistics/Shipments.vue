<template>
    <Head title="Logistic Shipments" />
    <AuthenticatedLayout>
        <h2 class="text-2xl font-bold mb-3">Logistic Shipments</h2>
        <DataTable
            v-model:expandedRows="expandedRows"
            :value="shipments"
            dataKey="id"
            @rowExpand="onRowExpand"
            @rowCollapse="onRowCollapse"
            tableStyle="min-width: 60rem"
        >
            <template #header>
                <div class="flex justify-end gap-2">
                    <Button
                        text
                        icon="pi pi-plus"
                        label="Expand All"
                        @click="expandAll"
                    />
                    <Button
                        text
                        icon="pi pi-minus"
                        label="Collapse All"
                        @click="collapseAll"
                    />
                </div>
            </template>

            <!-- Expander Column -->
            <Column expander style="width: 1rem" />

            <!-- Transaction Type (Borrowing/Leasing) -->
            <Column field="type" header="Type" />

            <!-- Initiator Business -->
            <Column header="Initiator">
                <template #body="slotProps">
                    {{ slotProps.data.initiator.business_name }}
                </template>
            </Column>

            <!-- Receiver Business or Customer -->
            <Column header="Receiver">
                <template #body="slotProps">
                    <span v-if="slotProps.data.receiver_business">
                        {{ slotProps.data.receiver_business.business_name }}
                    </span>
                    <span v-else> Customer </span>
                </template>
            </Column>

            <!-- Lease Start/End Dates -->
            <Column header="Lease Period">
                <template #body="slotProps">
                    {{ formatDate(slotProps.data.lease_start_date) }} -
                    {{ formatDate(slotProps.data.lease_end_date) }}
                </template>
            </Column>

            <!-- Status -->
            <Column header="Status">
                <template #body="slotProps">
                    <Tag
                        :value="slotProps.data.status"
                        :severity="getStatusSeverity(slotProps.data.status)"
                    />
                </template>
            </Column>

            <!-- Total Price -->
            <Column header="Total Price">
                <template #body="slotProps">
                    {{ formatCurrency(slotProps.data.totalPrice) }}
                </template>
            </Column>

            <!-- Row Expansion Template -->
            <template #expansion="slotProps">
                <div class="p-1">
                    <span class="m-0 p-0 text-sm">Items in Shipment</span>
                    <DataTable :value="slotProps.data.items">
                        <Column field="id" header="Item ID"></Column>
                        <Column field="quantity" header="Quantity"></Column>

                        <Column field="status" header="Item Status">
                            <template #body="itemSlotProps">
                                <Tag
                                    :value="itemSlotProps.data.status"
                                    :severity="
                                        getItemSeverity(
                                            itemSlotProps.data.status
                                        )
                                    "
                                />
                            </template>
                        </Column>
                        <Column field="price" header="Price">
                            <template #body="itemSlotProps">
                                {{ formatCurrency(itemSlotProps.data.price) }}
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </template>
        </DataTable>
        <Toast />
    </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Tag from "primevue/tag";

export default {
    components: {
        Button,
        Column,
        DataTable,
        Tag,
        AuthenticatedLayout,
        Head,
    },
    data() {
        return {
            shipments: [
                {
                    id: 5,
                    type: "borrowing",
                    status: "pending",
                    initiator: { business_name: "BipLee" },
                    receiver_business: { business_name: "LeoVibe" },
                    lease_start_date: "2024-09-22",
                    lease_end_date: "2024-11-22",
                    totalPrice: 20002000,
                    items: [
                        {
                            id: 6,
                            status: "pending",
                            quantity: "1",
                            price: "20000000.00",
                        },
                        {
                            id: 7,
                            status: "pending",
                            quantity: "1",
                            price: "2000.00",
                        },
                    ],
                },
            ],
            expandedRows: {},
        };
    },
    methods: {
        onRowExpand(event) {
            this.$toast.add({
                severity: "info",
                summary: "Shipment Expanded",
                detail: `Shipment ID: ${event.data.id}`,
                life: 3000,
            });
        },
        onRowCollapse(event) {
            this.$toast.add({
                severity: "success",
                summary: "Shipment Collapsed",
                detail: `Shipment ID: ${event.data.id}`,
                life: 3000,
            });
        },
        expandAll() {
            this.expandedRows = this.shipments.reduce(
                (acc, p) => (acc[p.id] = true) && acc,
                {}
            );
        },
        collapseAll() {
            this.expandedRows = null;
        },
        formatDate(date) {
            const options = { year: "numeric", month: "long", day: "numeric" };
            return new Date(date).toLocaleDateString(undefined, options);
        },
        formatCurrency(value) {
            return Number(value).toLocaleString("en-US", {
                style: "currency",
                currency: "USD",
            });
        },
        getStatusSeverity(status) {
            switch (status) {
                case "pending":
                    return "info";
                case "completed":
                    return "success";
                case "canceled":
                    return "danger";
                default:
                    return null;
            }
        },
        getItemSeverity(status) {
            return this.getStatusSeverity(status);
        },
    },
};
</script>

<style></style>
