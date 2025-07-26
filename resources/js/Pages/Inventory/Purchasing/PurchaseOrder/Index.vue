<script>
import Modal from "@/Components/Modal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { usePurchaseOrderStore } from "@/Store/PurchaseOrderStore";
import { useSupplierStore } from "@/Store/SupplierStore";
import { Head } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import AlertNotification from "@/Components/AlertNotification.vue";
import ModularDataTable from "@/Components/ModularDataTable.vue";
import CreatePurchaseOrder from "./Create.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Modal,
        CreatePurchaseOrder,
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

        const purchaseOrderStore = usePurchaseOrderStore();
        const supplierStore = useSupplierStore();
        
        // Fetch initial purchase orders data
        purchaseOrderStore.fetchOrders(params.value);

        const deletePurchaseOrder = async (id) => {
            await purchaseOrderStore.deletePurchaseOrder(id);
            if (purchaseOrderStore.success) {
                closeModal();
            }
        };

        const modal = ref({
            open: false,
            component: "",
        });

        const openPurchaseOrderForm = (component) => {
            modal.value.open = true;
            modal.value.component = component;
        };

        const closeModal = () => {
            modal.value.open = false;
        };

        const paginatePurchaseOrders = (page) => {
            params.value.page = page.page + 1;
            purchaseOrderStore.fetchOrders(params.value);
        };

        const onRowChange = (rows) => {
            params.value.rows = rows;
        };
        
        const handlePurchaseOrderSuccess = (action, result) => {
            console.log(`Purchase order ${action} successfully:`, result);
            purchaseOrderStore.fetchOrders(params.value);
        };
        
        // Method to fetch suppliers using store
        const fetchSuppliers = async () => {
            return await supplierStore.fetchSuppliers({
                business_id: localStorage.getItem('business_id'),
                rows: 100
            });
        };

        return {
            purchaseOrderStore,
            supplierStore,
            modal,
            openPurchaseOrderForm,
            closeModal,
            deletePurchaseOrder,
            params,
            paginatePurchaseOrders,
            onRowChange,
            handlePurchaseOrderSuccess,
            fetchSuppliers,
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

            // Table columns configuration 
            columns: [
                { 
                    header: "PO Number", 
                    field: "po_number", 
                    sortable: true 
                },
                { 
                    header: "Supplier", 
                    field: "supplier.name", 
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
                    header: "Expected Delivery", 
                    field: "expected_delivery", 
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
                    label: "New Purchase Order",
                    icon: "pi pi-plus",
                    severity: "secondary",
                    size: "small",
                    command: () => this.openPurchaseOrderForm("CreatePurchaseOrder"),
                },
            ],
            
            // Filters updated to use store methods
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
                        { label: "Received", value: "received" },
                        { label: "Cancelled", value: "cancelled" },
                    ],
                },
                {
                    label: "Supplier",
                    field: "supplier_id",
                    type: "dropdown",
                    options: [], // Will be populated dynamically
                    loadOptions: async () => {
                        try {
                            await this.fetchSuppliers();
                            
                            if (this.supplierStore.error) {
                                console.error('Error loading suppliers:', this.supplierStore.error);
                                return [{ label: "All Suppliers", value: "all" }];
                            }
                            
                            const suppliers = this.supplierStore.suppliers?.data || [];
                            
                            return [
                                { label: "All Suppliers", value: "all" },
                                ...suppliers.map(supplier => ({
                                    label: supplier.name,
                                    value: supplier.id
                                }))
                            ];
                        } catch (error) {
                            console.error('Error loading suppliers:', error);
                            return [{ label: "All Suppliers", value: "all" }];
                        }
                    },
                    loading: computed(() => this.supplierStore?.loading)
                },
            ],
            
            // Row actions
            rowActions: [
                {
                    label: "View",
                    icon: "pi pi-eye",
                    command: (data) => this.viewPurchaseOrder(data),
                },
                {
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: (data) => this.editPurchaseOrder(data),
                    visible: (data) => ["draft"].includes(data.status),
                },
                {
                    label: "Submit",
                    icon: "pi pi-send",
                    command: (data) => this.submitPurchaseOrder(data),
                    visible: (data) => data.status === "draft",
                },
                {
                    label: "Approve",
                    icon: "pi pi-check",
                    command: (data) => this.approvePurchaseOrder(data),
                    visible: (data) => data.status === "submitted",
                },
                {
                    label: "Receive",
                    icon: "pi pi-inbox",
                    command: (data) => this.createGoodsReceipt(data),
                    visible: (data) => data.status === "approved",
                },
                {
                    label: "Cancel",
                    icon: "pi pi-times",
                    command: (data) => this.cancelPurchaseOrder(data),
                    visible: (data) => ["draft", "submitted", "approved"].includes(data.status),
                },
                {
                    label: "Delete",
                    icon: "pi pi-trash",
                    command: (data) => this.openConfirm(
                        `Are you sure you want to delete this purchase order? This action cannot be undone.`,
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
            return this.purchaseOrderStore.orders?.data || [];
        },
        totalRecords() {
            return this.purchaseOrderStore.orders?.total || 0;
        },
        currentPage() {
            return this.purchaseOrderStore.orders?.current_page || 1;
        },
        rowsPerPage() {
            return this.params?.rows || 20;
        }
    },
    methods: {
        viewPurchaseOrder(data) {
            this.$inertia.visit(route('purchase-orders.show', { id: data.id }));
        },
        editPurchaseOrder(data) {
            this.openPurchaseOrderForm("EditPurchaseOrder");
            this.order_data = data;
        },
        submitPurchaseOrder(data) {
            this.openConfirm(
                `Are you sure you want to submit this purchase order?`,
                "Confirm Submission",
                data
            );
            this.confirmAction = () => this.purchaseOrderStore.submitPurchaseOrder(data.id);
        },
        approvePurchaseOrder(data) {
            this.openConfirm(
                `Are you sure you want to approve this purchase order?`,
                "Confirm Approval",
                data
            );
            this.confirmAction = () => this.purchaseOrderStore.approvePurchaseOrder(data.id);
        },
        cancelPurchaseOrder(data) {
            this.openConfirm(
                `Are you sure you want to cancel this purchase order? This action cannot be undone.`,
                "Confirm Cancellation",
                data
            );
            this.confirmAction = () => this.purchaseOrderStore.cancelPurchaseOrder(data.id);
        },
        createGoodsReceipt(data) {
            // Navigate to create goods receipt form with PO data
            this.$inertia.visit(route('goods-receipts.create', { purchase_order_id: data.id }));
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
                this.deletePurchaseOrder(this.order_to_delete.id);
            }
            this.closeConfirm();
        },
        handleSearch(query) {
            this.params.search = query;
            this.purchaseOrderStore.fetchOrders(this.params);
        },
        handleFilterChange(filters) {
            this.params = { ...this.params, ...filters };
            this.purchaseOrderStore.fetchOrders(this.params);
        },
        handlePageChange(event) {
            this.paginatePurchaseOrders(event);
        },
        handleRowsChange(rows) {
            this.onRowChange(rows);
        },
        handlePurchaseOrderSuccess(action, result) {
            this.$options.setup().handlePurchaseOrderSuccess(action, result);
        }
    },
    mounted() {
        this.fetchSuppliers();
    }
};
</script>

