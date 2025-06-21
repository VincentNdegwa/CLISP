<template>
    <Head title="Warehouses" />

    <AppLayout title="Warehouses">
        <div class="">
            <ModularDataTable
                :value="tableData"
                :loading="loading"
                dataKey="id"
                :columns="columns"
                :startActions="startActions"
                :rowActions="rowActions"
                searchPlaceholder="Search warehouses..."
                :rows="rowsPerPage"
                :totalRecords="totalRecords"
                :currentPage="currentPage"
                :rowsPerPageOptions="[10, 20, 50]"
                @page-change="handlePageChange"
                @rows-change="handleRowsChange"
                @row-action="handleRowAction"
                @search="handleSearch"
                emptyMessage="No warehouses found"
                stripedRows
                sortField="name"
                :sortOrder="1"
            />
        </div>

        <AlertNotification
            :open="warehouseStore.error != null"
            :message="warehouseStore.error ? warehouseStore.error : ''"
            :status="'error'"
        />

        <AlertNotification
            :open="warehouseStore.success != null"
            :message="warehouseStore.success ? warehouseStore.success : ''"
            status="success"
        />

        <Modal :show="modal.open" @close="closeModal">
            <WarehouseForm
                v-if="modal.component === 'WarehouseForm'"
                :newWarehouse="isNewWarehouse"
                :data="selectedWarehouse"
                :loading="loading"
                @close="closeModal"
                @success="handleWarehouseSuccess"
            />
        </Modal>

        <Dialog
            v-model:visible="deleteDialog"
            :style="{ width: '450px' }"
            header="Confirm Delete"
            :modal="true"
        >
            <div class="confirmation-content">
                <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                <span>Are you sure you want to delete this warehouse?</span>
            </div>
            <template #footer>
                <Button
                    label="No"
                    icon="pi pi-times"
                    class="p-button-text"
                    @click="closeDeleteDialog"
                />
                <Button
                    label="Yes"
                    icon="pi pi-check"
                    class="p-button-text"
                    @click="confirmDelete"
                    :loading="loading"
                />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import { useWarehouseStore } from "@/Store/Warehouse";
import AppLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Modal from "@/Components/Modal.vue";
import ModularDataTable from "@/Components/ModularDataTable.vue";
import WarehouseForm from "./Create.vue";
import AlertNotification from "@/Components/AlertNotification.vue";
import { Head } from "@inertiajs/vue3";

