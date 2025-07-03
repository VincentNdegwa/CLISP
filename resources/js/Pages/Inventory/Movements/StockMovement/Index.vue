<script>
import Modal from "@/Components/Modal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useStockMovementStore } from "@/Store/StockMovementStore";
import { Head } from "@inertiajs/vue3";
import { ref, computed, onMounted } from "vue";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import AlertNotification from "@/Components/AlertNotification.vue";
import ModularDataTable from "@/Components/ModularDataTable.vue";
import CreateStockMovement from "./Create.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Modal,
        CreateStockMovement,
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
        reasons: {
            type: Array,
            default: () => [],
        },
    },
    setup() {
        const params = ref({
            page: 1,
            rows: 20,
        });

        const stockMovementStore = useStockMovementStore();
        
        // Fetch initial stock movements data
        stockMovementStore.fetchMovements(params.value);

        const deleteStockMovement = async (id) => {
            await stockMovementStore.deleteStockMovement(id);
            if (stockMovementStore.success) {
                closeModal();
            }
        };

        const modal = ref({
            open: false,
            component: "",
        });

        const openStockMovementForm = (component) => {
            modal.value.open = true;
            modal.value.component = component;
        };

        const closeModal = () => {
            modal.value.open = false;
        };

        const paginateStockMovements = (page) => {
            params.value.page = page.page + 1;
            stockMovementStore.fetchMovements(params.value);
        };

        const onRowChange = (rows) => {
            params.value.rows = rows;
        };
        
        const handleStockMovementSuccess = (action, result) => {
            console.log(`Stock movement ${action} successfully:`, result);
            stockMovementStore.fetchMovements(params.value);
        };

        return {
            stockMovementStore,
            modal,
            openStockMovementForm,
            closeModal,
            deleteStockMovement,
            params,
            paginateStockMovements,
            onRowChange,
            handleStockMovementSuccess,
        };
    },
    data() {
        return {
            movement_data: {},
            confirmBox: {
                open: false,
                message: "Are you sure you want to proceed?",
                title: "Confirm Action",
            },
            movement_to_delete: {},
            selectedMovement: null,

            // Table columns configuration 
            columns: [
                { 
                    header: "Reference", 
                    field: "reference_number", 
                    sortable: true 
                },
                { 
                    header: "Date", 
                    field: "movement_date", 
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
                    header: "From Location", 
                    field: "from_location", 
                    sortable: true,
                    body: (data) => {
                        if (data.from_warehouse && data.from_bin) {
                            return `${data.from_warehouse.name} / ${data.from_bin.name}`;
                        } else if (data.from_warehouse) {
                            return data.from_warehouse.name;
                        } else {
                            return 'N/A';
                        }
                    }
                },
                { 
                    header: "To Location", 
                    field: "to_location", 
                    sortable: true,
                    body: (data) => {
                        if (data.to_warehouse && data.to_bin) {
                            return `${data.to_warehouse.name} / ${data.to_bin.name}`;
                        } else if (data.to_warehouse) {
                            return data.to_warehouse.name;
                        } else {
                            return 'N/A';
                        }
                    }
                },
                { 
                    header: "Reason", 
                    field: "reason.name", 
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
                    label: "New Stock Movement",
                    icon: "pi pi-plus",
                    severity: "secondary",
                    size: "small",
                    command: () => this.openStockMovementForm("CreateStockMovement"),
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
                {
                    label: "Reason",
                    field: "reason_id",
                    type: "dropdown",
                    options: [
                        { label: "All Reasons", value: "all" },
                        ...(this.reasons || []).map(reason => ({
                            label: reason.name,
                            value: reason.id
                        }))
                    ],
                },
            ],
            
            // Row actions
            rowActions: [
                {
                    label: "View",
                    icon: "pi pi-eye",
                    command: (data) => this.viewStockMovement(data),
                },
                {
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: (data) => this.editStockMovement(data),
                    visible: (data) => ["draft"].includes(data.status),
                },
                {
                    label: "Complete",
                    icon: "pi pi-check",
                    command: (data) => this.completeStockMovement(data),
                    visible: (data) => data.status === "pending",
                },
                {
                    label: "Cancel",
                    icon: "pi pi-times",
                    command: (data) => this.cancelStockMovement(data),
                    visible: (data) => ["draft", "pending"].includes(data.status),
                },
                {
                    label: "Delete",
                    icon: "pi pi-trash",
                    command: (data) => this.openConfirm(
                        `Are you sure you want to delete this stock movement? This action cannot be undone.`,
                        "Confirm Delete",
                        data
                    ),
                    visible: (data) => data.status === "draft",
                },
                {
                    label: "Print",
                    icon: "pi pi-print",
                    command: (data) => this.printStockMovement(data),
                },
            ],
        };
    },
    computed: {
        tableData() {
            return this.stockMovementStore.movements?.data || [];
        },
        totalRecords() {
            return this.stockMovementStore.movements?.total || 0;
        },
        currentPage() {
            return this.stockMovementStore.movements?.current_page || 1;
        },
        rowsPerPage() {
            return this.params?.rows || 20;
        }
    },
    methods: {
        viewStockMovement(data) {
            this.$inertia.visit(route('stock-movements.show', { id: data.id }));
        },
        editStockMovement(data) {
            this.openStockMovementForm("EditStockMovement");
            this.movement_data = data;
        },
        completeStockMovement(data) {
            this.openConfirm(
                `Are you sure you want to complete this stock movement? This will update inventory levels.`,
                "Confirm Completion",
                data
            );
            this.confirmAction = () => this.stockMovementStore.completeStockMovement(data.id);
        },
        cancelStockMovement(data) {
            this.openConfirm(
                `Are you sure you want to cancel this stock movement? This action cannot be undone.`,
                "Confirm Cancellation",
                data
            );
            this.confirmAction = () => this.stockMovementStore.cancelStockMovement(data.id);
        },
        printStockMovement(data) {
            // Logic to print movement document
            window.open(route('stock-movements.print', { id: data.id }), '_blank');
        },
        openConfirm(message, title, data) {
            this.confirmBox.open = true;
            this.confirmBox.title = title;
            this.confirmBox.message = message;
            this.movement_to_delete = data;
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
                this.deleteStockMovement(this.movement_to_delete.id);
            }
            this.closeConfirm();
        },
        handleSearch(query) {
            this.params.search = query;
            this.stockMovementStore.fetchMovements(this.params);
        },
        handleFilterChange(filters) {
            this.params = { ...this.params, ...filters };
            this.stockMovementStore.fetchMovements(this.params);
        },
        handlePageChange(event) {
            this.paginateStockMovements(event);
        },
        handleRowsChange(rows) {
            this.onRowChange(rows);
        },
        handleStockMovementSuccess(action, result) {
            this.$options.setup().handleStockMovementSuccess(action, result);
        }
    },
    mounted() {
        // Any additional initialization
    }
};
</script>

