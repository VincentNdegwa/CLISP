<script>
import Modal from "@/Components/Modal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useStockCountStore } from "@/Store/StockCountStore";
import { Head } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import AlertNotification from "@/Components/AlertNotification.vue";
import ModularDataTable from "@/Components/ModularDataTable.vue";
import CreateStockCount from "./Create.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Modal,
        CreateStockCount,
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

        const stockCountStore = useStockCountStore();
        
        // Fetch initial stock counts data
        stockCountStore.fetchStockCounts(params.value);

        const deleteStockCount = async (id) => {
            await stockCountStore.deleteStockCount(id);
            if (stockCountStore.success) {
                closeModal();
            }
        };

        const modal = ref({
            open: false,
            component: "",
        });

        const openStockCountForm = (component) => {
            modal.value.open = true;
            modal.value.component = component;
        };

        const closeModal = () => {
            modal.value.open = false;
        };

        const paginateStockCounts = (page) => {
            params.value.page = page.page + 1;
            stockCountStore.fetchStockCounts(params.value);
        };

        const onRowChange = (rows) => {
            params.value.rows = rows;
        };
        
        const handleStockCountSuccess = (action, result) => {
            console.log(`Stock count ${action} successfully:`, result);
            stockCountStore.fetchStockCounts(params.value);
        };

        return {
            stockCountStore,
            modal,
            openStockCountForm,
            closeModal,
            deleteStockCount,
            params,
            paginateStockCounts,
            onRowChange,
            handleStockCountSuccess,
        };
    },
    data() {
        return {
            count_data: {},
            confirmBox: {
                open: false,
                message: "Are you sure you want to proceed?",
                title: "Confirm Action",
            },
            count_to_delete: {},
            selectedCount: null,
            confirmAction: null,

            // Table columns configuration 
            columns: [
                { 
                    header: "Reference", 
                    field: "reference_number", 
                    sortable: true 
                },
                { 
                    header: "Count Date", 
                    field: "count_date", 
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
                    header: "Bin Location", 
                    field: "bin_location.name", 
                    sortable: true,
                    body: (data) => {
                        return data.bin_location ? data.bin_location.name : 'All Locations';
                    }
                },
                { 
                    header: "Counted By", 
                    field: "counted_by.name", 
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
                    label: "New Stock Count",
                    icon: "pi pi-plus",
                    severity: "secondary",
                    size: "small",
                    command: () => this.openStockCountForm("CreateStockCount"),
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
                        { label: "In Progress", value: "in_progress" },
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
                    command: (data) => this.viewStockCount(data),
                },
                {
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: (data) => this.editStockCount(data),
                    visible: (data) => ["draft", "in_progress"].includes(data.status),
                },
                {
                    label: "Count",
                    icon: "pi pi-list",
                    command: (data) => this.recordCount(data),
                    visible: (data) => data.status === "in_progress",
                },
                {
                    label: "Complete",
                    icon: "pi pi-check",
                    command: (data) => this.completeStockCount(data),
                    visible: (data) => data.status === "in_progress",
                },
                {
                    label: "Cancel",
                    icon: "pi pi-times",
                    command: (data) => this.cancelStockCount(data),
                    visible: (data) => ["draft", "in_progress"].includes(data.status),
                },
                {
                    label: "Delete",
                    icon: "pi pi-trash",
                    command: (data) => this.openConfirm(
                        `Are you sure you want to delete this stock count? This action cannot be undone.`,
                        "Confirm Delete",
                        data
                    ),
                    visible: (data) => data.status === "draft",
                },
                {
                    label: "Print",
                    icon: "pi pi-print",
                    command: (data) => this.printStockCount(data),
                },
            ],
        };
    },
    computed: {
        tableData() {
            return this.stockCountStore.stockCounts?.data || [];
        },
        totalRecords() {
            return this.stockCountStore.stockCounts?.total || 0;
        },
        currentPage() {
            return this.stockCountStore.stockCounts?.current_page || 1;
        },
        rowsPerPage() {
            return this.params?.rows || 20;
        }
    },
    methods: {
        viewStockCount(data) {
            this.$inertia.visit(route('stock-counts.show', { id: data.id }));
        },
        editStockCount(data) {
            this.openStockCountForm("EditStockCount");
            this.count_data = data;
        },
        recordCount(data) {
            this.$inertia.visit(route('stock-counts.record', { id: data.id }));
        },
        completeStockCount(data) {
            this.openConfirm(
                `Are you sure you want to complete this stock count? This will update inventory levels if there are any discrepancies.`,
                "Confirm Completion",
                data
            );
            this.confirmAction = () => this.stockCountStore.completeStockCount(data.id);
        },
        cancelStockCount(data) {
            this.openConfirm(
                `Are you sure you want to cancel this stock count? This action cannot be undone.`,
                "Confirm Cancellation",
                data
            );
            this.confirmAction = () => this.stockCountStore.cancelStockCount(data.id);
        },
        printStockCount(data) {
            // Logic to print count sheet
            window.open(route('stock-counts.print', { id: data.id }), '_blank');
        },
        openConfirm(message, title, data) {
            this.confirmBox.open = true;
            this.confirmBox.title = title;
            this.confirmBox.message = message;
            this.count_to_delete = data;
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
                this.deleteStockCount(this.count_to_delete.id);
            }
            this.closeConfirm();
        },
        handleSearch(query) {
            this.params.search = query;
            this.stockCountStore.fetchStockCounts(this.params);
        },
        handleFilterChange(filters) {
            this.params = { ...this.params, ...filters };
            this.stockCountStore.fetchStockCounts(this.params);
        },
        handlePageChange(event) {
            this.paginateStockCounts(event);
        },
        handleRowsChange(rows) {
            this.onRowChange(rows);
        },
        handleStockCountSuccess(action, result) {
            this.$options.setup().handleStockCountSuccess(action, result);
        }
    },
    mounted() {
        // Any additional initialization
    }
};
</script>

