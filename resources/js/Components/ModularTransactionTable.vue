<script>
import { ref, computed } from "vue";
import ModularDataTable from "@/Components/ModularDataTable.vue";
import { currencyConvertor } from "@/Store/CurrencyConvertStore";
import { useUserStore } from "@/Store/UserStore";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

export default {
    components: {
        ModularDataTable,
        ConfirmationModal,
        PrimaryButton,
    },
    props: {
        transactions: {
            type: Object,
            required: true,
        },
        loading: {
            type: Boolean,
            default: false,
        },
        isB2B: {
            type: Boolean,
            required: true,
        },
        transactionType: {
            type: String,
            required: true,
        },
    },
    emits: [
        "update",
        "delete",
        "pay",
        "record-payment",
        "approve",
        "cancel",
        "view",
        "filter-change",
        "search",
        "page-change",
        "clear-filters",
        "new-transaction",
        "new-purchase",
        "new-borrowing",
        "new-lease",
        "new-sale",
    ],
    setup(props, { emit }) {
        const confirmation = ref({
            isOpen: false,
            title: "",
            message: "",
            method: null,
        });

        const columns = computed(() => [
            {
                header: "Type",
                field: "transaction_type",
                sortable: true,
                bodyStyle: "max-width: 70px",
                bodyClass: "text-center",
                body: (data) => {
                    if (data.transaction_type === "Outgoing") {
                        return '<i class="pi pi-arrow-up-right text-red-500"></i>';
                    } else if (data.transaction_type === "Incoming") {
                        return '<i class="pi pi-arrow-down-left text-green-500"></i>';
                    } else {
                        return '<i class="pi pi-arrow-down-up text-blue-500"></i>';
                    }
                },
            },
            {
                header: "Initiator",
                field: "initiator.business_name",
                sortable: true,
                body: (data) => getBusinessName(data.initiator),
            },
            {
                header: props.isB2B ? "Receiver Business" : "Receiver Customer",
                field: props.isB2B
                    ? "receiver_business.business_name"
                    : "receiver_customer.full_names",
                sortable: true,
                body: (data) => {
                    if (props.isB2B) {
                        return getBusinessName(data.receiver_business);
                    } else {
                        return getCustomerName(data.receiver_customer);
                    }
                },
            },
            {
                header: "Status",
                field: "status",
                sortable: true,
                body: (data) => {
                    return {
                        severity: getBadgeSeverity(data.status),
                        value: data.status,
                    };
                },
            },
            {
                header: "Total Price",
                field: "totalPrice",
                sortable: true,
                body: (data) => convertCurrency(data.totalPrice),
            },
            {
                header: "Date Created",
                field: "created_at",
                sortable: true,
                body: (data) => formatDate(data.created_at),
            },
        ]);

        const filters = ref([
            {
                label: "Status",
                field: "status",
                type: "dropdown",
                options: [
                    { label: "All", value: null },
                    { label: "Pending", value: "pending" },
                    { label: "Approved", value: "approved" },
                    { label: "Paid", value: "paid" },
                    { label: "Dispatched", value: "dispatched" },
                    { label: "Completed", value: "completed" },
                    { label: "Canceled", value: "canceled" },
                    { label: "Returned", value: "returned" },
                ],
            },
            {
                label: "Type",
                field: "incoming",
                type: "dropdown",
                options: [
                    { label: "All", value: "all" },
                    { label: "Incoming", value: "incoming" },
                    { label: "Outgoing", value: "outgoing" },
                ],
            },
        ]);

        const toolbarActions = computed(() => {
            const actions = [];

            // Add action based on transaction type
            const actionLabel = `New ${props.transactionType}`;
            const actionEvent = `new-${props.transactionType.toLowerCase()}`;

            actions.push({
                label: actionLabel,
                icon: "pi pi-plus",
                command: () => emit(actionEvent),
            });

            return actions;
        });

        const getRowActions = (data) => {
            const actions = [];

            
            // View action is always available
            actions.push({
                label: "View",
                icon: "pi pi-arrow-up-right",
                command: () => emit("view", data),
            });

            // Edit action
            if (data.transaction_type === "Outgoing") {
                actions.push({
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: () => emit("update", data),
                });
            }

            // Approve action
            if (
                ((data.transaction_type === "Incoming" && props.isB2B) ||
                    (data.transaction_type === "Outgoing" && !props.isB2B)) &&
                data.status === "pending"
            ) {
                actions.push({
                    label: "Approve",
                    icon: "pi pi-check",
                    command: () =>
                        openConfirmation(
                            "Are you sure you want to approve this transaction?",
                            "Approve Transaction",
                            () => emit("approve", data)
                        ),
                });
            }

            // Record Payment action
            if (
                data.transaction_type === "Outgoing" &&
                data.status === "approved"
            ) {
                actions.push({
                    label: "Record Payment",
                    icon: "pi pi-wallet",
                    command: () => emit("record-payment", data),
                });
            }

            // Cancel action
            if (
                (data.transaction_type === "Incoming" ||
                    data.transaction_type === "Outgoing") &&
                data.status === "approved"
            ) {
                actions.push({
                    label: "Cancel",
                    icon: "pi pi-times",
                    command: () =>
                        openConfirmation(
                            "Are you sure you want to cancel this transaction?",
                            "Cancel Transaction",
                            () => emit("cancel", data)
                        ),
                });
            }

            // Invoice action
            if (["approved", "paid", "completed"].includes(data.status)) {
                actions.push({
                    label: "Invoice",
                    icon: "pi pi-receipt",
                    command: () => {
                        const printWindow = window.open(
                            `/transaction/view-receipt/print/${data.id}/${
                                useUserStore().business
                            }`,
                            "_blank"
                        );
                    },
                });
            }

            // Delete action
            if (data.status === "canceled") {
                actions.push({
                    label: "Delete",
                    icon: "pi pi-trash",
                    command: () =>
                        openConfirmation(
                            "Are you sure you want to delete this transaction?",
                            "Delete Transaction",
                            () => emit("delete", data)
                        ),
                });
            }

            return actions;
        };

        const openConfirmation = (message, title, method) => {
            confirmation.value.isOpen = true;
            confirmation.value.message = message;
            confirmation.value.title = title;
            confirmation.value.method = method;
        };

        const confirmAction = () => {
            confirmation.value.method();
            closeConfirmation();
        };

        const closeConfirmation = () => {
            confirmation.value.isOpen = false;
            confirmation.value.message = "";
            confirmation.value.title = "";
            confirmation.value.method = null;
        };

        const clearFilters = () => {
            emit("clear-filters");
        };

        // Helper functions
        const getBusinessName = (business) => {
            return business ? business.business_name : "N/A";
        };

        const getCustomerName = (customer) => {
            return customer ? customer.full_names : "N/A";
        };

        const formatDate = (date) => {
            const options = {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "numeric",
                minute: "numeric",
                hour12: false,
            };
            return new Date(date).toLocaleDateString(undefined, options);
        };

        const convertCurrency = (currency) => {
            return currencyConvertor().convertMyCurrency(currency);
        };

        const getBadgeSeverity = (status) => {
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
        };

        return {
            columns,
            filters,
            toolbarActions,
            getRowActions,
            confirmation,
            confirmAction,
            closeConfirmation,
            clearFilters,
        };
    },
};
</script>

<template>
    <ModularDataTable
        :value="transactions.data"
        :loading="loading"
        :columns="columns"
        :rowActions="getRowActions"
        :filters="filters"
        :toolbarTitle="
            transactionType.charAt(0).toUpperCase() + transactionType.slice(1)
        "
        :startActions="toolbarActions"
        dataKey="id"
        emptyMessage="No transactions found"
        searchPlaceholder="Search..."
        @filter-change="$emit('filter-change', $event)"
        @search="$emit('search', $event)"
        @page-change="$emit('page-change', $event)"
        :paginator="true"
        :rows="transactions.per_page || 10"
        :totalRecords="transactions.total || 0"
        :rowsPerPageOptions="[10, 20, 50]"
    />

    <ConfirmationModal
        :isOpen="confirmation.isOpen"
        :title="confirmation.title"
        :message="confirmation.message"
        @confirm="confirmAction"
        @close="closeConfirmation"
    />
</template>