<template>
    <Head title="Stock Movements" />
    <AuthenticatedLayout>
        <ConfirmationModal
            :isOpen="confirmBox.open"
            :message="confirmBox.message"
            :title="confirmBox.title"
            @confirm="handleConfirm"
            @close="closeConfirm"
        />

        <AlertNotification
            :open="stockMovementStore.error != null"
            :message="stockMovementStore.error ? stockMovementStore.error : ''"
            :status="stockMovementStore.error ? 'error' : 'success'"
        />

        <AlertNotification
            :open="stockMovementStore.success != null"
            :message="stockMovementStore.success ? stockMovementStore.success : ''"
            status="success"
        />

        <ModularDataTable
            :value="tableData"
            :loading="stockMovementStore.loading"
            dataKey="id"
            :columns="columns"
            :startActions="startActions"
            :rowActions="rowActions"
            :filters="filters"
            searchPlaceholder="Search stock movements..."
            :rows="rowsPerPage"
            :totalRecords="totalRecords"
            :currentPage="currentPage"
            :rowsPerPageOptions="[10, 20, 50]"
            @page-change="handlePageChange"
            @rows-change="handleRowsChange"
            @search="handleSearch"
            @filter-change="handleFilterChange"
            emptyMessage="No stock movements found"
            exportable
            stripedRows
            toolbarTitle="Stock Movements"
        />

        <Modal :show="modal.open" @close="closeModal">
            <CreateStockMovement
                v-if="modal.component === 'CreateStockMovement'"
                @close="closeModal"
                @success="handleStockMovementSuccess"
                :reasons="reasons"
                newMovement="true"
            />
            <CreateStockMovement
                v-if="modal.component === 'EditStockMovement'"
                @close="closeModal"
                @success="handleStockMovementSuccess"
                :reasons="reasons"
                newMovement="false"
                :data="movement_data"
            />
        </Modal>

    </AuthenticatedLayout>
</template>
