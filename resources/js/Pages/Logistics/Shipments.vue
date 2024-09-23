<template>
    <Head title="Logistic Shipments" />
    <AuthenticatedLayout>
        <h2 class="text-2xl font-bold mb-3">Logistic Shipments</h2>
        <Toolbar class="bg-slate-900" style="padding: 0rem 1rem">
            <template #start>
                <div class="flex gap-">
                    <PrimaryButton
                        @click="exportCSV"
                        class="flex gap-2 max:h-fit h-[1rem]"
                    >
                        <span> Export </span>
                        <i class="pi pi-external-link"></i>
                    </PrimaryButton>
                </div>
            </template>
            <template #end>
                <div class="flex gap-1">
                    <PrimaryButton
                        @click="expandAll"
                        class="flex gap-2 max:h-fit h-[1rem]"
                    >
                        <span> Expand All </span> <i class="pi pi-plus"></i>
                    </PrimaryButton>

                    <PrimaryButton
                        @click="collapseAll"
                        class="flex gap-2 max:h-fit h-[1rem]"
                    >
                        <span> Callapse All </span> <i class="pi pi-minus"></i>
                    </PrimaryButton>
                </div>
            </template>
        </Toolbar>
        <TableSkeleton v-if="transactionStore.loading" />
        <DataTable
            v-else
            v-model:expandedRows="expandedRows"
            :value="transactionStore.shipments.data?.data"
            dataKey="id"
            ref="dt"
            tableStyle="min-width: 60rem"
        >
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
                <div class="p-0 -mt-3">
                    <DataTable :value="slotProps.data.items" tableStyle="">
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
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useTransactionStore } from "@/Store/TransactionStore";
import { Head } from "@inertiajs/vue3";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Tag from "primevue/tag";
import Toast from "primevue/toast";
import Toolbar from "primevue/toolbar";
import { onMounted, ref } from "vue";

export default {
    components: {
        Button,
        Column,
        DataTable,
        Tag,
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        Toolbar,
        TableSkeleton,
        Toast,
    },
    setup() {
        const transactionStore = useTransactionStore();
        const filterParams = ref({
            incoming: "all",
            type: "",
            items_count: 20,
            isB2B: false,
            page: 0,
            search: "",
            status: null,
        });
        onMounted(() => {
            transactionStore.getTransactionLogistics(filterParams.value);
        });
        return {
            filterParams,
            transactionStore,
        };
    },
    data() {
        return {
            expandedRows: {},
        };
    },
    methods: {
        expandAll() {
            this.expandedRows =
                this.transactionStore.shipments.data?.data.reduce(
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
                currency: "KES",
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
        exportCSV() {
            this.$refs.dt.exportCSV();
        },
    },
};
</script>

<style></style>
