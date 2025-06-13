<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useCustomerStore } from "@/Store/Customer";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import Modal from "@/Components/Modal.vue";
import NewCustomer from "./NewCustomer.vue";
import NoRecords from "@/Components/NoRecords.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import ModularDataTable from "@/Components/ModularDataTable.vue";

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
        NoRecords,
        ConfirmationModal,
        ModularDataTable,
    },
    data() {
        return {
            selectcustomer: {
                full_names: "",
                email: "",
                phone_number: "",
                address: "",
            },
            edit_customer: false,
            confirmBox: {
                open: false,
                message: "Are you sure you want to proceed?",
                title: "Confirm Action",
                method: null,
            },
            tableColumns: [
                {
                    header: "Full Names",
                    field: "full_names",
                    sortable: true,
                },
                {
                    header: "Email",
                    field: "email",
                    sortable: true,
                },
                {
                    header: "Phone",
                    field: "phone_number",
                    sortable: true,
                },
                {
                    header: "Address",
                    field: "address",
                    sortable: true,
                },
            ],
            tableRowActions: [
                {
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: (row) => this.openEditCustomerForm(row),
                },
                {
                    label: "Delete",
                    icon: "pi pi-trash",
                    command: (row) =>
                        this.openConfirm(
                            "Are you sure you want to delete this customer",
                            "Confirm Delete action",
                            () => this.handleDelete(row.id)
                        ),
                },
            ],
            tableFilters: [
                {
                    label: "Filter By",
                    field: "filter_type",
                    type: "dropdown",
                    options: [
                        { label: "All", value: "all" },
                        { label: "Email", value: "email" },
                        { label: "Phone", value: "phone" },
                    ],
                },
            ],
        };
    },
    setup() {
        const customerStore = useCustomerStore();
        const search = ref("");
        const filter = ref("");

        const componentModal = ref({
            open: false,
            component: "",
        });

        onMounted(() => {
            customerStore.fetchBusinessCustomers();
        });

        const handleCreate = () => {};

        const handleFilter = (filters) => {
            filter.value = filters?.filter_type || "all";
            customerStore.fetchBusinessCustomers(search.value, filter.value);
        };

        const handleEdit = (customer) => {};

        const handleDelete = (customerId) => {
            customerStore.deleteCustomer(customerId);
        };

        return {
            customerStore,
            componentModal,
            search,
            filter,
            handleCreate,
            handleFilter,
            handleEdit,
            handleDelete,
        };
    },
    methods: {
        openCreateNewCustomer() {
            this.componentModal.open = true;
            this.componentModal.component = "NewCustomer";
        },
        closeModal() {
            this.componentModal.open = false;
            this.componentModal.component = "";
            this.edit_customer = false;
            this.selectcustomer = {
                full_names: "",
                email: "",
                phone_number: "",
                address: "",
            };
        },
        openEditCustomerForm(customer) {
            this.componentModal.open = true;
            this.componentModal.component = "NewCustomer";
            this.edit_customer = true;
            this.selectcustomer = customer;
        },
        openConfirm(message, title, method) {
            this.confirmBox.open = true;
            this.confirmBox.title = title;
            this.confirmBox.message = message;
            this.confirmBox.method = method;
        },
        closeConfirm() {
            this.confirmBox.open = false;
            this.confirmBox.message = "Are you sure you want to proceed?";
            this.confirmBox.title = "Confirm Action";
            this.confirmBox.method = null;
        },
        handleSearch(query) {
            this.search = query;
            this.customerStore.fetchBusinessCustomers(query, this.filter);
        },
    },
};
</script>

<template>
    <Head title="Customers" />
    <Modal :show="componentModal.open" @close="closeModal">
        <NewCustomer
            v-if="componentModal.component == 'NewCustomer'"
            :customer="selectcustomer"
            :is-editing="edit_customer"
            @close="closeModal"
        />
    </Modal>
    <ConfirmationModal
        :isOpen="confirmBox.open"
        :message="confirmBox.message"
        :title="confirmBox.title"
        @confirm="confirmBox.method()"
        @close="closeConfirm"
    />
    <AuthenticatedLayout>
        <ModularDataTable
            :value="customerStore.customers"
            :loading="customerStore.loading"
            :columns="tableColumns"
            :rowActions="tableRowActions"
            :filters="tableFilters"
            :startActions="[
                {
                    label: 'Customer',
                    icon: 'pi pi-plus',
                    command: () => openCreateNewCustomer(),
                },
            ]"
            @search="handleSearch"
            @filter-change="handleFilter"
            dataKey="id"
            :rowHover="true"
            emptyMessage="No customers found"
            :showSearch="true"
            searchPlaceholder="Search by name or email"
        />

        <!-- Alert Notifications -->
        <AlertNotification
            :open="customerStore.success != null || customerStore.error != null"
            :message="
                customerStore.success != null
                    ? customerStore.success
                    : '' || customerStore.error != null
                    ? customerStore.error
                    : ''
            "
            :status="customerStore.success ? 'success' : 'error'"
        />
    </AuthenticatedLayout>
</template>
