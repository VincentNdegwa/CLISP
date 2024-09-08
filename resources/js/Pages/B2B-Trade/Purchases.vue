<script>
import { ref, onMounted, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useTransactionStore } from "@/Store/TransactionStore";
import TableDisplay from "@/Layouts/TableDisplay.vue";
import IncommingPurchase from "./IncommingPurchase.vue";
import OutgoingPurchase from "./OutgoingPurchase.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        TextInput,
        TableDisplay,
        IncommingPurchase,
        OutgoingPurchase,
    },

    setup() {
        const transactionStore = useTransactionStore();
        const search = ref("");
        const filter = ref("all");
        const isDropdownOpen = ref(false);
        const incoming = ref(true);

        onMounted(() => {
            transactionStore.getTransaction({
                type: "purchase",
                incoming: incoming.value,
            });
        });

        const toggleDropdown = () => {
            isDropdownOpen.value = !isDropdownOpen.value;
        };

        const handleFilter = (filterValue) => {
            filter.value = filterValue;
            transactionStore.getTransaction({
                type: "purchase",
                incoming: incoming.value,
                filter: filterValue,
            });
            isDropdownOpen.value = false;
        };

        const openCreateNewPurchase = () => {};

        watch([search, filter, incoming], () => {
            transactionStore.getTransaction({
                type: "purchase",
                incoming: incoming.value,
                search: search.value,
                filter: filter.value,
            });
        });

        const openIncomming = () => {
            incoming.value = true;
        };
        const openOutgoing = () => {
            incoming.value = false;
        };

        return {
            search,
            filter,
            isDropdownOpen,
            toggleDropdown,
            handleFilter,
            openCreateNewPurchase,
            transactionStore,
            openIncomming,
            openOutgoing,
            incoming,
        };
    },
    data() {
        return {
            tableHeaders: ["#", "item", "qnty", "price", "status", "date"],
        };
    },
};
</script>

<template>
    <Head title="Purchases" />
    <AuthenticatedLayout>
        <div class="flex justify-between items-center mt-1">
            <div class="flex items-center md:gap-6 gap-2">
                <h1 class="text-slate-900 text-xl font-semibold">Purchases</h1>
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
                            class="dropdown-content flex flex-col gap-2 bg-white text-slate-900 rounded-box z-[1] w-52 p-2 shadow"
                        >
                            <li>
                                <a @click="handleFilter('all')">All</a>
                            </li>
                            <li>
                                <a @click="handleFilter('incoming')"
                                    >Incoming</a
                                >
                            </li>
                            <li>
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
                    @click="openCreateNewPurchase"
                    class="bg-slate-900 text-white"
                >
                    Create New Purchase
                </PrimaryButton>
            </div>
        </div>

        <p class="text-xs m-0">
            Outgoing Purchase (From Your Business to Another)
        </p>

        <p class="text-xs m-0">
            Incoming Purchase (From Another Business to Your Business)
        </p>

        <!-- Tabs -->
        <div class="flex border-b mt-2 mb-2">
            <button
                @click="openIncomming"
                :class="[
                    'py-2 px-4 rounded-t-lg transition-all ease-linear duration-150',
                    incoming
                        ? 'border-b-2 border-blue-600 bg-slate-700 text-white'
                        : '',
                ]"
            >
                Incomming Purchases
            </button>
            <button
                @click="openOutgoing"
                :class="[
                    'py-2 px-4 rounded-t-lg transition-all ease-linear duration-150',
                    !incoming
                        ? 'border-b-2 border-blue-600 bg-slate-700 text-white'
                        : '',
                ]"
            >
                Outgoing Requests
            </button>
        </div>

        <div id="incomming" v-if="incoming">
            <IncommingPurchase
                :transactionStore="transactionStore"
                :tableHeaders="tableHeaders"
            />
        </div>
        <div id="outgoing" v-else>
            <OutgoingPurchase
                :transactionStore="transactionStore"
                :tableHeaders="tableHeaders"
            />
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Add any specific styles here */
</style>
