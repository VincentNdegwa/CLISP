<template>
    <div class="h-full">
        <div class="table-display">
            <DataTable
                v-if="
                    transactionStore.transactions.data &&
                    transactionStore.transactions?.data?.length > 0
                "
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
                                @click="toggle(slotProps.data.id, $event)"
                                aria-haspopup="true"
                                severity="contrast"
                                size="small"
                                aria-controls="overlay_menu"
                            />
                            <Menu
                                ref="menu"
                                :id="'overlay_menu_' + slotProps.data.id"
                                :model="actionItem"
                                :popup="true"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
            <NoRecords v-else />
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
import NoRecords from "@/Components/NoRecords.vue";
import { useUserStore } from "@/Store/UserStore";
import { currencyConvertor } from "@/Store/CurrencyConvertStore";

export default {
    emits: ["startUpdate", "startDelete", "payTransaction"],
    components: {
        TableDisplay,
        ConfirmationModal,
        Tag,
        Column,
        DataTable,
        Button,
        Menu,
        NoRecords,
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
            actionItem: [],
            transactionId: null,
        };
    },
    methods: {
        getActionItems(transactionId) {
            const defaultActionItems = [
                {
                    label: "View",
                    icon: "pi pi-arrow-up-right",
                    command: () => {
                        this.getUrl();
                    },
                },
            ];
            this.actionItem = defaultActionItems;
            const data = this.transactionStore.transactions.data.find(
                (trans) => trans.id == transactionId
            );

            const transaction_type = data.transaction_type;
            const transaction_status = data.status;
            const isB2B = data.isB2B;

            let edit = {
                label: "Edit",
                icon: "pi pi-pencil",
                command: () => {
                    this.startUpdate();
                },
            };
            let approve = {
                label: "Approve",
                icon: "pi pi-check",
                command: () => {
                    this.startMakingRequestChanges(
                        "Are you sure you want to approve this transaction?",
                        "Approve Transaction",
                        () => {
                            this.transactionStore.acceptTransaction(
                                transactionId,
                                data.type
                            );
                        }
                    );
                },
            };
            // let pay = {
            //     label: "Pay",
            //     icon: "pi pi-wallet",
            //     command: () => {
            //         this.startMakingRequestChanges(
            //             "Are you sure you want to pay this transaction?",
            //             "Pay Transaction",
            //             () => {
            //                 this.$emit("payTransaction", data);
            //             }
            //         );
            //     },
            // };
            let record_payment = {
                label: "Record Payment",
                icon: "pi pi-wallet",
                command: () => {
                    this.$emit("recordPayment", data);
                },
            };
            let cancel = {
                label: "Cancel",
                icon: "pi pi-times",
                command: () => {
                    this.startMakingRequestChanges(
                        "Are you sure you want to cancel this transaction?",
                        "Cancel Transaction",
                        () => {
                            this.transactionStore.rejectTransaction(
                                transactionId,
                                "Use choose to cancel this transaction"
                            );
                        }
                    );
                },
            };
            let print = {
                label: "Invoice",
                icon: "pi pi-receipt",
                command: () => {
                    const printWindow = window.open(
                        `/transaction/view-receipt/print/${transactionId}`,
                        "_blank"
                    );
                    // printWindow.addEventListener("load", () => {
                    //     printWindow.print();
                    // });
                },
            };

            let deleteM = {
                label: "Delete",
                icon: "pi pi-trash",
                command: () => {
                    this.startDelete();
                },
            };

            let canEdit = transaction_type === "Outgoing";
            let canApprove =
                ((transaction_type == "Incoming" && isB2B == true) ||
                    (transaction_type == "Outgoing" && isB2B == false)) &&
                transaction_status == "pending";
            let canPay =
                transaction_type == "Incoming" &&
                isB2B == true &&
                transaction_status == "approved";
            let canRecordPayment =
                transaction_type == "Outgoing" &&
                transaction_status == "approved";
            let canCancel =
                (transaction_type == "Incoming" ||
                    transaction_type == "Outgoing") &&
                transaction_status == "approved";
            let canViewInvoice = true;

            let canDelete = transaction_status == "canceled";
            if (canEdit) {
                defaultActionItems.push(edit);
            }
            if (canApprove) {
                defaultActionItems.push(approve);
            }
            if (canPay) {
                defaultActionItems.push(pay);
            }
            if (canRecordPayment) {
                defaultActionItems.push(record_payment);
            }
            if (canCancel) {
                defaultActionItems.push(cancel);
            }
            if (canViewInvoice) {
                defaultActionItems.push(print);
            }
            if (canDelete) {
                defaultActionItems.push(deleteM);
            }

            this.actionItem = defaultActionItems;
        },
        getBusinessName(business) {
            return business ? business.business_name : "N/A";
        },
        startMakingRequestChanges(message, title, method) {
            this.confirmation.isOpen = true;
            this.confirmation.message = message;
            this.confirmation.title = title;
            this.confirmation.method = method;
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
            return currencyConvertor().convertMyCurrency(currency);
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
        toggle(transactionId, event) {
            this.getActionItems(transactionId);
            this.$refs.menu.toggle(event);
            this.transactionId = transactionId;
        },
    },
};
</script>
