<template>
    <AppLayout title="Inventory Movements">
        <div class="">
            <ModularDataTable
                :value="tableData"
                :loading="loading"
                dataKey="id"
                :columns="columns"
                :startActions="startActions"
                :rowActions="rowActions"
                searchPlaceholder="Search movements..."
                :rows="rowsPerPage"
                :totalRecords="totalRecords"
                :currentPage="currentPage"
                :rowsPerPageOptions="[10, 20, 50]"
                @page-change="handlePageChange"
                @rows-change="handleRowsChange"
                @row-action="handleRowAction"
                emptyMessage="No movements found"
                stripedRows
                sortField="created_at"
                :sortOrder="-1"
            />
        </div>

        <Dialog
            v-model:visible="movementDetailsDialog"
            :style="{ width: '550px' }"
            header="Movement Details"
            :modal="true"
        >
            <div v-if="selectedMovement" class="p-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <Tag :severity="getMovementTypeSeverity(selectedMovement.movement_type)" :value="getMovementTypeLabel(selectedMovement.movement_type)" class="mb-2" />
                        <h3 class="text-xl font-semibold mb-2">{{ selectedMovement.inventory?.item?.name }}</h3>
                        <p class="text-gray-500 text-sm">{{ formatDate(selectedMovement.created_at, true) }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Quantity</p>
                        <p class="font-medium">{{ selectedMovement.quantity }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-500">Reason</p>
                        <p class="font-medium">{{ selectedMovement.reason?.name || 'N/A' }}</p>
                    </div>
                    
                    <div v-if="selectedMovement.movement_type === 'transfer'" class="col-span-2">
                        <p class="text-sm text-gray-500">From</p>
                        <p class="font-medium">
                            {{ selectedMovement.sourceInventory?.warehouse?.name || 'N/A' }}
                            {{ selectedMovement.sourceInventory?.binLocation?.name ? `(${selectedMovement.sourceInventory.binLocation.name})` : '' }}
                        </p>
                        <p class="text-sm text-gray-500 mt-2">To</p>
                        <p class="font-medium">
                            {{ selectedMovement.destinationInventory?.warehouse?.name || 'N/A' }}
                            {{ selectedMovement.destinationInventory?.binLocation?.name ? `(${selectedMovement.destinationInventory.binLocation.name})` : '' }}
                        </p>
                    </div>
                    <div v-else class="col-span-2">
                        <p class="text-sm text-gray-500">Location</p>
                        <p class="font-medium">
                            {{ selectedMovement.inventory?.warehouse?.name || 'N/A' }}
                            {{ selectedMovement.inventory?.binLocation?.name ? `(${selectedMovement.inventory.binLocation.name})` : '' }}
                        </p>
                    </div>
                    
                    <div class="col-span-2">
                        <p class="text-sm text-gray-500">Reference</p>
                        <p class="font-medium">{{ selectedMovement.reference || 'N/A' }}</p>
                    </div>
                    
                    <div class="col-span-2">
                        <p class="text-sm text-gray-500">Notes</p>
                        <p class="font-medium">{{ selectedMovement.notes || 'N/A' }}</p>
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Close" icon="pi pi-times" @click="closeMovementDetails" class="p-button-text" />
            </template>
        </Dialog>
    </AppLayout>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue';
import { useInventoryStore } from '@/Store/Inventory';
import AppLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import ModularDataTable from "@/Components/ModularDataTable.vue";

export default {
    components: {
        AppLayout,
        Button,
        Calendar,
        Dialog,
        Dropdown,
        InputText,
        Tag,
        ModularDataTable,
    },
    setup() {
        const inventoryStore = useInventoryStore();
        const movements = ref([]);
        const loading = ref(false);
        const movementDetailsDialog = ref(false);
        const selectedMovement = ref(null);
        const warehouseOptions = ref([]);
        const params = ref({
            page: 1,
            rows: 10,
        });

        const filters = ref({
            search: '',
            dateRange: null,
            movementType: null,
            warehouseId: null,
        });

        const getMovementTypeLabel = (type) => {
            switch (type) {
                case 'in': return 'IN';
                case 'out': return 'OUT';
                case 'transfer': return 'TRANSFER';
                case 'adjustment': return 'ADJUSTMENT';
                default: return type.toUpperCase();
            }
        };

        const getMovementTypeSeverity = (type) => {
            switch (type) {
                case 'in': return 'success';
                case 'out': return 'danger';
                case 'transfer': return 'info';
                case 'adjustment': return 'warning';
                default: return 'secondary';
            }
        };

        const formatDate = (dateString, includeTime = false) => {
            if (!dateString) return 'N/A';
            
            const date = new Date(dateString);
            const options = { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
            };
            
            if (includeTime) {
                options.hour = '2-digit';
                options.minute = '2-digit';
            }
            
            return date.toLocaleDateString('en-US', options);
        };

        const loadWarehouses = async () => {
            try {
                const businessId = localStorage.getItem('business_id');
                const response = await inventoryStore.getWarehouses(businessId);
                warehouseOptions.value = [
                    { id: null, name: 'All Warehouses' },
                    ...response.data
                ];
            } catch (error) {
                console.error('Error loading warehouses:', error);
            }
        };

        const formatDateForApi = (date) => {
            return date.toISOString().split('T')[0];
        };

        const loadMovements = async () => {
            loading.value = true;
            try {
                const businessId = localStorage.getItem('business_id');
                
                // Prepare filter parameters
                const params = { business_id: businessId };
                
                if (filters.value.search) {
                    params.search = filters.value.search;
                }
                
                if (filters.value.dateRange && filters.value.dateRange[0]) {
                    params.from_date = formatDateForApi(filters.value.dateRange[0]);
                    
                    if (filters.value.dateRange[1]) {
                        params.to_date = formatDateForApi(filters.value.dateRange[1]);
                    }
                }
                
                if (filters.value.movementType) {
                    params.movement_type = filters.value.movementType;
                }
                
                if (filters.value.warehouseId) {
                    params.warehouse_id = filters.value.warehouseId;
                }
                
                const response = await inventoryStore.getStockMovements(params);
                movements.value = response.data;
            } catch (error) {
                console.error('Error loading stock movements:', error);
            } finally {
                loading.value = false;
            }
        };

        const resetFilters = () => {
            filters.value = {
                search: '',
                dateRange: null,
                movementType: null,
                warehouseId: null,
            };
            loadMovements();
        };

        const viewMovementDetails = (movement) => {
            selectedMovement.value = movement;
            movementDetailsDialog.value = true;
        };

        const closeMovementDetails = () => {
            movementDetailsDialog.value = false;
            selectedMovement.value = null;
        };

        const handlePageChange = (event) => {
            params.value.page = event.page + 1;
            loadMovements();
        };

        const handleRowsChange = (rows) => {
            params.value.rows = rows;
            loadMovements();
        };

        // Watch for filter changes with debounce for search
        let searchTimeout;
        watch(() => filters.value.search, (newVal) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (newVal.length >= 3 || newVal.length === 0) {
                    loadMovements();
                }
            }, 500);
        });

        onMounted(() => {
            loadWarehouses();
            loadMovements();
        });

        return {
            inventoryStore,
            movements,
            loading,
            movementDetailsDialog,
            selectedMovement,
            warehouseOptions,
            filters,
            params,
            getMovementTypeLabel,
            getMovementTypeSeverity,
            formatDate,
            loadWarehouses,
            loadMovements,
            resetFilters,
            viewMovementDetails,
            closeMovementDetails,
            handlePageChange,
            handleRowsChange,
        };
    },
    data() {
        return {
            // Movement type options for filter dropdown
            movementTypeOptions: [
                { name: 'All Types', value: null },
                { name: 'In', value: 'in' },
                { name: 'Out', value: 'out' },
                { name: 'Transfer', value: 'transfer' },
                { name: 'Adjustment', value: 'adjustment' },
            ],
            
            // Table columns configuration
            columns: [
                {
                    header: "Date & Time",
                    field: "created_at",
                    sortable: true,
                    template: (value, row) => {
                        return this.formatDate(value);
                    },
                },
                {
                    header: "Type",
                    field: "movement_type",
                    sortable: true,
                    template: (value, row) => {
                        return `<Tag severity="${this.getMovementTypeSeverity(value)}" value="${this.getMovementTypeLabel(value)}" />`;
                    },
                },
                {
                    header: "Item",
                    field: "inventory.item.name",
                    sortable: true,
                    format: (value) => value || 'N/A',
                },
                {
                    header: "Quantity",
                    field: "quantity",
                    sortable: true,
                },
                {
                    header: "From / To",
                    field: "source_destination",
                    sortable: true,
                    template: (value, row) => {
                        if (row.movement_type === 'transfer') {
                            return `
                                <div class="text-sm">
                                    <span class="font-semibold">From:</span> 
                                    ${row.sourceInventory?.warehouse?.name || 'N/A'}
                                    ${row.sourceInventory?.binLocation?.name ? `(${row.sourceInventory.binLocation.name})` : ''}
                                </div>
                                <div class="text-sm">
                                    <span class="font-semibold">To:</span> 
                                    ${row.destinationInventory?.warehouse?.name || 'N/A'}
                                    ${row.destinationInventory?.binLocation?.name ? `(${row.destinationInventory.binLocation.name})` : ''}
                                </div>
                            `;
                        } else {
                            return `
                                ${row.inventory?.warehouse?.name || 'N/A'}
                                ${row.inventory?.binLocation?.name ? `(${row.inventory.binLocation.name})` : ''}
                            `;
                        }
                    },
                },
                {
                    header: "Reason",
                    field: "reason.name",
                    sortable: true,
                    format: (value) => value || 'N/A',
                },
                {
                    header: "Reference",
                    field: "reference",
                    sortable: true,
                    format: (value) => value || 'N/A',
                },
                {
                    header: "Notes",
                    field: "notes",
                    template: (value, row) => {
                        return `<div class="max-w-xs truncate">${value || 'N/A'}</div>`;
                    },
                },
            ],

            // Start actions (filter controls)
            startActions: [
                {
                    label: "Date Range",
                    icon: "pi pi-calendar",
                    component: "Calendar",
                    props: {
                        v_model: "filters.dateRange",
                        selectionMode: "range",
                        placeholder: "Date Range",
                        dateFormat: "yy-mm-dd",
                        class: "p-calendar-sm w-48",
                    },
                },
                {
                    label: "Type",
                    icon: "pi pi-tag",
                    component: "Dropdown",
                    props: {
                        v_model: "filters.movementType",
                        options: "movementTypeOptions",
                        optionLabel: "name",
                        optionValue: "value",
                        placeholder: "Movement Type",
                        class: "w-48",
                    },
                },
                {
                    label: "Warehouse",
                    icon: "pi pi-home",
                    component: "Dropdown",
                    props: {
                        v_model: "filters.warehouseId",
                        options: "warehouseOptions",
                        optionLabel: "name",
                        optionValue: "id",
                        placeholder: "Warehouse",
                        class: "w-48",
                    },
                },
                {
                    label: "Search",
                    icon: "pi pi-search",
                    component: "InputText",
                    props: {
                        v_model: "filters.search",
                        placeholder: "Search items, references...",
                        class: "p-inputtext-sm w-48",
                    },
                },
                {
                    label: "Apply",
                    icon: "pi pi-filter",
                    severity: "primary",
                    size: "small",
                    command: () => this.loadMovements(),
                },
                {
                    label: "Reset",
                    icon: "pi pi-refresh",
                    severity: "secondary",
                    size: "small",
                    outlined: true,
                    command: () => this.resetFilters(),
                },
            ],

            // Row actions
            rowActions: [
                {
                    label: "View Details",
                    icon: "pi pi-eye",
                    command: (data) => this.viewMovementDetails(data),
                },
            ],
        };
    },
    computed: {
        tableData() {
            return this.movements || [];
        },
        totalRecords() {
            return this.movements?.length || 0;
        },
        currentPage() {
            return this.params?.page || 1;
        },
        rowsPerPage() {
            return this.params?.rows || 10;
        },
    },
    methods: {
        loadMovements() {
            this.$options.setup().loadMovements();
        },
        resetFilters() {
            this.filters = {
                search: '',
                dateRange: null,
                movementType: null,
                warehouseId: null,
            };
            this.loadMovements();
        },
        viewMovementDetails(data) {
            this.selectedMovement = data;
            this.movementDetailsDialog = true;
        },
        closeMovementDetails() {
            this.movementDetailsDialog = false;
            this.selectedMovement = null;
        },
        formatDate(dateString, includeTime = false) {
            return this.$options.setup().formatDate(dateString, includeTime);
        },
        getMovementTypeLabel(type) {
            return this.$options.setup().getMovementTypeLabel(type);
        },
        getMovementTypeSeverity(type) {
            return this.$options.setup().getMovementTypeSeverity(type);
        },
        handleRowAction({ action, row }) {
            if (action.label === "View Details") {
                this.viewMovementDetails(row);
            }
        },
        handlePageChange(event) {
            this.params.page = event.page + 1;
            this.loadMovements();
        },
        handleRowsChange(rows) {
            this.params.rows = rows;
            this.loadMovements();
        },
    },
};
</script>
