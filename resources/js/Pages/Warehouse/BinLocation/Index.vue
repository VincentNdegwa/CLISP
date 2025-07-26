<template>
    <Head title="Bin Location" />
    <AppLayout title="Bin Locations">
        <div class="">
            <ModularDataTable
                :value="tableData"
                :loading="loading"
                dataKey="id"
                :columns="columns"
                :startActions="startActions"
                :rowActions="rowActions"
                :searchPlaceholder="searchPlaceholder"
                :rows="rowsPerPage"
                :totalRecords="totalRecords"
                :currentPage="currentPage"
                :rowsPerPageOptions="[10, 20, 50]"
                @page-change="handlePageChange"
                @rows-change="handleRowsChange"
                @row-action="handleRowAction"
                @search="handleSearch"
                emptyMessage="No bin locations found"
                stripedRows
                sortField="name"
                :sortOrder="1"
            />
        </div>

        <AlertNotification
            :open="binLocationStore.error != null"
            :message="binLocationStore.error ? binLocationStore.error : ''"
            :status="'error'"
        />

        <AlertNotification
            :open="binLocationStore.success != null"
            :message="binLocationStore.success ? binLocationStore.success : ''"
            status="success"
        />
        
        <AlertNotification
            :open="warehouseStore.error != null"
            :message="warehouseStore.error ? warehouseStore.error : ''"
            :status="'error'"
        />

        <Modal :show="modal.open" @close="closeModal">
            <BinLocationForm
                v-if="modal.component === 'BinLocationForm'"
                :newBinLocation="isNewBinLocation"
                :data="selectedBinLocation"
                :loading="loading"
                :warehouseId="warehouseId"
                @close="closeModal"
                @success="handleBinLocationSuccess"
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
                <span>Are you sure you want to delete this bin location?</span>
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
import { useBinLocationStore } from "@/Store/BinLocation";
import { useWarehouseStore } from "@/Store/Warehouse";
import AppLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Modal from "@/Components/Modal.vue";
import ModularDataTable from "@/Components/ModularDataTable.vue";
import BinLocationForm from "./Create.vue";
import AlertNotification from "@/Components/AlertNotification.vue";
import { useRoute, useRouter } from "vue-router";
import { Head } from "@inertiajs/vue3";

