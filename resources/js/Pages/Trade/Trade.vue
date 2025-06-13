<script>
import { ref, onMounted, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { useTransactionStore } from "@/Store/TransactionStore";
import Modal from "@/Components/Modal.vue";
import NewTransactionForm from "@/Components/NewTransactionForm.vue";
import { useUserStore } from "@/Store/UserStore";
import { useMyBusiness } from "@/Store/MyBusiness";
import { useCustomerStore } from "@/Store/Customer";
import { useResourceStore } from "@/Store/Resource";
import ModularTransactionTable from "@/Components/ModularTransactionTable.vue";
import AlertNotification from "@/Components/AlertNotification.vue";
import PaymentProcess from "../Payment/PaymentProcess.vue";
import SellerCheckout from "../Payment/SellerCheckout.vue";
import { usePaymentMethods } from "@/Store/PaymentMethods";

export default {
    props: {
        transactionType: {
            type: String,
            required: true,
        },
        isB2B: {
            type: Boolean,
            required: true,
        },
    },
    components: {
        AuthenticatedLayout,
        Head,
        Modal,
        NewTransactionForm,
        ModularTransactionTable,
        AlertNotification,
        PaymentProcess,
        SellerCheckout,
    },

    setup(props) {
        const transactionStore = useTransactionStore();
        const businessStore = useUserStore();
        const myBusinessStore = useMyBusiness();
        const customerStore = useCustomerStore();
        const resourceStore = useResourceStore();
        const paymentMethodsStore = usePaymentMethods();
        const InitiatorBusiness = businessStore.business;
        
        const filterParams = ref({
            incoming: "all",
            isB2B: props.isB2B,
            type: "",
            items_count: 20,
            page: 0,
            search: "",
            status: null,
        });

        onMounted(async () => {
            myBusinessStore.fetchActiveConnection();
            customerStore.fetchBusinessCustomers();
            resourceStore.fetchResources();
            await paymentMethodsStore.fetchPaymentMethods();
        });

        watch(
            filterParams,
            () => {
                transactionStore.getTransaction(filterParams.value);
            },
            { deep: true }
        );

        const handleFilterChange = (filters) => {
            if ((filters.status !== undefined) && filters.status !== null) {
                filterParams.value.status = filters.status;
            }
            if ((filters.incoming !== undefined) && filters.incoming !== null) {
                filterParams.value.incoming = filters.incoming;
            }
        };

        const handleSearch = (query) => {
            filterParams.value.search = query;
        };

        const handlePageChange = (event) => {
            filterParams.value.page = event.page + 1; // PrimeVue Paginator is zero-based
            filterParams.value.items_count = event.rows;
        };

        const clearFilters = () => {
            filterParams.value.incoming = "all";
            filterParams.value.search = "";
            filterParams.value.status = null;
        };

        const changeType = (transactionType) => {
            filterParams.value.type = transactionType;
        };

        const transactionData = (id) => {
            transactionStore.getSingleTransaction(id);
        };

        const deleteTransaction = async (id) => {
            await transactionStore.deleteTransaction(id);
        };

        const payTransaction = async (params) => {
            await transactionStore.payTransaction(params);
        };

        return {
            transactionStore,
            InitiatorBusiness,
            myBusinessStore,
            customerStore,
            resourceStore,
            filterParams,
            paymentMethodsStore,
            handleFilterChange,
            handleSearch,
            handlePageChange,
            clearFilters,
            changeType,
            transactionData,
            deleteTransaction,
            payTransaction,
        };
    },
    data() {
        return {
            modal: {
                open: false,
                maxWidth: "4xl",
                component: "",
            },
            notification: {
                open: false,
                message: "",
                status: "error",
            },
            paymentProcess: {
                start: false,
                data: null,
                mode: null,
            },
            selectedTransaction: null,
        };
    },
    methods: {
        openModal(component, width) {
            this.modal.open = true;
            this.modal.component = component;
            if (width) {
                this.modal.maxWidth = width;
            }
        },
        closeModal() {
            this.modal.open = false;
            this.modal.component = "";
        },
        handleUpdate(transaction) {
            this.transactionData(transaction.id);
            this.openModal("UpdateTransaction");
        },
        handleDelete(transaction) {
            this.deleteTransaction(transaction.id);
        },
        handleView(transaction) {
            window.location.href = `/transaction/view/${transaction.id}`;
        },
        handleApprove(transaction) {
            this.transactionStore.acceptTransaction(transaction.id, transaction.type);
        },
        handleCancel(transaction) {
            this.transactionStore.rejectTransaction(transaction.id, "User chose to cancel this transaction");
        },
        handlePayment(transaction) {
            this.paymentProcess.start = true;
            this.paymentProcess.data = {
                transactionId: transaction.id,
                items: transaction.items.map((item) => {
                    return {
                        id: item.id,
                        quantity: item.quantity,
                        price: parseFloat(item.price).toFixed(2),
                        description: item.item.description,
                        name: item.item.item_name,
                        image: item.item.item_image,
                        usdPrice: item.usdPrice,
                    };
                }),
                transaction: transaction,
            };

            this.openModal("PaymentProcess", "6xl");
        },
        handleRecordPayment(transaction) {
            this.selectedTransaction = transaction;
            this.openModal("SellerCheckout");
        },
        // Generic handler for new transaction
        handleNewTransaction() {
            this.openModal("NewTransaction");
        },
        // Specific handlers for different transaction types
        handleNewPurchase() {
            this.openModal("NewTransaction");
        },
        handleNewSale() {
            this.openModal("NewTransaction");
        },
        handleNewBorrowing() {
            this.openModal("NewTransaction");
        },
        handleNewLease() {
            this.openModal("NewTransaction");
        },
        handleSuccessPayment(data) {
            try {
                if (data.error) {
                    if (data.errors) {
                        this.openNotification(data.errors, "error");
                    } else {
                        this.openNotification(data.message, "error");
                    }
                } else {
                    this.openNotification(data.message, "success");
                    const index =
                        this.transactionStore.transactions?.data?.findIndex(
                            (transaction) =>
                                transaction.id === data.data.transaction.id
                        );
                    if (index !== -1) {
                        this.transactionStore.transactions.data[index] =
                            data.data.transaction;
                    }
                    this.closeModal();
                }
            } catch (error) {
                console.log(error);
            }
        },
        openNotification(message, status) {
            this.notification.open = true;
            this.notification.message = message;
            this.notification.status = status;
        },
    },
    mounted() {
        this.changeType(this.transactionType);
    },
    computed: {
        transactionTypeHead() {
            if (this.transactionType) {
                return (
                    this.transactionType.charAt(0).toUpperCase() +
                    this.transactionType.slice(1)
                );
            } else {
                return "--";
            }
        },
    },
};
</script>

<template>
    <Head :title="transactionTypeHead" />
    <AlertNotification
        :open="
            transactionStore.success != null || transactionStore.error != null
        "
        :message="
            transactionStore.success != null
                ? transactionStore.success
                : '' || transactionStore.error != null
                ? transactionStore.error
                : ''
        "
        :status="transactionStore.success ? 'success' : 'error'"
    />

    <AlertNotification
        :open="notification.open"
        :message="notification.message"
        :status="notification.status"
    />
    <Modal :show="modal.open" :maxWidth="modal.maxWidth" @close="closeModal">
        <NewTransactionForm
            v-if="modal.component == 'NewTransaction'"
            :initiatorBusiness="InitiatorBusiness"
            :business="myBusinessStore.data"
            :customer="customerStore.customers"
            :transactionType="transactionType"
            :isB2B="isB2B"
            :products="resourceStore.items.data"
            :newTransaction="true"
            :transactionData="null"
            @closeMe="closeModal"
        />
        <NewTransactionForm
            v-if="modal.component == 'UpdateTransaction'"
            :initiatorBusiness="InitiatorBusiness"
            :business="myBusinessStore.data"
            :customer="customerStore.customers"
            :transactionType="transactionType"
            :isB2B="isB2B"
            :products="resourceStore.items.data"
            :newTransaction="false"
            :transactionData="transactionStore.singleTransaction"
            @closeMe="closeModal"
        />
        <PaymentProcess
            v-if="modal.component == 'PaymentProcess'"
            @paymentStatus="handleSuccessPayment"
            @close="closeModal"
            :paymentProcess="paymentProcess"
        />

        <SellerCheckout
            v-if="modal.component == 'SellerCheckout'"
            @close="closeModal"
            @successPayment="handleSuccessPayment"
            :transactionData="selectedTransaction"
            :paymentMethods="paymentMethodsStore.methods"
        />
    </Modal>
    <AuthenticatedLayout>
        <ModularTransactionTable
            :transactions="transactionStore.transactions"
            :loading="transactionStore.loading"
            :isB2B="isB2B"
            :transactionType="transactionType"
            @update="handleUpdate"
            @delete="handleDelete"
            @view="handleView"
            @approve="handleApprove"
            @cancel="handleCancel"
            @pay="handlePayment"
            @record-payment="handleRecordPayment"
            @filter-change="handleFilterChange"
            @search="handleSearch"
            @page-change="handlePageChange"
            @clear-filters="clearFilters"
            @new-transaction="handleNewTransaction"
            @new-purchase="handleNewPurchase"
            @new-sale="handleNewSale"
            @new-borrowing="handleNewBorrowing"
            @new-lease="handleNewLease"
        />
        
    </AuthenticatedLayout>
</template>

<style scoped>
.hide-overflow::-webkit-scrollbar {
    display: none;
}
.hide-overflow {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
