<template>
    <Head title="Warehouse Zones" />
    <AppLayout title="Warehouse Zones">
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
                emptyMessage="No warehouse zones found"
                stripedRows
                sortField="name"
                :sortOrder="1"
            />
        </div>

        <!-- Show alerts from zone store -->
        <AlertNotification
            :open="warehouseZoneStore.error != null"
            :message="warehouseZoneStore.error ? warehouseZoneStore.error : ''"
            :status="'error'"
        />

        <AlertNotification
            :open="warehouseZoneStore.success != null"
            :message="warehouseZoneStore.success ? warehouseZoneStore.success : ''"
            status="success"
        />
        <Modal :show="modal.open" @close="closeModal">
            <WarehouseZoneForm
                v-if="modal.component === 'WarehouseZoneForm'"
                :newZone="isNewZone"
                :data="selectedZone"
                :loading="loading"
                :warehouseId="warehouseId"
                @close="closeModal"
                @success="handleZoneSuccess"
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
                <span>Are you sure you want to delete this warehouse zone?</span>
                <p class="mt-2 text-sm text-red-600">This will delete all associated data.</p>
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
import { useWarehouseZoneStore } from "@/Store/WarehouseZone";
import { useWarehouseStore } from "@/Store/Warehouse";
import AppLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Modal from "@/Components/Modal.vue";
import ModularDataTable from "@/Components/ModularDataTable.vue";
import WarehouseZoneForm from "./Create.vue";
import AlertNotification from "@/Components/AlertNotification.vue";
// import { useRoute, useRouter } from "vue-router";
import { Head } from "@inertiajs/vue3";

