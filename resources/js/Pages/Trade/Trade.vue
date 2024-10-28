<script>
import { ref, onMounted, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useTransactionStore } from "@/Store/TransactionStore";
import TableDisplay from "@/Layouts/TableDisplay.vue";
import TransactionDisplay from "./TransactionDisplay.vue";
import Modal from "@/Components/Modal.vue";
import NewTransactionForm from "@/Components/NewTransactionForm.vue";
import { useUserStore } from "@/Store/UserStore";
import { useMyBusiness } from "@/Store/MyBusiness";
import { useCustomerStore } from "@/Store/Customer";
import { useResourceStore } from "@/Store/Resource";

import Paginator from "primevue/paginator";
import Select from "primevue/select";
import PaymentProcess from "../Payment/PaymentProcess.vue";
import PayPalComponent from "../Payment/PayPalComponent.vue";
import { data } from "autoprefixer";

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
        PrimaryButton,
        TextInput,
        TableDisplay,
        TransactionDisplay,
        Modal,
        NewTransactionForm,
        Paginator,
        Select,
        PaymentProcess,
        PayPalComponent,
    },

    setup(props) {
        const transactionStore = useTransactionStore();
        const businessStore = useUserStore();
        const myBusinessStore = useMyBusiness();
        const customerStore = useCustomerStore();
        const resourceStore = useResourceStore();
        const InitiatorBusiness = businessStore.business;
        const isDropdownOpen = ref(false);
        const filterParams = ref({
            incoming: "all",
            isB2B: props.isB2B,
            type: "",
            items_count: 20,
            page: 0,
            search: "",
            status: null,
        });

        onMounted(() => {
            myBusinessStore.fetchActiveConnection();
            customerStore.fetchBusinessCustomers();
            resourceStore.fetchResources();
        });

        const toggleDropdown = () => {
            isDropdownOpen.value = !isDropdownOpen.value;
        };

        const handleFilter = (filterValue) => {
            filterParams.value.incoming = filterValue.value;
            transactionStore.getTransaction(filterParams.value);
            isDropdownOpen.value = false;
        };

        const openCreateNewPurchase = () => {};

        watch(
            filterParams,
            () => {
                transactionStore.getTransaction(filterParams.value);
            },
            { deep: true }
        );

        const changeType = (transactionType) => {
            filterParams.value.type = transactionType;
        };
        const navigatePage = (count) => {
            filterParams.value.page = count;
        };
        const changeRowCount = (rowCount) => {
            filterParams.value.items_count = rowCount;
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
            isDropdownOpen,
            toggleDropdown,
            handleFilter,
            openCreateNewPurchase,
            transactionStore,
            InitiatorBusiness,
            changeType,
            myBusinessStore,
            customerStore,
            resourceStore,
            filterParams,
            navigatePage,
            transactionData,
            deleteTransaction,
            changeRowCount,
            payTransaction,
        };
    },
    data() {
        return {
            tableHeaders: [
                "Trend",
                "Initiator Business Name",
                this.isB2B
                    ? "Receiver Business Name"
                    : "Receiver Customer Name",
                "Transaction Status",
                "Total Amount",
                "Created Date",
                "Actions",
            ],
            modal: {
                open: false,
                maxWidth: "4xl",
                component: "",
            },
            statuses: [
                { label: "All", value: "all" },
                { label: "Pending", value: "pending" },
                { label: "Approved", value: "approved" },
                { label: "Paid", value: "paid" },
                { label: "Dispatched", value: "dispatched" },
                { label: "Completed", value: "completed" },
                { label: "Canceled", value: "canceled" },
                { label: "Return", value: "return" },
            ],
            transaction_types: [
                { label: "All", value: "all" },
                { label: "Incomming", value: "incoming" },
                { label: "Outgoing", value: "outgoing" },
            ],
            PaymentProcess: {
                start: false,
                data: null,
                mode: null,
            },
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
        startUpdate(id) {
            this.transactionData(id);
            this.openModal("UpdateTransaction");
        },
        startDelete(id) {
            this.deleteTransaction(id);
        },
        searchStatus(value) {
            this.filterParams.status = value.value;
        },
        searchIncoming(value) {
            this.filterParams.incoming = value.value;
        },
        clearFilters() {
            this.filterParams.incoming = "all";
            this.filterParams.search = "";
            this.filterParams.status = null;
        },
        handleClickOutside(event) {
            const dropdown = this.$refs.dropdown;
            if (dropdown && !dropdown.contains(event.target)) {
                this.isDropdownOpen = false;
                this.removeClickOutsideListener();
            }
        },
        addClickOutsideListener() {
            document.addEventListener("click", this.handleClickOutside);
        },
        removeClickOutsideListener() {
            document.removeEventListener("click", this.handleClickOutside);
        },
        onPageChange(event) {
            const newPage = event.page + 1; // PrimeVue Paginator is zero-based
            this.navigatePage(newPage);
        },
        onRowChange(row) {
            this.changeRowCount(row);
        },
        startPayTransaction(transaction) {
            this.PaymentProcess.start = true;
            this.PaymentProcess.data = {
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

        completedPayment(mode) {
            this.payTransaction({
                transactionId: this.PaymentProcess.data.transactionId,
                mode: mode,
            });
        },
    },
    mounted() {
        this.addClickOutsideListener();
        this.changeType(this.transactionType);
    },
};
</script>

<template>
    <Head :title="transactionType" />
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
            @close="closeModal"
            :PaymentProcess="PaymentProcess"
        />
        <PayPalComponent
            v-if="modal.component == 'PayPalComponent'"
            :transaction="PaymentProcess.data"
            @close="closeModal"
            @completedPayment="completedPayment"
        />
    </Modal>
    <AuthenticatedLayout>
        <div class="flex justify-between items-center mt-1">
            <div class="flex items-center md:gap-6 gap-2">
                <h1 class="text-slate-900 text-xl font-semibold capitalize">
                    {{ transactionType }}
                </h1>
                <!-- Filter and Search -->
                <div class="flex items-center" ref="dropdown">
                    <div class="dropdown">
                        <div
                            tabindex="0"
                            role="button"
                            class="btn m-1 bg-slate-900 text-white"
                            @click="toggleDropdown"
                        >
                            Filters <i class="bi bi-funnel"></i>
                        </div>
                        <ul
                            tabindex="0"
                            v-if="isDropdownOpen"
                            class="dropdown-content flex flex-col gap-2 bg-white text-slate-900 rounded-t-none rounded-b-md z-[100] min-w-52 p-2 shadow"
                        >
                            <li class="flex flex-col">
                                <span>Transaction Status</span>
                                <Select
                                    @change="toggleDropdown"
                                    :options="statuses"
                                    v-model="filterParams.status"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Select Status"
                                    class="w-full"
                                />
                            </li>

                            <li class="flex flex-col">
                                <span>Transaction Type</span>
                                <Select
                                    @change="toggleDropdown"
                                    :options="transaction_types"
                                    v-model="filterParams.incoming"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Select Status"
                                    class="w-full"
                                />
                            </li>
                        </ul>
                    </div>

                    <PrimaryButton
                        @click="clearFilters"
                        class="flex gap-1 bg-slate-900"
                    >
                        <span>Clear Filters</span> <i class="bi bi-x-lg"></i>
                    </PrimaryButton>
                </div>
            </div>
            <div class="flex items-center">
                <PrimaryButton
                    @click="() => openModal('NewTransaction')"
                    class="bg-slate-900 text-white"
                >
                    New {{ transactionType }}
                </PrimaryButton>
            </div>
        </div>

        <div class="h-[75vh] overflow-auto hide-overflow">
            <TransactionDisplay
                :transactionStore="transactionStore"
                :tableHeaders="tableHeaders"
                :isB2B="isB2B"
                @startUpdate="startUpdate"
                @startDelete="startDelete"
                @payTransaction="startPayTransaction"
            />
        </div>
        <div v-if="transactionStore.transactions?.data?.length > 0">
            <Paginator
                :totalRecords="transactionStore.transactions?.total"
                :rows="transactionStore.transactions?.per_page"
                :first="
                    (transactionStore.transactions?.current_page - 1) *
                    transactionStore.transactions?.per_page
                "
                @page="onPageChange"
                @update:rows="onRowChange"
                :rowsPerPageOptions="[10, 20, 50]"
            >
            </Paginator>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped></style>
