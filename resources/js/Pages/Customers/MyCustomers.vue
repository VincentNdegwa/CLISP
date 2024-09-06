<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import AlertNotification from "@/Components/AlertNotification.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useCustomerStore } from "@/Store/Customer";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import Modal from "@/Components/Modal.vue";
import NewCustomer from "./NewCustomer.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        InputLabel,
        PrimaryButton,
        TextInput,
        TableSkeleton,
        Modal,
        NewCustomer,
    },
    data() {
        return {
            isDropdownOpen: false,
            componentModal: {
                open: false,
                component: "",
            },
        };
    },
    setup() {
        const customerStore = useCustomerStore();
        const search = ref("");
        const filter = ref("");

        onMounted(() => {
            customerStore.fetchBusinessCustomers();
        });

        const handleCreate = () => {};

        const handleFilter = () => {
            customerStore.fetchBusinessCustomers();
        };

        const handleEdit = (customer) => {};

        const handleDelete = (customerId) => {
            customerStore.deleteCustomer(customerId);
        };

        return {
            customerStore,
            search,
            filter,
            handleCreate,
            handleFilter,
            handleEdit,
            handleDelete,
        };
    },
    methods: {
        toggleDropdown() {
            this.isDropdownOpen = !this.isDropdownOpen;
        },
        openCreateNewCustomer() {
            this.componentModal.open = true;
            this.componentModal.component = "NewCustomer";
        },
        closeModal() {
            this.componentModal.open = false;
            this.componentModal.component = "";
        },
    },
};
</script>

<template>
    <Head title="Customers" />
    <Modal :show="componentModal.open" @close="closeModal">
        <NewCustomer v-if="componentModal.component == 'NewCustomer'" />
    </Modal>
    <AuthenticatedLayout>
        <div class="bg-white">
            <div class="flex justify-between items-center mb-1">
                <h1 class="text-slate-900 text-xl font-semibold">
                    My Customers
                </h1>
                <PrimaryButton
                    @click="openCreateNewCustomer"
                    class="bg-slate-900 text-white"
                >
                    Create New Customer
                </PrimaryButton>
            </div>

            <!-- Filter and Search -->
            <div class="flex items-center mb-4">
                <div class="mr-4">
                    <TextInput
                        id="search"
                        v-model="search"
                        class="block mt-1 w-full"
                        placeholder="Search by name or email"
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
                        <ul
                            tabindex="0"
                            class="dropdown-content menu p-2 shadow bg-white rounded-box w-52"
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
                                        filter = 'email';
                                        handleFilter();
                                    "
                                    >Email</a
                                >
                            </li>
                            <li>
                                <a
                                    @click="
                                        filter = 'phone';
                                        handleFilter();
                                    "
                                    >Phone</a
                                >
                            </li>
                        </ul>
                    </ul>
                </div>
            </div>

            <!-- Customer Table -->
            <div class="h-[74vh] overflow-scroll relative mt-1">
                <TableSkeleton v-if="customerStore.loading" />
                <table v-else class="table min-w-full bg-white relative">
                    <thead>
                        <tr>
                            <th class="bg-slate-900 text-white">Full Names</th>
                            <th class="bg-slate-900 text-white">Email</th>
                            <th class="bg-slate-900 text-white">Phone</th>
                            <th class="bg-slate-900 text-white">Address</th>
                            <th class="bg-slate-900 text-white">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="customer in customerStore.customers"
                            :key="customer.id"
                            class="hover:bg-gray-100 transition-colors"
                        >
                            <td class="py-2 px-4 border-b">
                                {{ customer.full_names }}
                            </td>
                            <td class="py-2 px-4 border-b">
                                {{ customer.email }}
                            </td>
                            <td class="py-2 px-4 border-b">
                                {{ customer.phone_number }}
                            </td>
                            <td class="py-2 px-4 border-b">
                                {{ customer.address }}
                            </td>
                            <td class="py-2 px-4 border-b">
                                <div class="dropdown dropdown-left">
                                    <div
                                        tabindex="0"
                                        class="btn btn-xs bg-green-500 hover:bg-green-400 text-white"
                                    >
                                        Action
                                    </div>
                                    <ul
                                        tabindex="0"
                                        class="dropdown-content menu bg-white rounded-box z-[1] w-52 p-2 shadow"
                                    >
                                        <li @click="handleEdit(customer)">
                                            <a>Edit</a>
                                        </li>
                                        <li @click="handleDelete(customer.id)">
                                            <a>Delete</a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Alert Notifications -->
            <AlertNotification
                :open="
                    customerStore.success != null || customerStore.error != null
                "
                :message="
                    customerStore.success != null
                        ? customerStore.success
                        : '' || customerStore.error != null
                        ? customerStore.error
                        : ''
                "
                :status="customerStore.success ? 'success' : 'error'"
            />
        </div>
    </AuthenticatedLayout>
</template>