export default {
    components: {
        AppLayout,
        Button,
        Dialog,
        Modal,
        ModularDataTable,
        WarehouseZoneForm,
        AlertNotification,
        Head
    },
    setup() {
        const warehouseZoneStore = useWarehouseZoneStore();
        const warehouseStore = useWarehouseStore();
        // const route = useRoute();
        // const router = useRouter();
        
        const zones = ref([]);
        const warehouses = ref([]);
        const selectedWarehouse = ref(null);
        const zoneTypes = ref([]);
        const loading = ref(false);
        const deleteDialog = ref(false);
        const selectedZone = ref(null);
        const isNewZone = ref(true);
        
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

        const loadZones = async () => {
            loading.value = true;
            try {
                await warehouseZoneStore.fetchZones({
                    page: params.value.page,
                    rows: params.value.rows,
                    search: params.value.search,
                    warehouse_id: params.value.warehouse_id
                });
                
                zones.value = warehouseZoneStore.zones.data || [];
            } catch (error) {
                console.error("Error loading zones:", error);
                warehouseZoneStore.error = "Failed to load warehouse zones";
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

        const openNewZone = () => {
            selectedZone.value = null;
            isNewZone.value = true;
            modal.value.component = "WarehouseZoneForm";
            modal.value.open = true;
            
            // Clear any previous messages
            warehouseZoneStore.clearErrors();
            warehouseZoneStore.clearSuccess();
        };

        const openEditZone = (zone) => {
            selectedZone.value = { ...zone };
            isNewZone.value = false;
            modal.value.component = "WarehouseZoneForm";
            modal.value.open = true;
            
            // Clear any previous messages
            warehouseZoneStore.clearErrors();
            warehouseZoneStore.clearSuccess();
        };

        const closeModal = () => {
            modal.value.open = false;
            selectedZone.value = null;
        };

        const openDeleteDialog = (zone) => {
            selectedZone.value = zone;
            deleteDialog.value = true;
            
            // Clear any previous messages
            warehouseZoneStore.clearErrors();
            warehouseZoneStore.clearSuccess();
        };

        const closeDeleteDialog = () => {
            deleteDialog.value = false;
            selectedZone.value = null;
        };

        const confirmDelete = async () => {
            if (!selectedZone.value) return;
            
            loading.value = true;
            try {
                const success = await warehouseZoneStore.deleteZone(selectedZone.value.id);
                
                if (success) {
                    warehouseZoneStore.success = "Warehouse zone deleted successfully";
                    closeDeleteDialog();
                    loadZones();
                }
            } catch (error) {
                console.error("Error deleting zone:", error);
                warehouseZoneStore.error = "Failed to delete warehouse zone";
            } finally {
                loading.value = false;
            }
        };

        const handleZoneSuccess = (action, zone) => {
            console.log(`Zone ${action} successfully:`, zone);
            loadZones(); // Refresh the zones list
        };

        const goBackToWarehouses = () => {
            router.push({ name: 'logistics.warehouses' });
        };

        const handlePageChange = (event) => {
            params.value.page = event.page + 1;
            loadZones();
        };

        const handleRowsChange = (rows) => {
            params.value.rows = rows;
            loadZones();
        };

        const handleSearch = (search) => {
            params.value.search = search;
            params.value.page = 1; // Reset to first page when searching
            loadZones();
        };

        onMounted(() => {
            // Clear any previous messages
            warehouseZoneStore.clearErrors();
            warehouseZoneStore.clearSuccess();
            warehouseStore.clearErrors();
            
            loadWarehouses();
            loadZones();
        });

        return {
            warehouseZoneStore,
            warehouseStore,
            zones,
            warehouses,
            selectedWarehouse,
            zoneTypes,
            loading,
            modal,
            deleteDialog,
            selectedZone,
            isNewZone,
            params,
            warehouseId,
            loadZones,
            loadWarehouses,
            openNewZone,
            openEditZone,
            closeModal,
            openDeleteDialog,
            closeDeleteDialog,
            confirmDelete,
            handleZoneSuccess,
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
                    header: "Zone Type",
                    field: "zone_type",
                    sortable: true,
                    template: (value) => {
                        const zoneTypeMap = {
                            'storage': { label: 'Storage', class: 'bg-blue-100 text-blue-800' },
                            'picking': { label: 'Picking', class: 'bg-green-100 text-green-800' },
                            'packing': { label: 'Packing', class: 'bg-teal-100 text-teal-800' },
                            'receiving': { label: 'Receiving', class: 'bg-yellow-100 text-yellow-800' },
                            'shipping': { label: 'Shipping', class: 'bg-indigo-100 text-indigo-800' },
                            'returns': { label: 'Returns', class: 'bg-purple-100 text-purple-800' },
                            'quarantine': { label: 'Quarantine', class: 'bg-red-100 text-red-800' }
                        };
                        
                        const typeInfo = zoneTypeMap[value] || { label: value, class: 'bg-gray-100 text-gray-800' };
                        
                        return `<span class="px-2 py-1 rounded-md text-xs font-medium ${typeInfo.class}">${typeInfo.label}</span>`;
                    }
                },
                {
                    header: "Temperature",
                    field: "temperature_controlled",
                    sortable: true,
                    template: (value, row) => {
                        if (!value) {
                            return `<span class="text-gray-500">No</span>`;
                        }
                        
                        const tempRange = `${row.min_temperature}° - ${row.max_temperature}° ${row.temperature_unit}`;
                        return `<span class="text-blue-600">${tempRange}</span>`;
                    }
                },
                {
                    header: "Status",
                    field: "status",
                    sortable: true,
                    template: (value) => {
                        const statusMap = {
                            'active': { label: 'Active', class: 'bg-green-100 text-green-800' },
                            'inactive': { label: 'Inactive', class: 'bg-red-100 text-red-800' },
                            'maintenance': { label: 'Maintenance', class: 'bg-orange-100 text-orange-800' }
                        };
                        
                        const statusInfo = statusMap[value] || { label: value, class: 'bg-gray-100 text-gray-800' };
                        
                        return `<span class="px-2 py-1 rounded-md text-xs font-medium ${statusInfo.class}">${statusInfo.label}</span>`;
                    }
                }
            ],

            // Start actions
            startActions: [
                {
                    label: "Add Zone",
                    icon: "pi pi-plus",
                    severity: "secondary",
                    size: "small",
                    command: () => this.openNewZone()
                },
                {
                    label: "Refresh",
                    icon: "pi pi-refresh",
                    severity: "secondary",
                    size: "small",
                    command: () => this.loadZones()
                },
            ],

            // Row actions
            rowActions: [
                {
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: (data) => this.openEditZone(data)
                },
                {
                    label: "Delete",
                    icon: "pi pi-trash",
                    command: (data) => this.openDeleteDialog(data)
                },
                {
                    label: "View Bin Locations",
                    icon: "pi pi-list",
                    command: (data) => this.$router.push(`/bin-locations?zone_id=${data.id}`)
                }
            ]
        };
    },
    computed: {
        tableData() {
            return this.zones || [];
        },
        totalRecords() {
            return this.warehouseZoneStore.zones?.total || 0;
        },
        currentPage() {
            return this.params.page;
        },
        rowsPerPage() {
            return this.params.rows;
        },
        searchPlaceholder() {
            return this.warehouseId ? 
                `Search zones in ${this.selectedWarehouse?.name || 'warehouse'}...` : 
                "Search warehouse zones...";
        }
    },
    methods: {
        loadZones() {
            this.$options.setup().loadZones();
        },
        openNewZone() {
            this.selectedZone = null;
            this.isNewZone = true;
            this.modal.component = "WarehouseZoneForm";
            this.modal.open = true;
            
            // Clear any previous messages
            this.warehouseZoneStore.clearErrors();
            this.warehouseZoneStore.clearSuccess();
        },
        openEditZone(data) {
            this.selectedZone = { ...data };
            this.isNewZone = false;
            this.modal.component = "WarehouseZoneForm";
            this.modal.open = true;
            
            // Clear any previous messages
            this.warehouseZoneStore.clearErrors();
            this.warehouseZoneStore.clearSuccess();
        },
        closeModal() {
            this.modal.open = false;
            this.selectedZone = null;
        },
        openDeleteDialog(data) {
            this.selectedZone = data;
            this.deleteDialog = true;
            
            // Clear any previous messages
            this.warehouseZoneStore.clearErrors();
            this.warehouseZoneStore.clearSuccess();
        },
        closeDeleteDialog() {
            this.deleteDialog = false;
            this.selectedZone = null;
        },
        confirmDelete() {
            this.$options.setup().confirmDelete();
        },
        handleZoneSuccess(action, zone) {
            this.loadZones();
        },
        goBackToWarehouses() {
            this.$router.push({ name: 'logistics.warehouses' });
        },
        handleRowAction({ action, row }) {
            if (action.label === "Edit") {
                this.openEditZone(row);
            } else if (action.label === "Delete") {
                this.openDeleteDialog(row);
            } else if (action.label === "View Bin Locations") {
                this.$router.push(`/bin-locations?zone_id=${row.id}`);
            }
        },
        handlePageChange(event) {
            this.params.page = event.page + 1;
            this.loadZones();
        },
        handleRowsChange(rows) {
            this.params.rows = rows;
            this.loadZones();
        },
        handleSearch(search) {
            this.params.search = search;
            this.params.page = 1;
            this.loadZones();
        }
    }
};
</script>