<template>
    <Head title="Stock Counts" />
    <AuthenticatedLayout>
        <ConfirmationModal
            :isOpen="confirmBox.open"
            :message="confirmBox.message"
            :title="confirmBox.title"
            @confirm="handleConfirm"
            @close="closeConfirm"
        />

        <AlertNotification
            :open="stockCountStore.error != null"
            :message="stockCountStore.error ? stockCountStore.error : ''"
            :status="stockCountStore.error ? 'error' : 'success'"
        />

        <AlertNotification
            :open="stockCountStore.success != null"
            :message="stockCountStore.success ? stockCountStore.success : ''"
            status="success"
        />

        <ModularDataTable
            :value="tableData"
            :loading="stockCountStore.loading"
            dataKey="id"
            :columns="columns"
            :startActions="startActions"
            :rowActions="rowActions"
            :filters="filters"
            searchPlaceholder="Search stock counts..."
            :rows="rowsPerPage"
            :totalRecords="totalRecords"
            :currentPage="currentPage"
            :rowsPerPageOptions="[10, 20, 50]"
            @page-change="handlePageChange"
            @rows-change="handleRowsChange"
            @search="handleSearch"
            @filter-change="handleFilterChange"
            emptyMessage="No stock counts found"
            exportable
            stripedRows
            toolbarTitle="Stock Counts"
        />

        <Modal :show="modal.open" @close="closeModal">
            <CreateStockCount
                v-if="modal.component === 'CreateStockCount'"
                @close="closeModal"
                @success="handleStockCountSuccess"
                newCount="true"
            />
            <CreateStockCount
                v-if="modal.component === 'EditStockCount'"
                @close="closeModal"
                @success="handleStockCountSuccess"
                newCount="false"
                :data="count_data"
            />
        </Modal>

    </AuthenticatedLayout>
</template>
