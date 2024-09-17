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
            status: null,
            type: "",
            items_count: 20,
            page: 0,
            search: "",
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
            filterParams.value.incoming = filterValue;
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

        const transactionData = (id) => {
            transactionStore.getSingleTransaction(id);
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
        };
    },
    methods: {
        openModal(component) {
            this.modal.open = true;
            this.modal.component = component;
        },
        closeModal() {
            this.modal.open = false;
            this.modal.component = "";
        },
        startUpdate(id) {
            this.transactionData(id);
            this.openModal("UpdateTransaction");
        },
    },
    mounted() {
        this.changeType(this.transactionType);
    },
};
</script>

<template>
    <Head :title="transactionType" />
    <Modal :show="modal.open" :maxWidth="modal.maxWidth" @close="closeModal">
        <NewTransactionForm
            v-if="modal.component == 'NewTransaction'"
            :initiatorBusiness="InitiatorBusiness"
            :business="myBusinessStore.data"
            :customer="customerStore.customers"
            :transactionType="transactionType"
            :isB2B="isB2B"
            :products="resourceStore.items.data"
            newTransaction="true"
            transactionData="null"
            @close="closeModal"
        />
        <NewTransactionForm
            v-if="modal.component == 'UpdateTransaction'"
            :initiatorBusiness="InitiatorBusiness"
            :business="myBusinessStore.data"
            :customer="customerStore.customers"
            :transactionType="transactionType"
            :isB2B="isB2B"
            :products="resourceStore.items.data"
            newTransaction="false"
            :transactionData="transactionStore.singleTransaction"
            @close="closeModal"
        />
    </Modal>
    <AuthenticatedLayout>
        <div class="flex justify-between items-center mt-1">
            <div class="flex items-center md:gap-6 gap-2">
                <h1 class="text-slate-900 text-xl font-semibold capitalize">
                    {{ transactionType }}
                </h1>
                <!-- Filter and Search -->
                <div class="flex items-center">
                    <div class="mr-4">
                        <TextInput
                            id="search"
                            v-model="filterParams.search"
                            class="block mt-1 w-full"
                            placeholder="Search by item"
                        />
                    </div>
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
                            class="dropdown-content flex flex-col gap-2 bg-white text-slate-900 rounded-box z-[100] w-52 p-2 shadow"
                        >
                            <li class="cursor-pointer hover:bg-slate-100 p-1">
                                <a @click="handleFilter('all')">All</a>
                            </li>
                            <li class="cursor-pointer hover:bg-slate-100 p-1">
                                <a @click="handleFilter('incoming')"
                                    >Incoming</a
                                >
                            </li>
                            <li class="cursor-pointer hover:bg-slate-100 p-1">
                                <a @click="handleFilter('outgoing')"
                                    >Outgoing</a
                                >
                            </li>
                        </ul>
                    </div>
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

        <div id="outgoing">
            <TransactionDisplay
                :transactionStore="transactionStore"
                :tableHeaders="tableHeaders"
                :isB2B="isB2B"
                @startUpdate="startUpdate"
            />
        </div>

        <div
            v-if="transactionStore.transactions?.data?.length > 0"
            class="flex justify-between items-center"
        >
            <button
                :class="[
                    'py-2 px-4 rounded',
                    transactionStore.transactions?.prev_page_url == null
                        ? 'bg-gray-300 text-gray-700 cursor-not-allowed'
                        : 'bg-slate-900 text-white',
                ]"
                :disabled="transactionStore.transactions?.prev_page_url == null"
                @click="
                    navigatePage(
                        transactionStore.transactions?.current_page - 1
                    )
                "
            >
                Previous
            </button>
            <span>
                Page {{ transactionStore.transactions?.current_page }} of
                {{ transactionStore.transactions?.last_page }}
            </span>
            <button
                :class="[
                    'py-2 px-4 rounded',
                    transactionStore.transactions?.next_page_url == null
                        ? 'bg-gray-300 text-gray-700 cursor-not-allowed'
                        : 'bg-slate-900 text-white',
                ]"
                :disabled="
                    transactionStore.transactions?.next_page_url === null
                "
                @click="
                    navigatePage(
                        transactionStore.transactions?.current_page + 1
                    )
                "
            >
                Next
            </button>
        </div>
        <!-- <Paginator
            class="bg-white"
            :rows="10"
            :totalRecords="120"
            :rowsPerPageOptions="[10, 20, 30]"
        ></Paginator> -->
    </AuthenticatedLayout>
</template>

<style scoped>
/* Add any specific styles here */
</style>
