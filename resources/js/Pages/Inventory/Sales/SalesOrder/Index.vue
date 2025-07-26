<script>
import Modal from "@/Components/Modal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useSalesOrderStore } from "@/Store/SalesOrderStore";
import { Head } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import AlertNotification from "@/Components/AlertNotification.vue";
import ModularDataTable from "@/Components/ModularDataTable.vue";
import CreateSalesOrder from "./Create.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Modal,
        CreateSalesOrder,
        TableSkeleton,
        ConfirmationModal,
        AlertNotification,
        ModularDataTable,
    },
    props: {
        statuses: {
            type: Array,
            default: () => [],
        },
    },
    setup() {
        const params = ref({
            page: 1,
            rows: 20,
        });

        const salesOrderStore = useSalesOrderStore();
        
        // Fetch initial sales orders data
        salesOrderStore.fetchOrders(params.value);

        const deleteSalesOrder = async (id) => {
            await salesOrderStore.deleteSalesOrder(id);
            if (salesOrderStore.success) {
                closeModal();
            }
        };

        const modal = ref({
            open: false,
            component: "",
        });

        const openSalesOrderForm = (component) => {
            modal.value.open = true;
            modal.value.component = component;
        };

        const closeModal = () => {
            modal.value.open = false;
        };

        const paginateSalesOrders = (page) => {
            params.value.page = page.page + 1;
            salesOrderStore.fetchOrders(params.value);
        };

        const onRowChange = (rows) => {
            params.value.rows = rows;
        };
        
        const handleSalesOrderSuccess = (action, result) => {
            console.log(`Sales order ${action} successfully:`, result);
            salesOrderStore.fetchOrders(params.value);
        };

        return {
            salesOrderStore,
            modal,
            openSalesOrderForm,
            closeModal,
            deleteSalesOrder,
            params,
            paginateSalesOrders,
            onRowChange,
            handleSalesOrderSuccess,
        };
    },
    data() {
        return {
            order_data: {},
            confirmBox: {
                open: false,
                message: "Are you sure you want to proceed?",
                title: "Confirm Action",
            },
            order_to_delete: {},
            selectedOrder: null,
            confirmAction: null,

            // Table columns configuration 
            columns: [
                { 
                    header: "SO Number", 
                    field: "so_number", 
                    sortable: true 
                },
                { 
                    header: "Customer", 
                    field: "customer.full_names", 
                    sortable: true 
                },
                { 
                    header: "Order Date", 
                    field: "order_date", 
                    sortable: true,
                    format: (value) => {
                        return new Date(value).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                    }
                },
                { 
                    header: "Requested Delivery", 
                    field: "requested_delivery", 
                    sortable: true,
                    format: (value) => {
                        return value ? new Date(value).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        }) : 'N/A';
                    }
                },
                { 
                    header: "Total", 
                    field: "total", 
                    sortable: true,
                    format: (value) => {
                        return new Intl.NumberFormat('en-US', {
                            style: 'currency',
                            currency: 'USD'
                        }).format(value);
                    } 
                },
                { 
                    header: "Status", 
                    field: "status", 
                    sortable: true,
                    template: (value, row) => {
                        const statusText = row.status_text;
                        const statusClass = row.status_class;
                        return `<span class="px-2 py-1 text-xs font-medium rounded-md ${statusClass}">${statusText}</span>`;
                    }
                },
            ],
            
            // Start actions 
            startActions: [
                {
                    label: "New Sales Order",
                    icon: "pi pi-plus",
                    severity: "secondary",
                    size: "small",
                    command: () => this.openSalesOrderForm("CreateSalesOrder"),
                },
            ],
            
            // Filters
            filters: [
                {
                    label: "Status",
                    field: "status",
                    type: "dropdown",
                    options: [
                        { label: "All Statuses", value: "all" },
                        { label: "Draft", value: "draft" },
                        { label: "Submitted", value: "submitted" },
                        { label: "Approved", value: "approved" },
                        { label: "Shipped", value: "shipped" },
                        { label: "Delivered", value: "delivered" },
                        { label: "Cancelled", value: "cancelled" },
                    ],
                },
            ],
            
            // Row actions
            rowActions: [
                {
                    label: "View",
                    icon: "pi pi-eye",
                    command: (data) => this.viewSalesOrder(data),
                },
                {
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: (data) => this.editSalesOrder(data),
                    visible: (data) => ["draft"].includes(data.status),
                },
                {
                    label: "Submit",
                    icon: "pi pi-send",
                    command: (data) => this.submitSalesOrder(data),
                    visible: (data) => data.status === "draft",
                },
                {
                    label: "Approve",
                    icon: "pi pi-check",
                    command: (data) => this.approveSalesOrder(data),
                    visible: (data) => data.status === "submitted",
                },
                {
                    label: "Ship",
                    icon: "pi pi-truck",
                    command: (data) => this.createShipment(data),
                    visible: (data) => data.status === "approved",
                },
                {
                    label: "Cancel",
                    icon: "pi pi-times",
                    command: (data) => this.cancelSalesOrder(data),
                    visible: (data) => ["draft", "submitted", "approved"].includes(data.status),
                },
                {
                    label: "Delete",
                    icon: "pi pi-trash",
                    command: (data) => this.openConfirm(
                        `Are you sure you want to delete this sales order? This action cannot be undone.`,
                        "Confirm Delete",
                        data
                    ),
                    visible: (data) => data.status === "draft",
                },
            ],
        };
    },
    computed: {
        tableData() {
            return this.salesOrderStore.orders?.data || [];
        },
        totalRecords() {
            return this.salesOrderStore.orders?.total || 0;
        },
        currentPage() {
            return this.salesOrderStore.orders?.current_page || 1;
        },
        rowsPerPage() {
            return this.params?.rows || 20;
        }
    },
    methods: {
        viewSalesOrder(data) {
            this.$inertia.visit(route('sales-orders.show', { id: data.id }));
        },
        editSalesOrder(data) {
            this.openSalesOrderForm("EditSalesOrder");
            this.order_data = data;
        },
        submitSalesOrder(data) {
            this.openConfirm(
                `Are you sure you want to submit this sales order?`,
                "Confirm Submission",
                data
            );
            this.confirmAction = () => this.salesOrderStore.submitSalesOrder(data.id);
        },
        approveSalesOrder(data) {
            this.openConfirm(
                `Are you sure you want to approve this sales order?`,
                "Confirm Approval",
                data
            );
            this.confirmAction = () => this.salesOrderStore.approveSalesOrder(data.id);
        },
        cancelSalesOrder(data) {
            this.openConfirm(
                `Are you sure you want to cancel this sales order? This action cannot be undone.`,
                "Confirm Cancellation",
                data
            );
            this.confirmAction = () => this.salesOrderStore.cancelSalesOrder(data.id);
        },
        createShipment(data) {
            // Navigate to create shipment form with SO data
            this.$inertia.visit(route('shipments.create', { sales_order_id: data.id }));
        },
        openConfirm(message, title, data) {
            this.confirmBox.open = true;
            this.confirmBox.title = title;
            this.confirmBox.message = message;
            this.order_to_delete = data;
        },
        closeConfirm() {
            this.confirmBox.open = false;
            this.confirmBox.message = "Are you sure you want to proceed?";
            this.confirmBox.title = "Confirm Action";
            this.confirmAction = null;
        },
        handleConfirm() {
            if (this.confirmAction) {
                this.confirmAction();
            } else {
                this.deleteSalesOrder(this.order_to_delete.id);
            }
            this.closeConfirm();
        },
        handleSearch(query) {
            this.params.search = query;
            this.salesOrderStore.fetchOrders(this.params);
        },
        handleFilterChange(filters) {
            this.params = { ...this.params, ...filters };
            this.salesOrderStore.fetchOrders(this.params);
        },
        handlePageChange(event) {
            this.paginateSalesOrders(event);
        },
        handleRowsChange(rows) {
            this.onRowChange(rows);
        },
        handleSalesOrderSuccess(action, result) {
            this.$options.setup().handleSalesOrderSuccess(action, result);
        }
    },
    mounted() {
        // Any additional initialization
    }
};
</script>