<template>
    <Head title="Purchase Orders" />
    <AuthenticatedLayout>
        <ConfirmationModal
            :isOpen="confirmBox.open"
            :message="confirmBox.message"
            :title="confirmBox.title"
            @confirm="handleConfirm"
            @close="closeConfirm"
        />

        <AlertNotification
            :open="purchaseOrderStore.error != null"
            :message="purchaseOrderStore.error ? purchaseOrderStore.error : ''"
            :status="purchaseOrderStore.error ? 'error' : 'success'"
        />

        <AlertNotification
            :open="purchaseOrderStore.success != null"
            :message="purchaseOrderStore.success ? purchaseOrderStore.success : ''"
            status="success"
        />

        <ModularDataTable
            :value="tableData"
            :loading="purchaseOrderStore.loading"
            dataKey="id"
            :columns="columns"
            :startActions="startActions"
            :rowActions="rowActions"
            :filters="filters"
            searchPlaceholder="Search purchase orders..."
            :rows="rowsPerPage"
            :totalRecords="totalRecords"
            :currentPage="currentPage"
            :rowsPerPageOptions="[10, 20, 50]"
            @page-change="handlePageChange"
            @rows-change="handleRowsChange"
            @search="handleSearch"
            @filter-change="handleFilterChange"
            emptyMessage="No purchase orders found"
            exportable
            stripedRows
            toolbarTitle="Purchase Orders"
        />

        <Modal :show="modal.open" @close="closeModal">
            <CreatePurchaseOrder
                v-if="modal.component === 'CreatePurchaseOrder'"
                @close="closeModal"
                @success="handlePurchaseOrderSuccess"
                newPO="true"
            />
            <CreatePurchaseOrder
                v-if="modal.component === 'EditPurchaseOrder'"
                @close="closeModal"
                @success="handlePurchaseOrderSuccess"
                newPO="false"
                :data="order_data"
            />
        </Modal>

    </AuthenticatedLayout>
</template>
