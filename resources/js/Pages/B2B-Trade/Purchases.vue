<script>
import { ref, onMounted, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useTransactionStore } from "@/Store/TransactionStore";
import TableDisplay from "@/Layouts/TableDisplay.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        TextInput,
        TableDisplay,
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

        watch([search, filter], () => {
            transactionStore.getTransaction({
                type: "purchase",
                search: search.value,
                filter: filter.value,
            });
        });

        return {
            search,
            filter,
            isDropdownOpen,
            toggleDropdown,
            handleFilter,
            openCreateNewPurchase,
            transactionStore,
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
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-slate-900 text-xl font-semibold">Purchases</h1>
            <PrimaryButton
                @click="openCreateNewPurchase"
                class="bg-slate-900 text-white"
            >
                Create New Purchase
            </PrimaryButton>
        </div>

        <!-- Filter and Search -->
        <div class="flex items-center mb-4">
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
                        <a @click="handleFilter('incoming')">Incoming</a>
                    </li>
                    <li>
                        <a @click="handleFilter('outgoing')">Outgoing</a>
                    </li>
                </ul>
            </div>
        </div>

        <TableDisplay
            :loading="transactionStore.loading"
            :key="tableHeaders"
            :data_length="transactionStore?.transactions?.length"
        >
            <template v-slot:row>
                <tr
                    v-for="transaction in transactionStore.transactions"
                    :key="transaction.id"
                    class="hover:bg-gray-100 transition-colors"
                >
                    <td class="py-2 px-4 border-b">
                        {{ transaction.item }}
                    </td>
                    <td class="px-6 py-4 border-b">
                        {{ transaction.quantity }}
                    </td>
                    <td class="px-6 py-4 border-b">
                        ${{ transaction.price || 0 }}
                    </td>
                    <td class="px-6 py-4 border-b">
                        <span
                            :class="{
                                'text-yellow-500':
                                    transaction.status === 'pending',
                                'text-green-500':
                                    transaction.status === 'completed',
                                'text-red-500':
                                    transaction.status === 'canceled',
                            }"
                        >
                            {{ transaction.status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 border-b">
                        {{ transaction.date }}
                    </td>
                </tr>
            </template>
        </TableDisplay>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Add any specific styles here */
</style>