<template>
    <Head title="Sales Orders" />
    <AuthenticatedLayout>
        <ConfirmationModal
            :isOpen="confirmBox.open"
            :message="confirmBox.message"
            :title="confirmBox.title"
            @confirm="handleConfirm"
            @close="closeConfirm"
        />

        <AlertNotification
            :open="salesOrderStore.error != null"
            :message="salesOrderStore.error ? salesOrderStore.error : ''"
            :status="salesOrderStore.error ? 'error' : 'success'"
        />

        <AlertNotification
            :open="salesOrderStore.success != null"
            :message="salesOrderStore.success ? salesOrderStore.success : ''"
            status="success"
        />

        <ModularDataTable
            :value="tableData"
            :loading="salesOrderStore.loading"
            dataKey="id"
            :columns="columns"
            :startActions="startActions"
            :rowActions="rowActions"
            :filters="filters"
            searchPlaceholder="Search sales orders..."
            :rows="rowsPerPage"
            :totalRecords="totalRecords"
            :currentPage="currentPage"
            :rowsPerPageOptions="[10, 20, 50]"
            @page-change="handlePageChange"
            @rows-change="handleRowsChange"
            @search="handleSearch"
            @filter-change="handleFilterChange"
            emptyMessage="No sales orders found"
            exportable
            stripedRows
            toolbarTitle="Sales Orders"
        />

        <Modal :show="modal.open" @close="closeModal">
            <CreateSalesOrder
                v-if="modal.component === 'CreateSalesOrder'"
                @close="closeModal"
                @success="handleSalesOrderSuccess"
                newSO="true"
            />
            <CreateSalesOrder
                v-if="modal.component === 'EditSalesOrder'"
                @close="closeModal"
                @success="handleSalesOrderSuccess"
                newSO="false"
                :data="order_data"
            />
        </Modal>

    </AuthenticatedLayout>
</template>
