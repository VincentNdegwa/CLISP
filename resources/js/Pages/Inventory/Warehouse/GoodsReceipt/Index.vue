<script>
import Modal from "@/Components/Modal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useGoodsReceiptStore } from "@/Store/GoodsReceiptStore";
import { Head } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import AlertNotification from "@/Components/AlertNotification.vue";
import ModularDataTable from "@/Components/ModularDataTable.vue";
import CreateGoodsReceipt from "./Create.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Modal,
        CreateGoodsReceipt,
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

        const goodsReceiptStore = useGoodsReceiptStore();
        
        // Fetch initial goods receipts data
        goodsReceiptStore.fetchReceipts(params.value);

        const deleteGoodsReceipt = async (id) => {
            await goodsReceiptStore.deleteGoodsReceipt(id);
            if (goodsReceiptStore.success) {
                closeModal();
            }
        };

        const modal = ref({
            open: false,
            component: "",
        });

        const openGoodsReceiptForm = (component) => {
            modal.value.open = true;
            modal.value.component = component;
        };

        const closeModal = () => {
            modal.value.open = false;
        };

        const paginateGoodsReceipts = (page) => {
            params.value.page = page.page + 1;
            goodsReceiptStore.fetchReceipts(params.value);
        };

        const onRowChange = (rows) => {
            params.value.rows = rows;
        };
        
        const handleGoodsReceiptSuccess = (action, result) => {
            console.log(`Goods receipt ${action} successfully:`, result);
            goodsReceiptStore.fetchReceipts(params.value);
        };

        return {
            goodsReceiptStore,
            modal,
            openGoodsReceiptForm,
            closeModal,
            deleteGoodsReceipt,
            params,
            paginateGoodsReceipts,
            onRowChange,
            handleGoodsReceiptSuccess,
        };
    },
    data() {
        return {
            receipt_data: {},
            confirmBox: {
                open: false,
                message: "Are you sure you want to proceed?",
                title: "Confirm Action",
            },
            receipt_to_delete: {},
            selectedReceipt: null,
            confirmAction: null,

            // Table columns configuration 
            columns: [
                { 
                    header: "Receipt #", 
                    field: "receipt_number", 
                    sortable: true 
                },
                { 
                    header: "PO Number", 
                    field: "purchase_order.po_number", 
                    sortable: true 
                },
                { 
                    header: "Supplier", 
                    field: "supplier.name", 
                    sortable: true 
                },
                { 
                    header: "Receipt Date", 
                    field: "receipt_date", 
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
                    header: "Warehouse", 
                    field: "warehouse.name", 
                    sortable: true
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
                    label: "New Goods Receipt",
                    icon: "pi pi-plus",
                    severity: "secondary",
                    size: "small",
                    command: () => this.openGoodsReceiptForm("CreateGoodsReceipt"),
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
                        { label: "Pending", value: "pending" },
                        { label: "Completed", value: "completed" },
                        { label: "Cancelled", value: "cancelled" },
                    ],
                },
            ],
            
            // Row actions
            rowActions: [
                {
                    label: "View",
                    icon: "pi pi-eye",
                    command: (data) => this.viewGoodsReceipt(data),
                },
                {
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: (data) => this.editGoodsReceipt(data),
                    visible: (data) => ["draft"].includes(data.status),
                },
                {
                    label: "Complete",
                    icon: "pi pi-check",
                    command: (data) => this.completeGoodsReceipt(data),
                    visible: (data) => data.status === "pending",
                },
                {
                    label: "Cancel",
                    icon: "pi pi-times",
                    command: (data) => this.cancelGoodsReceipt(data),
                    visible: (data) => ["draft", "pending"].includes(data.status),
                },
                {
                    label: "Delete",
                    icon: "pi pi-trash",
                    command: (data) => this.openConfirm(
                        `Are you sure you want to delete this goods receipt? This action cannot be undone.`,
                        "Confirm Delete",
                        data
                    ),
                    visible: (data) => data.status === "draft",
                },
                {
                    label: "Print",
                    icon: "pi pi-print",
                    command: (data) => this.printGoodsReceipt(data),
                },
            ],
        };
    },
    computed: {
        tableData() {
            return this.goodsReceiptStore.receipts?.data || [];
        },
        totalRecords() {
            return this.goodsReceiptStore.receipts?.total || 0;
        },
        currentPage() {
            return this.goodsReceiptStore.receipts?.current_page || 1;
        },
        rowsPerPage() {
            return this.params?.rows || 20;
        }
    },
    methods: {
        viewGoodsReceipt(data) {
            this.$inertia.visit(route('goods-receipts.show', { id: data.id }));
        },
        editGoodsReceipt(data) {
            this.openGoodsReceiptForm("EditGoodsReceipt");
            this.receipt_data = data;
        },
        completeGoodsReceipt(data) {
            this.openConfirm(
                `Are you sure you want to mark this goods receipt as complete? This will update inventory levels.`,
                "Confirm Completion",
                data
            );
            this.confirmAction = () => this.goodsReceiptStore.completeGoodsReceipt(data.id);
        },
        cancelGoodsReceipt(data) {
            this.openConfirm(
                `Are you sure you want to cancel this goods receipt? This action cannot be undone.`,
                "Confirm Cancellation",
                data
            );
            this.confirmAction = () => this.goodsReceiptStore.cancelGoodsReceipt(data.id);
        },
        printGoodsReceipt(data) {
            // Logic to print receipt
            window.open(route('goods-receipts.print', { id: data.id }), '_blank');
        },
        openConfirm(message, title, data) {
            this.confirmBox.open = true;
            this.confirmBox.title = title;
            this.confirmBox.message = message;
            this.receipt_to_delete = data;
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
                this.deleteGoodsReceipt(this.receipt_to_delete.id);
            }
            this.closeConfirm();
        },
        handleSearch(query) {
            this.params.search = query;
            this.goodsReceiptStore.fetchReceipts(this.params);
        },
        handleFilterChange(filters) {
            this.params = { ...this.params, ...filters };
            this.goodsReceiptStore.fetchReceipts(this.params);
        },
        handlePageChange(event) {
            this.paginateGoodsReceipts(event);
        },
        handleRowsChange(rows) {
            this.onRowChange(rows);
        },
        handleGoodsReceiptSuccess(action, result) {
            this.$options.setup().handleGoodsReceiptSuccess(action, result);
        }
    },
    mounted() {
        // Any additional initialization
    }
};
</script>

