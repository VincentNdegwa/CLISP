<template>
    <div class="h-[80vh]">
        <div class="table-display">
            <DataTable
                :value="transactionStore.transactions.data"
                :loading="transactionStore.loading"
                dataKey="id"
                tableStyle="width:100%"
            >
                <!-- Transaction Type (Outgoing/Incoming) -->
                <Column header="Type">
                    <template #body="slotProps">
                        <span
                            v-if="
                                slotProps.data.transaction_type === 'Outgoing'
                            "
                        >
                            <i class="bi bi-arrow-up-right"></i>
                        </span>
                        <span
                            v-else-if="
                                slotProps.data.transaction_type === 'Incoming'
                            "
                        >
                            <i class="bi bi-arrow-down-left"></i>
                        </span>
                        <span v-else>
                            <i class="bi bi-arrow-down-up"></i>
                        </span>
                    </template>
                </Column>

                <!-- Initiator Business -->
                <Column header="Initiator">
                    <template #body="slotProps">
                        {{ getBusinessName(slotProps.data.initiator) }}
                    </template>
                </Column>

                <!-- Receiver Business or Customer -->
                <Column header="Receiver">
                    <template #body="slotProps">
                        <span v-if="isB2B">
                            {{
                                getBusinessName(
                                    slotProps.data.receiver_business
                                )
                            }}
                        </span>
                        <span v-else>
                            {{
                                getCustomerName(
                                    slotProps.data.receiver_customer
                                )
                            }}
                        </span>
                    </template>
                </Column>

                <!-- Status -->
                <Column header="Status">
                    <template #body="slotProps">
                        <Tag
                            :severity="getBadgeSeverity(slotProps.data.status)"
                            :value="slotProps.data.status"
                            style="text-transform: capitalize"
                        />
                    </template>
                </Column>

                <!-- Total Price -->
                <Column header="Total Price">
                    <template #body="slotProps">
                        {{ convertCurrency(slotProps.data.totalPrice) }}
                    </template>
                </Column>

                <!-- Created At -->
                <Column header="Date Created">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.created_at) }}
                    </template>
                </Column>

                <!-- Actions -->
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="card flex justify-center">
                            <Button
                                type="button"
                                icon="pi pi-ellipsis-v"
                                @click="toggle"
                                aria-haspopup="true"
                                severity="contrast"
                                size="small"
                                aria-controls="overlay_menu"
                            />
                            <Menu
                                ref="menu"
                                id="overlay_menu"
                                :model="actionItem"
                                :popup="true"
                                @focus="
                                    () => selectedTransaction(slotProps.data.id)
                                "
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>

    <ConfirmationModal
        :isOpen="confirmation.isOpen"
        :title="confirmation.title"
        :message="confirmation.message"
        @confirm="confirmAction"
        @close="cancelMakingRequest"
    />
</template>

<script>
import TableDisplay from "@/Layouts/TableDisplay.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Tag from "primevue/tag";
import Button from "primevue/button";
import Menu from "primevue/menu";

export default {
    emits: ["startUpdate", "startDelete"],
    components: {
        TableDisplay,
        ConfirmationModal,
        Tag,
        Column,
        DataTable,
        Button,
        Menu,
    },
    props: {
        transactionStore: {
            type: Object,
            required: true,
        },
        tableHeaders: {
            type: Object,
            required: true,
        },
        isB2B: {
            required: true,
            boolead: true,
            default: true,
        },
    },
    data() {
        return {
            confirmation: {
                isOpen: false,
                title: "",
                message: "",
                method: null,
            },
            actionItem: [
                {
                    label: "View",
                    icon: "pi pi-arrow-up-right",
                    command: () => {
                        this.getUrl();
                    },
                },
                {
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: () => {
                        this.startUpdate();
                    },
                },
                {
                    label: "Delete",
                    icon: "pi pi-trash",
                    command: () => {
                        this.startDelete();
                    },
                },
            ],
            transactionId: null,
        };
    },
    methods: {
        selectedTransaction(transactionId) {
            this.transactionId = transactionId;
        },
        getBusinessName(business) {
            return business ? business.business_name : "N/A";
        },
        formatDate(date) {
            const options = {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "numeric",
                minute: "numeric",
                second: "numeric",
                hour12: false,
            };
            return new Date(date).toLocaleDateString(undefined, options);
        },
        convertCurrency(currency) {
            return Number(currency).toLocaleString("en-US", {
                style: "currency",
                currency: "KES",
            });
        },
        confirmAction() {
            this.confirmation.method();
            this.confirmation.isOpen = false;
        },
        cancelMakingRequest() {
            this.confirmation.isOpen = false;
            this.selectedRequest = null;
            this.confirmation.message = "";
            this.confirmation.title = "";
            this.confirmation.method = null;
        },
        getCustomerName(customer) {
            return customer ? customer.full_names : "N/A";
        },
        getUrl() {
            if (this.transactionId != null) {
                const url = `/transaction/view/` + this.transactionId;
                window.location.href = url;
            }
        },
        confirmDelete(id) {
            this.$emit("startDelete", this.transactionId);
        },
        startUpdate() {
            if (this.transactionId != null) {
                this.$emit("startUpdate", this.transactionId);
            }
        },
        startDelete() {
            if (this.transactionId != null) {
                this.confirmation.isOpen = true;
                this.confirmation.title = "Delete Transaction";
                this.confirmation.message =
                    "Are you sure you want to delete this transaction?";
                this.confirmation.method = () =>
                    this.confirmDelete(this.transactionId);
            }
        },
        getBadgeSeverity(status) {
            switch (status) {
                case "pending":
                    return "info";
                case "approved":
                    return "primary";
                case "paid":
                    return "success";
                case "dispatched":
                    return "warning";
                case "completed":
                    return "success";
                case "canceled":
                    return "danger";
                case "return":
                    return "danger";
                default:
                    return "secondary"; 
            }
        },
        toggle(event) {
            this.$refs.menu.toggle(event);
        },
    },
};
</script>
