<script>
import Modal from "@/Components/Modal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useInventoryStore } from "@/Store/Inventory";
import { Head } from "@inertiajs/vue3";
import NewInventory from "./Create.vue";
import { ref, computed, onMounted } from "vue";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import AlertNotification from "@/Components/AlertNotification.vue";
import ModularDataTable from "@/Components/ModularDataTable.vue";
import { useResourceStore } from "@/Store/Resource";
import { useWarehouseStore } from "@/Store/Warehouse";
import { useBinLocationStore } from "@/Store/BinLocation";
import AdjustQuantity from "./AdjustQuantity.vue";
import BatchManagement from "./BatchManagement.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Modal,
        NewInventory,
        TableSkeleton,
        ConfirmationModal,
        AlertNotification,
        ModularDataTable,
        AdjustQuantity,
        BatchManagement,
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

        const inventory = useInventoryStore();
        const resourceStore = useResourceStore();
        const warehouseStore = useWarehouseStore();
        const binLocationStore = useBinLocationStore();
        
        // Fetch initial inventory data
        inventory.fetchInventory(params.value);

        const inventoryDelete = async (id) => {
            await inventory.deleteInventoryItem(id);
            if (inventory.success) {
                closeModal();
            }
        };

        const modal = ref({
            open: false,
            component: "",
        });

        const openNewInventoryForm = (component) => {
            modal.value.open = true;
            modal.value.component = component;
        };

        const closeModal = () => {
            modal.value.open = false;
        };

        const paginateInventory = (page) => {
            params.value.page = page.page + 1;
            inventory.fetchInventory(params.value);
        };

        const onRowChange = (rows) => {
            params.value.rows = rows;
        };
        
        const handleInventorySuccess = (action, result) => {
            console.log(`Inventory ${action} successfully:`, result);
            inventory.fetchInventory(params.value);
        };
        
        // Methods to fetch warehouses and bin locations using stores
        const fetchWarehouses = async () => {
            return await warehouseStore.fetchWarehouses({
                business_id: localStorage.getItem('business_id'),
                rows: 100
            });
        };
        
        const fetchBinLocations = async () => {
            return await binLocationStore.fetchBinLocations({
                business_id: localStorage.getItem('business_id'),
                rows: 100
            });
        };

        return {
            inventory,
            resourceStore,
            warehouseStore,
            binLocationStore,
            modal,
            openNewInventoryForm,
            closeModal,
            inventoryDelete,
            params,
            paginateInventory,
            onRowChange,
            handleInventorySuccess,
            fetchWarehouses,
            fetchBinLocations,
        };
    },
    data() {
        return {
            inventory_data: {},
            confirmBox: {
                open: false,
                message: "Are you sure you want to proceed?",
                title: "Confirm Action",
            },
            inventory_to_delete: {},
            selectedInventory: null,

            // Table columns configuration 
            columns: [
                { 
                    header: "Item", 
                    field: "item.item_name", 
                    sortable: true 
                },
                { 
                    header: "Warehouse", 
                    field: "warehouse.name", 
                    sortable: true 
                },
                { 
                    header: "Bin Location", 
                    field: "bin_location.name", 
                    sortable: true 
                },
                { 
                    header: "Quantity", 
                    field: "quantity", 
                    sortable: true,
                    format: (value) => {
                        return parseFloat(value).toFixed(2);
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
                    label: "Inventory",
                    icon: "pi pi-plus",
                    severity: "secondary",
                    size: "small",
                    command: () => this.openNewInventoryForm("NewInventory"),
                },
                {
                    label: "Low Stock Report",
                    icon: "pi pi-exclamation-triangle",
                    severity: "secondary",
                    size: "small",
                    command: () => this.viewLowStockReport(),
                },
            ],
            
            // Filters updated to use store methods
            filters: [
                {
                    label: "Status",
                    field: "status",
                    type: "dropdown",
                    options: this.statuses,
                },
                {
                    label: "Warehouse",
                    field: "warehouse_id",
                    type: "dropdown",
                    options: [], // Will be populated dynamically
                    loadOptions: async () => {
                        try {
                            await this.fetchWarehouses();
                            
                            if (this.warehouseStore.error) {
                                console.error('Error loading warehouses:', this.warehouseStore.error);
                                return [{ label: "All Warehouses", value: null }];
                            }
                            
                            const warehouses = this.warehouseStore.warehouses?.data || [];
                            
                            return [
                                { label: "All Warehouses", value: null },
                                ...warehouses.map(warehouse => ({
                                    label: warehouse.name,
                                    value: warehouse.id
                                }))
                            ];
                        } catch (error) {
                            console.error('Error loading warehouses:', error);
                            return [{ label: "All Warehouses", value: null }];
                        }
                    },
                    loading: computed(() => this.warehouseStore?.loading)
                },
                {
                    label: "Bin Location",
                    field: "bin_location_id",
                    type: "dropdown",
                    options: [], // Will be populated dynamically
                    loadOptions: async () => {
                        try {
                            await this.fetchBinLocations();
                            
                            if (this.binLocationStore.error) {
                                console.error('Error loading bin locations:', this.binLocationStore.error);
                                return [{ label: "All Locations", value: null }];
                            }
                            
                            const binLocations = this.binLocationStore.binLocations?.data || [];
                            
                            return [
                                { label: "All Locations", value: null },
                                ...binLocations.map(location => ({
                                    label: location.name,
                                    value: location.id
                                }))
                            ];
                        } catch (error) {
                            console.error('Error loading bin locations:', error);
                            return [{ label: "All Locations", value: null }];
                        }
                    },
                    loading: computed(() => this.binLocationStore?.loading)
                },
            ],
            
            // Row actions
            rowActions: [
                {
                    label: "View",
                    icon: "pi pi-eye",
                    command: (data) => this.viewInventory(data),
                },
                {
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: (data) => this.openEditInventory(data),
                },
                {
                    label: "Adjust Quantity",
                    icon: "pi pi-sliders-h",
                    command: (data) => this.openAdjustQuantity(data),
                },
                {
                    label: "Batch",
                    icon: "pi pi-arrows-h",
                    command: (data) => this.openBatch(data),
                },
                {
                    label: "Delete",
                    icon: "pi pi-trash",
                    command: (data) => this.openConfirm(
                        `Are you sure you want to delete this inventory record? This process cannot be undone`,
                        "Confirm Inventory Delete",
                        data
                    ),
                    visible: (data) => data.quantity === 0, // Only allow deletion if quantity is 0
                },
            ],
        };
    },
    computed: {
        tableData() {
            return this.inventory.items?.data || [];
        },
        totalRecords() {
            return this.inventory.items?.total || 0;
        },
        currentPage() {
            return this.inventory.items?.current_page || 1;
        },
        rowsPerPage() {
            return this.params?.rows || 20;
        }
    },
    methods: {
        viewInventory(data) {
            this.$inertia.visit(route('inventory.view', { id: data.id }));
        },
        openEditInventory(data) {
            this.openNewInventoryForm("UpdateInventory");
            this.inventory_data = data;
        },
        openAdjustQuantity(data) {
            this.openNewInventoryForm("AdjustQuantity");
            this.inventory_data = data;
        },
        openBatch(data) {
            this.openNewInventoryForm("Batch");
            this.inventory_data = data;
        },
        openMoveInventory(data) {
            this.openNewInventoryForm("MoveInventory");
            this.inventory_data = data;
        },
        viewLowStockReport() {
            this.$inertia.visit('/inventory/low-stock');
        },
        openConfirm(message, title, data) {
            this.confirmBox.open = true;
            this.confirmBox.title = title;
            this.confirmBox.message = message;
            this.inventory_to_delete = data;
        },
        closeConfirm() {
            this.confirmBox.open = false;
            this.confirmBox.message = "Are you sure you want to proceed?";
            this.confirmBox.title = "Confirm Action";
        },
        handleConfirm() {
            this.inventoryDelete(this.inventory_to_delete.id);
        },
        handleRowAction({ action, row }) {
            if (action.label === "View") {
                this.viewInventory(row);
            } else if (action.label === "Edit") {
                this.openEditInventory(row);
            } else if (action.label === "Adjust Quantity") {
                this.openAdjustQuantity(row);
            } else if (action.label === "Move") {
                this.openMoveInventory(row);
            } else if (action.label === "Delete") {
                this.openConfirm(
                    `Are you sure you want to delete this inventory record? This process cannot be undone`,
                    "Confirm Inventory Delete",
                    row
                );
            }
        },
        handleSearch(query) {
            this.params.search = query;
            this.inventory.fetchInventory(this.params);
        },
        handleFilterChange(filters) {
            this.params = { ...this.params, ...filters };
            this.inventory.fetchInventory(this.params);
        },
        handlePageChange(event) {
            this.paginateInventory(event);
        },
        handleRowsChange(rows) {
            this.onRowChange(rows);
        },
        handleInventorySuccess(action, result) {
            this.$options.setup().handleInventorySuccess(action, result);
        }
    },
    mounted() {
        this.fetchWarehouses();
        this.fetchBinLocations();
    }
};
</script>

