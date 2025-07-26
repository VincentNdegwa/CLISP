<template>
    <AppLayout title="Low Stock Items">
        <div class="">
            <ModularDataTable
                :value="tableData"
                :loading="loading"
                dataKey="id"
                :columns="columns"
                :startActions="startActions"
                :rowActions="rowActions"
                searchPlaceholder="Search low stock items..."
                :rows="rowsPerPage"
                :totalRecords="totalRecords"
                :currentPage="currentPage"
                :rowsPerPageOptions="[10, 20, 50]"
                @page-change="handlePageChange"
                @rows-change="handleRowsChange"
                @row-action="handleRowAction"
                emptyMessage="No low stock items found"
                stripedRows
                sortField="quantity_remaining"
                :sortOrder="1"
            />
        </div>

        <Dialog
            v-model:visible="adjustQuantityDialog"
            :style="{ width: '450px' }"
            header="Adjust Inventory Quantity"
            :modal="true"
            :closable="false"
        >
            <AdjustQuantity
                v-if="adjustQuantityDialog"
                :data="selectedInventory"
                :loading="loading"
                @close="closeAdjustQuantity"
                @updateInventory="onQuantityAdjusted"
            />
        </Dialog>
    </AppLayout>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import { useInventoryStore } from "@/Store/Inventory";
import AppLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import AdjustQuantity from "./AdjustQuantity.vue";
import ModularDataTable from "@/Components/ModularDataTable.vue";

export default {
    components: {
        AppLayout,
        Button,
        Dialog,
        AdjustQuantity,
        ModularDataTable,
    },
    setup() {
        const inventoryStore = useInventoryStore();
        const lowStockItems = ref([]);
        const loading = ref(false);
        const adjustQuantityDialog = ref(false);
        const selectedInventory = ref(null);
        const adjustmentType = ref("increase");
        const params = ref({
            page: 1,
            rows: 10,
        });

        // Calculate quantity remaining (quantity - reorder_point)
        const processLowStockItems = (items) => {
            return items.map((item) => ({
                ...item,
                quantity_remaining: item.quantity - item.reorder_point,
            }));
        };

        const loadLowStockItems = async () => {
            loading.value = true;
            try {
                const businessId = localStorage.getItem("business_id");
                const response = await inventoryStore.getLowStockItems(
                    businessId
                );
                lowStockItems.value = processLowStockItems(response.data);
            } catch (error) {
                console.error("Error loading low stock items:", error);
            } finally {
                loading.value = false;
            }
        };

        const openAdjustQuantity = (inventory, type) => {
            selectedInventory.value = inventory;
            adjustmentType.value = type;
            adjustQuantityDialog.value = true;
        };

        const closeAdjustQuantity = () => {
            adjustQuantityDialog.value = false;
            selectedInventory.value = null;
        };

        const onQuantityAdjusted = () => {
            closeAdjustQuantity();
            loadLowStockItems();
        };

        const createPurchaseOrder = (inventory) => {
            // This would navigate to purchase order creation with the item pre-selected
            // For now, just show a message
            alert(
                `Create purchase order for ${inventory.item.name} would go here`
            );
        };

        const handlePageChange = (event) => {
            params.value.page = event.page + 1;
            loadLowStockItems();
        };

        const handleRowsChange = (rows) => {
            params.value.rows = rows;
            loadLowStockItems();
        };

        onMounted(() => {
            loadLowStockItems();
        });

        return {
            inventoryStore,
            lowStockItems,
            loading,
            adjustQuantityDialog,
            selectedInventory,
            adjustmentType,
            openAdjustQuantity,
            closeAdjustQuantity,
            onQuantityAdjusted,
            createPurchaseOrder,
            params,
            handlePageChange,
            handleRowsChange,
        };
    },
    data() {
        return {
            // Table columns configuration
            columns: [
                {
                    header: "Item",
                    field: "item.name",
                    sortable: true,
                },
                {
                    header: "SKU",
                    field: "item.sku",
                    sortable: true,
                },
                {
                    header: "Warehouse",
                    field: "warehouse.name",
                    sortable: true,
                },
                {
                    header: "Bin Location",
                    field: "bin_location.name",
                    sortable: true,
                    format: (value) => value || "N/A",
                },
                {
                    header: "Quantity",
                    field: "quantity",
                    sortable: true,
                    template: (value, row) => {
                        const className =
                            row.quantity <= row.reorder_point
                                ? "text-red-500 font-bold"
                                : "";
                        return `<span class="${className}">${value}</span>`;
                    },
                },
                {
                    header: "Reorder Point",
                    field: "reorder_point",
                    sortable: true,
                },
                {
                    header: "Remaining",
                    field: "quantity_remaining",
                    sortable: true,
                    template: (value, row) => {
                        const className =
                            value < 0 ? "text-red-500 font-bold" : "";
                        return `<span class="${className}">${value}</span>`;
                    },
                },
            ],

            // Start actions
            startActions: [
                {
                    label: "Refresh",
                    icon: "pi pi-refresh",
                    severity: "secondary",
                    size: "small",
                    command: () => this.loadLowStockItems(),
                },
            ],

            // Row actions
            rowActions: [
                {
                    label: "Add Stock",
                    icon: "pi pi-plus",
                    command: (data) =>
                        this.openAdjustQuantity(data, "increase"),
                },
                {
                    label: "Create Purchase Order",
                    icon: "pi pi-shopping-cart",
                    command: (data) => this.createPurchaseOrder(data),
                },
            ],
        };
    },
    computed: {
        tableData() {
            return this.lowStockItems || [];
        },
        totalRecords() {
            return this.lowStockItems?.length || 0;
        },
        currentPage() {
            return this.params?.page || 1;
        },
        rowsPerPage() {
            return this.params?.rows || 10;
        },
    },
    methods: {
        loadLowStockItems() {
            this.$options.setup().loadLowStockItems();
        },
        openAdjustQuantity(data, type = "increase") {
            this.selectedInventory = data;
            this.adjustmentType = type;
            this.adjustQuantityDialog = true;
        },
        closeAdjustQuantity() {
            this.adjustQuantityDialog = false;
            this.selectedInventory = null;
        },
        onQuantityAdjusted() {
            this.closeAdjustQuantity();
            this.loadLowStockItems();
        },
        createPurchaseOrder(data) {
            alert(`Create purchase order for ${data.item.name} would go here`);
        },
        handleRowAction({ action, row }) {
            if (action.label === "Add Stock") {
                this.openAdjustQuantity(row, "increase");
            } else if (action.label === "Create Purchase Order") {
                this.createPurchaseOrder(row);
            }
        },
        handlePageChange(event) {
            this.params.page = event.page + 1;
            this.loadLowStockItems();
        },
        handleRowsChange(rows) {
            this.params.rows = rows;
            this.loadLowStockItems();
        },
    },
};
</script>