export default {
    components: {
        AppLayout,
        Button,
        Dialog,
        Modal,
        ModularDataTable,
        WarehouseForm,
        AlertNotification,
        Head
    },
    setup() {
        const warehouseStore = useWarehouseStore();
        const warehouses = ref([]);
        const loading = ref(false);
        const deleteDialog = ref(false);
        const selectedWarehouse = ref(null);
        const isNewWarehouse = ref(true);
        const params = ref({
            page: 1,
            rows: 10,
            search: ""
        });

        // Modal state
        const modal = ref({
            open: false,
            component: "",
        });

        const loadWarehouses = async () => {
            loading.value = true;
            try {
                await warehouseStore.fetchWarehouses({
                    page: params.value.page,
                    rows: params.value.rows,
                    search: params.value.search
                });

                warehouses.value = warehouseStore.warehouses.data || [];
            } catch (error) {
                console.error("Error loading warehouses:", error);
                warehouseStore.error = "Failed to load warehouses";
            } finally {
                loading.value = false;
            }
        };

        const openNewWarehouse = () => {
            selectedWarehouse.value = null;
            isNewWarehouse.value = true;
            modal.value.component = "WarehouseForm";
            modal.value.open = true;

            // Clear any previous messages
            warehouseStore.clearErrors();
            warehouseStore.clearSuccess();
        };

        const openEditWarehouse = (warehouse) => {
            selectedWarehouse.value = { ...warehouse };
            isNewWarehouse.value = false;
            modal.value.component = "WarehouseForm";
            modal.value.open = true;

            // Clear any previous messages
            warehouseStore.clearErrors();
            warehouseStore.clearSuccess();
        };

        const closeModal = () => {
            modal.value.open = false;
            selectedWarehouse.value = null;
        };

        const openDeleteDialog = (warehouse) => {
            selectedWarehouse.value = warehouse;
            deleteDialog.value = true;

            // Clear any previous messages
            warehouseStore.clearErrors();
            warehouseStore.clearSuccess();
        };

        const closeDeleteDialog = () => {
            deleteDialog.value = false;
            selectedWarehouse.value = null;
        };

        const confirmDelete = async () => {
            if (!selectedWarehouse.value) return;

            loading.value = true;
            try {
                const success = await warehouseStore.deleteWarehouse(selectedWarehouse.value.id);

                if (success) {
                    closeDeleteDialog();
                    loadWarehouses();
                }
            } catch (error) {
                console.error("Error deleting warehouse:", error);
                warehouseStore.error = "Failed to delete warehouse";
            } finally {
                loading.value = false;
            }
        };

        const handleWarehouseSuccess = (action, warehouse) => {
            console.log(`Warehouse ${action} successfully:`, warehouse);
            loadWarehouses();
        };

        const handlePageChange = (event) => {
            params.value.page = event.page + 1;
            loadWarehouses();
        };

        const handleRowsChange = (rows) => {
            params.value.rows = rows;
            loadWarehouses();
        };

        const handleSearch = (search) => {
            params.value.search = search;
            params.value.page = 1; // Reset to first page when searching
            loadWarehouses();
        };

        onMounted(() => {
            // Clear any previous messages
            warehouseStore.clearErrors();
            warehouseStore.clearSuccess();
            loadWarehouses();
        });

        return {
            warehouseStore,
            warehouses,
            loading,
            modal,
            deleteDialog,
            selectedWarehouse,
            isNewWarehouse,
            params,
            loadWarehouses,
            openNewWarehouse,
            openEditWarehouse,
            closeModal,
            openDeleteDialog,
            closeDeleteDialog,
            confirmDelete,
            handleWarehouseSuccess,
            handlePageChange,
            handleRowsChange,
            handleSearch
        };
    },
    data() {
        return {
            // Table columns configuration
            columns: [
                {
                    header: "Name",
                    field: "name",
                    sortable: true
                },
                {
                    header: 'Code',
                    field: 'code',
                    sortable: true,
                },
                {
                    header: "Address",
                    field: "address",
                    sortable: true
                },
                {
                    header: "City",
                    field: "city",
                    sortable: true
                },
                {
                    header: "State",
                    field: "state",
                    sortable: true
                },
                {
                    header: "Country",
                    field: "country",
                    sortable: true
                },
                {
                    header: "Status",
                    field: "is_active",
                    sortable: true,
                    template: (value, row) => {
                        const status = value ? "Active" : "Inactive";
                        const className = value
                            ? "bg-green-100 text-green-800"
                            : "bg-red-100 text-red-800";
                        return `<span class="px-2 py-1 rounded-md text-xs font-medium ${className}">${status}</span>`;
                    }
                }
            ],

            // Start actions
            startActions: [
                {
                    label: "Warehouse",
                    icon: "pi pi-plus",
                    severity: "secondary",
                    size: "small",
                    command: () => this.openNewWarehouse()
                },
                {
                    label: "Refresh",
                    icon: "pi pi-refresh",
                    severity: "secondary",
                    size: "small",
                    command: () => this.loadWarehouses()
                }
            ],

            // Row actions
            rowActions: [
                {
                    label: "View",
                    icon: "pi pi-eye",
                    command: (data) => this.$inertia.visit(route('warehouse.view', { id: data.id }))
                },
                {
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: (data) => this.openEditWarehouse(data)
                },
                {
                    label: "Delete",
                    icon: "pi pi-trash",
                    command: (data) => this.openDeleteDialog(data)
                }
            ]
        };
    },
    computed: {
        tableData() {
            return this.warehouses || [];
        },
        totalRecords() {
            return this.warehouseStore.warehouses?.total || 0;
        },
        currentPage() {
            return this.params.page;
        },
        rowsPerPage() {
            return this.params.rows;
        }
    },
    methods: {
        loadWarehouses() {
            this.$options.setup().loadWarehouses();
        },
        openNewWarehouse() {
            this.selectedWarehouse = null;
            this.isNewWarehouse = true;
            this.modal.component = "WarehouseForm";
            this.modal.open = true;

            // Clear any previous messages
            this.warehouseStore.clearErrors();
            this.warehouseStore.clearSuccess();
        },
        openEditWarehouse(data) {
            this.selectedWarehouse = { ...data };
            this.isNewWarehouse = false;
            this.modal.component = "WarehouseForm";
            this.modal.open = true;

            // Clear any previous messages
            this.warehouseStore.clearErrors();
            this.warehouseStore.clearSuccess();
        },
        closeModal() {
            this.modal.open = false;
            this.selectedWarehouse = null;
        },
        openDeleteDialog(data) {
            this.selectedWarehouse = data;
            this.deleteDialog = true;

            // Clear any previous messages
            this.warehouseStore.clearErrors();
            this.warehouseStore.clearSuccess();
        },
        closeDeleteDialog() {
            this.deleteDialog = false;
            this.selectedWarehouse = null;
        },
        confirmDelete() {
            this.$options.setup().confirmDelete();
        },
        handleWarehouseSuccess(action, warehouse) {
            this.loadWarehouses();
        },
        handleRowAction({ action, row }) {
            if (action.label === "Edit") {
                this.openEditWarehouse(row);
            } else if (action.label === "Delete") {
                this.openDeleteDialog(row);
            } else if (action.label === "View Bin Locations") {
                this.$router.push(`/bin-locations?warehouse_id=${row.id}`);
            }
        },
        handlePageChange(event) {
            this.params.page = event.page + 1;
            this.loadWarehouses();
        },
        handleRowsChange(rows) {
            this.params.rows = rows;
            this.loadWarehouses();
        },
        handleSearch(search) {
            this.params.search = search;
            this.params.page = 1;
            this.loadWarehouses();
        }
    }
};
</script>