<template>
    <Head title="Inventory" />
    <AuthenticatedLayout>
        <ConfirmationModal
            :isOpen="confirmBox.open"
            :message="confirmBox.message"
            :title="confirmBox.title"
            @confirm="handleConfirm"
            @close="closeConfirm"
        />

        <AlertNotification
            :open="inventory.error != null"
            :message="inventory.error ? inventory.error : ''"
            :status="inventory.error ? 'error' : 'success'"
        />

        <AlertNotification
            :open="inventory.success != null"
            :message="inventory.success ? inventory.success : ''"
            status="success"
        />


        <ModularDataTable
            :value="tableData"
            :loading="inventory.loading"
            dataKey="id"
            :columns="columns"
            :startActions="startActions"
            :rowActions="rowActions"
            :filters="filters"
            searchPlaceholder="Search inventory..."
            :rows="rowsPerPage"
            :totalRecords="totalRecords"
            :currentPage="currentPage"
            :rowsPerPageOptions="[10, 20, 50]"
            @page-change="handlePageChange"
            @rows-change="handleRowsChange"
            @row-action="handleRowAction"
            @search="handleSearch"
            @filter-change="handleFilterChange"
            emptyMessage="No inventory found"
            exportable
            stripedRows
            toolbarTitle="Inventory"
        />

        <Modal :show="modal.open" @close="closeModal">
            <NewInventory
                v-if="modal.component === 'NewInventory'"
                @close="closeModal"
                @success="handleInventorySuccess"
                :status="statuses"
                newInventory="true"
            />
            <NewInventory
                v-if="modal.component === 'UpdateInventory'"
                @close="closeModal"
                @success="handleInventorySuccess"
                :status="statuses"
                newInventory="false"
                :data="inventory_data"
            />
            <AdjustQuantity
                v-if="modal.component === 'AdjustQuantity'"
                @close="closeModal"
                @success="handleInventorySuccess"
                :data="inventory_data"
            />
            <BatchManagement
                v-if="modal.component === 'Batch'"
                @close="closeModal"
                @success="handleInventorySuccess"
                :data="inventory_data"
            />
        </Modal>

    </AuthenticatedLayout>
</template>