<template>
    <Head title="Goods Receipts" />
    <AuthenticatedLayout>
        <ConfirmationModal
            :isOpen="confirmBox.open"
            :message="confirmBox.message"
            :title="confirmBox.title"
            @confirm="handleConfirm"
            @close="closeConfirm"
        />

        <AlertNotification
            :open="goodsReceiptStore.error != null"
            :message="goodsReceiptStore.error ? goodsReceiptStore.error : ''"
            :status="goodsReceiptStore.error ? 'error' : 'success'"
        />

        <AlertNotification
            :open="goodsReceiptStore.success != null"
            :message="goodsReceiptStore.success ? goodsReceiptStore.success : ''"
            status="success"
        />

        <ModularDataTable
            :value="tableData"
            :loading="goodsReceiptStore.loading"
            dataKey="id"
            :columns="columns"
            :startActions="startActions"
            :rowActions="rowActions"
            :filters="filters"
            searchPlaceholder="Search goods receipts..."
            :rows="rowsPerPage"
            :totalRecords="totalRecords"
            :currentPage="currentPage"
            :rowsPerPageOptions="[10, 20, 50]"
            @page-change="handlePageChange"
            @rows-change="handleRowsChange"
            @search="handleSearch"
            @filter-change="handleFilterChange"
            emptyMessage="No goods receipts found"
            exportable
            stripedRows
            toolbarTitle="Goods Receipts"
        />

        <Modal :show="modal.open" @close="closeModal">
            <CreateGoodsReceipt
                v-if="modal.component === 'CreateGoodsReceipt'"
                @close="closeModal"
                @success="handleGoodsReceiptSuccess"
                newReceipt="true"
            />
            <CreateGoodsReceipt
                v-if="modal.component === 'EditGoodsReceipt'"
                @close="closeModal"
                @success="handleGoodsReceiptSuccess"
                newReceipt="false"
                :data="receipt_data"
            />
        </Modal>

    </AuthenticatedLayout>
</template>
