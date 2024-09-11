<script>
import { ref, onMounted, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useTransactionStore } from "@/Store/TransactionStore";
import TableDisplay from "@/Layouts/TableDisplay.vue";
import Incoming from "./Incomming.vue";
import Outgoing from "./Outgoing.vue";
import Modal from "@/Components/Modal.vue";
import NewTransactionForm from "@/Components/NewTransactionForm.vue";
import { useUserStore } from "@/Store/UserStore";
import { useMyBusiness } from "@/Store/MyBusiness";
import { useCustomerStore } from "@/Store/Customer";
import { useResourceStore } from "@/Store/Resource";

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
        Incoming,
        Outgoing,
        Modal,
        NewTransactionForm,
    },

    setup() {
        const transactionStore = useTransactionStore();
        const businessStore = useUserStore();
        const myBusinessStore = useMyBusiness();
        const customerStore = useCustomerStore();
        const resourceStore = useResourceStore();
        const InitiatorBusiness = businessStore.business;
        const search = ref("");
        const filter = ref("all");
        const isDropdownOpen = ref(false);
        const incoming = ref("all");
        const type = ref("");

        onMounted(() => {
            myBusinessStore.fetchActiveConnection();
            customerStore.fetchBusinessCustomers();
            resourceStore.fetchResources();
        });

        const toggleDropdown = () => {
            isDropdownOpen.value = !isDropdownOpen.value;
        };

        const handleFilter = (filterValue) => {
            incoming.value = filterValue;
            transactionStore.getTransaction({
                type: type.value,
                incoming: incoming.value,
            });
            isDropdownOpen.value = false;
        };

        const openCreateNewPurchase = () => {};

        watch([search, filter, incoming, type], () => {
            transactionStore.getTransaction({
                type: type.value,
                incoming: incoming.value,
                search: search.value,
                filter: filter.value,
            });
        });

        const changeType = (transactionType) => {
            type.value = transactionType;
        };

        return {
            search,
            filter,
            isDropdownOpen,
            toggleDropdown,
            handleFilter,
            openCreateNewPurchase,
            transactionStore,
            incoming,
            InitiatorBusiness,
            changeType,
            myBusinessStore,
            customerStore,
            resourceStore,
        };
    },
    data() {
        return {
            tableHeaders: [
                "Initiator Business Name",
                "Receiver Business Name",
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
        fetchTransactions() {},
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
                            v-model="search"
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
            <Outgoing
                :transactionStore="transactionStore"
                :tableHeaders="tableHeaders"
            />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Add any specific styles here */
</style>
