<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        TextInput,
    },
    data() {
        return {
            search: "",
            filter: "all",
            isDropdownOpen: false,
            purchases: [
                {
                    id: 1,
                    type: "incoming",
                    item: "Laptop",
                    quantity: 10,
                    price: 1500.0,
                    status: "pending",
                    date: "2024-09-10",
                },
                {
                    id: 2,
                    type: "outgoing",
                    item: "Office Chair",
                    quantity: 5,
                    price: 300.0,
                    status: "completed",
                    date: "2024-09-09",
                },
                // Add more dummy data here...
            ],
        };
    },
    computed: {
        filteredPurchases() {
            return this.purchases.filter((purchase) => {
                const matchesSearch = purchase.item
                    .toLowerCase()
                    .includes(this.search.toLowerCase());
                const matchesFilter =
                    this.filter === "all" || purchase.type === this.filter;
                return matchesSearch && matchesFilter;
            });
        },
    },
    methods: {
        toggleDropdown() {
            this.isDropdownOpen = !this.isDropdownOpen;
        },
        handleFilter() {
            this.isDropdownOpen = false;
        },
        openCreateNewPurchase() {
            // Logic for opening the form to create a new purchase
        },
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
                        <a
                            @click="
                                filter = 'all';
                                handleFilter();
                            "
                            >All</a
                        >
                    </li>
                    <li>
                        <a
                            @click="
                                filter = 'incoming';
                                handleFilter();
                            "
                            >Incoming</a
                        >
                    </li>
                    <li>
                        <a
                            @click="
                                filter = 'outgoing';
                                handleFilter();
                            "
                            >Outgoing</a
                        >
                    </li>
                </ul>
            </div>
        </div>

        <!-- Purchases Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th
                            class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-600"
                        >
                            Item
                        </th>
                        <th
                            class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-600"
                        >
                            Quantity
                        </th>
                        <th
                            class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-600"
                        >
                            Price
                        </th>
                        <th
                            class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-600"
                        >
                            Status
                        </th>
                        <th
                            class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 text-gray-600"
                        >
                            Date
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="purchase in filteredPurchases"
                        :key="purchase.id"
                    >
                        <td class="px-6 py-4 border-b border-gray-300">
                            {{ purchase.item }}
                        </td>
                        <td class="px-6 py-4 border-b border-gray-300">
                            {{ purchase.quantity }}
                        </td>
                        <td class="px-6 py-4 border-b border-gray-300">
                            ${{ purchase.price.toFixed(2) }}
                        </td>
                        <td class="px-6 py-4 border-b border-gray-300">
                            <span
                                :class="{
                                    'text-yellow-500':
                                        purchase.status === 'pending',
                                    'text-green-500':
                                        purchase.status === 'completed',
                                    'text-red-500':
                                        purchase.status === 'canceled',
                                }"
                            >
                                {{ purchase.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 border-b border-gray-300">
                            {{ purchase.date }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>

</style>