export default {
    components: {
        AppLayout,
        Button,
        Dialog,
        Modal,
        ModularDataTable,
        BinLocationForm,
        AlertNotification,
        Head
    },
    setup() {
        const binLocationStore = useBinLocationStore();
        const warehouseStore = useWarehouseStore();
        const route = useRoute();
        const router = useRouter();
        
        const binLocations = ref([]);
        const warehouses = ref([]);
        const selectedWarehouse = ref(null);
        const loading = ref(false);
        const deleteDialog = ref(false);
        const selectedBinLocation = ref(null);
        const isNewBinLocation = ref(true);
        
        // Get warehouse_id from query parameters if available
        const warehouseId = null;
        
        const params = ref({
            page: 1,
            rows: 10,
            search: "",
            warehouse_id: warehouseId
        });
        
        // Modal state
        const modal = ref({
            open: false,
            component: "",
        });

        const loadBinLocations = async () => {
            loading.value = true;
            try {
                await binLocationStore.fetchBinLocations({
                    page: params.value.page,
                    rows: params.value.rows,
                    search: params.value.search,
                    warehouse_id: params.value.warehouse_id
                });
                
                binLocations.value = binLocationStore.binLocations.data || [];
            } catch (error) {
                console.error("Error loading bin locations:", error);
                binLocationStore.error = "Failed to load bin locations";
            } finally {
                loading.value = false;
            }
        };

        const loadWarehouses = async () => {
            try {
                await warehouseStore.fetchWarehouses();
                
                warehouses.value = warehouseStore.warehouses.data || [];
                
                // If we have a warehouse_id in the query, find that warehouse
                if (warehouseId) {
                    selectedWarehouse.value = warehouses.value.find(w => w.id === warehouseId);
                }
            } catch (error) {
                console.error("Error loading warehouses:", error);
                warehouseStore.error = "Failed to load warehouses";
            }
        };

        const openNewBinLocation = () => {
            selectedBinLocation.value = null;
            isNewBinLocation.value = true;
            modal.value.component = "BinLocationForm";
            modal.value.open = true;
            
            // Clear any previous messages
            binLocationStore.clearErrors();
            binLocationStore.clearSuccess();
        };

        const openEditBinLocation = (binLocation) => {
            selectedBinLocation.value = { ...binLocation };
            isNewBinLocation.value = false;
            modal.value.component = "BinLocationForm";
            modal.value.open = true;
            
            // Clear any previous messages
            binLocationStore.clearErrors();
            binLocationStore.clearSuccess();
        };

        const closeModal = () => {
            modal.value.open = false;
            selectedBinLocation.value = null;
        };

        const openDeleteDialog = (binLocation) => {
            selectedBinLocation.value = binLocation;
            deleteDialog.value = true;
            
            // Clear any previous messages
            binLocationStore.clearErrors();
            binLocationStore.clearSuccess();
        };

        const closeDeleteDialog = () => {
            deleteDialog.value = false;
            selectedBinLocation.value = null;
        };

        const confirmDelete = async () => {
            if (!selectedBinLocation.value) return;
            
            loading.value = true;
            try {
                const success = await binLocationStore.deleteBinLocation(selectedBinLocation.value.id);
                
                if (success) {
                    binLocationStore.success = "Bin location deleted successfully";
                    closeDeleteDialog();
                    loadBinLocations();
                }
            } catch (error) {
                console.error("Error deleting bin location:", error);
                binLocationStore.error = "Failed to delete bin location";
            } finally {
                loading.value = false;
            }
        };

        const handleBinLocationSuccess = (action, binLocation) => {
            console.log(`Bin location ${action} successfully:`, binLocation);
            loadBinLocations();
        };

        const goBackToWarehouses = () => {
            router.push({ name: 'warehouse.warehouses' });
        };

        const handlePageChange = (event) => {
            params.value.page = event.page + 1;
            loadBinLocations();
        };

        const handleRowsChange = (rows) => {
            params.value.rows = rows;
            loadBinLocations();
        };

        const handleSearch = (search) => {
            params.value.search = search;
            params.value.page = 1; // Reset to first page when searching
            loadBinLocations();
        };

        onMounted(() => {
            // Clear any previous messages
            binLocationStore.clearErrors();
            binLocationStore.clearSuccess();
            warehouseStore.clearErrors();
            
            loadWarehouses();
            loadBinLocations();
        });

        return {
            binLocationStore,
            warehouseStore,
            binLocations,
            warehouses,
            selectedWarehouse,
            loading,
            modal,
            deleteDialog,
            selectedBinLocation,
            isNewBinLocation,
            params,
            warehouseId,
            loadBinLocations,
            loadWarehouses,
            openNewBinLocation,
            openEditBinLocation,
            closeModal,
            openDeleteDialog,
            closeDeleteDialog,
            confirmDelete,
            handleBinLocationSuccess,
            goBackToWarehouses,
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
                    header: "Code",
                    field: "code",
                    sortable: true
                },
                {
                    header: "Warehouse",
                    field: "warehouse.name",
                    sortable: true,
                },
                {
                    header: "Inventory Items",
                    field: "inventory_count",
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
                    label: "Bin Location",
                    icon: "pi pi-plus",
                    severity: "secondary",
                    size: "small",
                    command: () => this.openNewBinLocation()
                },
                {
                    label: "Refresh",
                    icon: "pi pi-refresh",
                    severity: "secondary",
                    size: "small",
                    command: () => this.loadBinLocations()
                }
            ],

            // Row actions
            rowActions: [
                {
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: (data) => this.openEditBinLocation(data)
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
            return this.binLocations || [];
        },
        totalRecords() {
            return this.binLocationStore.binLocations?.total || 0;
        },
        currentPage() {
            return this.params.page;
        },
        rowsPerPage() {
            return this.params.rows;
        },
        searchPlaceholder() {
            return this.warehouseId ? 
                `Search bin locations in ${this.selectedWarehouse?.name || 'warehouse'}...` : 
                "Search bin locations...";
        }
    },
    methods: {
        loadBinLocations() {
            this.$options.setup().loadBinLocations();
        },
        openNewBinLocation() {
            this.selectedBinLocation = null;
            this.isNewBinLocation = true;
            this.modal.component = "BinLocationForm";
            this.modal.open = true;
            
            // Clear any previous messages
            this.binLocationStore.clearErrors();
            this.binLocationStore.clearSuccess();
        },
        openEditBinLocation(data) {
            this.selectedBinLocation = { ...data };
            this.isNewBinLocation = false;
            this.modal.component = "BinLocationForm";
            this.modal.open = true;
            
            // Clear any previous messages
            this.binLocationStore.clearErrors();
            this.binLocationStore.clearSuccess();
        },
        closeModal() {
            this.modal.open = false;
            this.selectedBinLocation = null;
        },
        openDeleteDialog(data) {
            this.selectedBinLocation = data;
            this.deleteDialog = true;
            
            // Clear any previous messages
            this.binLocationStore.clearErrors();
            this.binLocationStore.clearSuccess();
        },
        closeDeleteDialog() {
            this.deleteDialog = false;
            this.selectedBinLocation = null;
        },
        confirmDelete() {
            this.$options.setup().confirmDelete();
        },
        handleBinLocationSuccess(action, binLocation) {
            this.loadBinLocations();
        },
        goBackToWarehouses() {
            this.$router.push({ name: 'logistics.warehouses' });
        },
        handleRowAction({ action, row }) {
            if (action.label === "Edit") {
                this.openEditBinLocation(row);
            } else if (action.label === "Delete") {
                this.openDeleteDialog(row);
            }
        },
        handlePageChange(event) {
            this.params.page = event.page + 1;
            this.loadBinLocations();
        },
        handleRowsChange(rows) {
            this.params.rows = rows;
            this.loadBinLocations();
        },
        handleSearch(search) {
            this.params.search = search;
            this.params.page = 1;
            this.loadBinLocations();
        }
    }
};
</script>