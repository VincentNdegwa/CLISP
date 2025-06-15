<script>
import { ref, onMounted, computed } from 'vue';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import axios from 'axios';

export default {
    components: {
        InputText,
        InputNumber,
        Dropdown,
        Calendar,
        Button,
        Textarea,
        DataTable,
        Column
    },
    props: {
        newShipment: {
            type: Boolean,
            default: true
        },
        data: {
            type: Object,
            default: null
        },
        loading: {
            type: Boolean,
            default: false
        }
    },
    emits: ['close', 'newShipment', 'updateShipment'],
    setup(props, { emit }) {
        const shipment = ref({
            business_id: localStorage.getItem('business_id'),
            sales_order_id: null,
            warehouse_id: null,
            carrier_id: null,
            shipment_date: new Date(),
            tracking_number: '',
            shipping_cost: 0,
            notes: '',
            items: []
        });

        const salesOrders = ref([]);
        const warehouses = ref([]);
        const carriers = ref([]);
        const selectedSalesOrder = ref(null);
        const salesOrderItems = ref([]);
        const selectedItems = ref([]);
        const binLocations = ref([]);
        const inventoryBatches = ref([]);

        // Populate data if editing
        onMounted(async () => {
            if (!props.newShipment && props.data) {
                shipment.value = {
                    id: props.data.id,
                    business_id: props.data.business_id,
                    sales_order_id: props.data.sales_order_id,
                    warehouse_id: props.data.warehouse_id,
                    carrier_id: props.data.carrier_id,
                    shipment_date: new Date(props.data.shipment_date),
                    tracking_number: props.data.tracking_number || '',
                    shipping_cost: props.data.shipping_cost || 0,
                    notes: props.data.notes || '',
                    status: props.data.status,
                    items: props.data.shipment_items?.map(item => ({
                        id: item.id,
                        sales_order_item_id: item.sales_order_item_id,
                        inventory_batch_id: item.inventory_batch_id,
                        bin_location_id: item.bin_location_id,
                        quantity: item.quantity,
                        notes: item.notes
                    })) || []
                };
                
                // Load related data
                await fetchSalesOrders();
                await fetchWarehouses();
                await fetchCarriers();
                
                if (shipment.value.sales_order_id) {
                    await fetchSalesOrderItems(shipment.value.sales_order_id);
                }
                
                if (shipment.value.warehouse_id) {
                    await fetchBinLocations(shipment.value.warehouse_id);
                }
            } else {
                // Load dropdowns for new shipment
                await fetchSalesOrders();
                await fetchWarehouses();
                await fetchCarriers();
            }
        });

        const fetchSalesOrders = async () => {
            try {
                const response = await axios.get('/api/sales-orders', {
                    params: {
                        business_id: shipment.value.business_id,
                        fulfillment_status: 0, // Unfulfilled or partially fulfilled
                        rows: 100
                    }
                });
                salesOrders.value = response.data.data || [];
            } catch (error) {
                console.error('Error fetching sales orders:', error);
            }
        };

        const fetchWarehouses = async () => {
            try {
                const response = await axios.get('/api/warehouses', {
                    params: {
                        business_id: shipment.value.business_id,
                        rows: 100
                    }
                });
                warehouses.value = response.data.data || [];
            } catch (error) {
                console.error('Error fetching warehouses:', error);
            }
        };

        const fetchCarriers = async () => {
            try {
                const response = await axios.get('/api/carriers', {
                    params: {
                        business_id: shipment.value.business_id,
                        rows: 100
                    }
                });
                carriers.value = response.data.data || [];
            } catch (error) {
                console.error('Error fetching carriers:', error);
            }
        };

        const fetchSalesOrderItems = async (salesOrderId) => {
            try {
                const response = await axios.get(`/api/sales-orders/${salesOrderId}/items`);
                salesOrderItems.value = response.data.filter(item => 
                    item.status === 0 || item.status === 1 // Pending or partial
                );
            } catch (error) {
                console.error('Error fetching sales order items:', error);
            }
        };

        const fetchBinLocations = async (warehouseId) => {
            try {
                const response = await axios.get('/api/bin-locations', {
                    params: {
                        warehouse_id: warehouseId,
                        rows: 100
                    }
                });
                binLocations.value = response.data.data || [];
            } catch (error) {
                console.error('Error fetching bin locations:', error);
            }
        };

        const fetchInventoryBatches = async (itemId, warehouseId) => {
            try {
                const response = await axios.get('/api/inventory-batches', {
                    params: {
                        item_id: itemId,
                        warehouse_id: warehouseId,
                        rows: 100
                    }
                });
                inventoryBatches.value = response.data.data || [];
            } catch (error) {
                console.error('Error fetching inventory batches:', error);
            }
        };

        const handleSalesOrderChange = async () => {
            if (shipment.value.sales_order_id) {
                await fetchSalesOrderItems(shipment.value.sales_order_id);
                shipment.value.items = []; // Clear items when sales order changes
            }
        };

        const handleWarehouseChange = async () => {
            if (shipment.value.warehouse_id) {
                await fetchBinLocations(shipment.value.warehouse_id);
            }
        };

        const addSelectedItems = () => {
            if (selectedItems.value.length > 0) {
                for (const item of selectedItems.value) {
                    // Check if item is already added
                    const existingItem = shipment.value.items.find(
                        i => i.sales_order_item_id === item.id
                    );
                    
                    if (!existingItem) {
                        shipment.value.items.push({
                            sales_order_item_id: item.id,
                            inventory_batch_id: null,
                            bin_location_id: null,
                            quantity: item.remaining_quantity || item.quantity,
                            notes: ''
                        });
                    }
                }
                selectedItems.value = [];
            }
        };

        const removeItem = (index) => {
            shipment.value.items.splice(index, 1);
        };

        const handleItemBatchChange = async (item) => {
            if (item.sales_order_item_id) {
                const salesOrderItem = salesOrderItems.value.find(
                    soi => soi.id === item.sales_order_item_id
                );
                
                if (salesOrderItem && shipment.value.warehouse_id) {
                    await fetchInventoryBatches(salesOrderItem.item_id, shipment.value.warehouse_id);
                }
            }
        };

        const saveShipment = () => {
            if (props.newShipment) {
                emit('newShipment', shipment.value);
            } else {
                emit('updateShipment', shipment.value);
            }
        };

        const isFormValid = computed(() => {
            return shipment.value.sales_order_id && 
                   shipment.value.warehouse_id && 
                   shipment.value.shipment_date &&
                   shipment.value.items.length > 0 &&
                   shipment.value.items.every(item => 
                       item.sales_order_item_id && 
                       item.bin_location_id && 
                       item.quantity > 0
                   );
        });

        const title = computed(() => {
            return props.newShipment ? 'Create New Shipment' : 'Edit Shipment';
        });

        return {
            shipment,
            salesOrders,
            warehouses,
            carriers,
            selectedSalesOrder,
            salesOrderItems,
            selectedItems,
            binLocations,
            inventoryBatches,
            handleSalesOrderChange,
            handleWarehouseChange,
            addSelectedItems,
            removeItem,
            handleItemBatchChange,
            saveShipment,
            isFormValid,
            title
        };
    }
};
</script>

<template>
    <div class="p-4">
        <h2 class="text-xl font-bold mb-4">{{ title }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Sales Order -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sales Order *</label>
                <Dropdown 
                    v-model="shipment.sales_order_id" 
                    :options="salesOrders" 
                    optionLabel="order_number" 
                    optionValue="id"
                    placeholder="Select Sales Order" 
                    class="w-full"
                    :disabled="!newShipment || loading"
                    @change="handleSalesOrderChange"
                />
            </div>

            <!-- Warehouse -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse *</label>
                <Dropdown 
                    v-model="shipment.warehouse_id" 
                    :options="warehouses" 
                    optionLabel="name" 
                    optionValue="id"
                    placeholder="Select Warehouse" 
                    class="w-full"
                    :disabled="!newShipment || loading"
                    @change="handleWarehouseChange"
                />
            </div>

            <!-- Carrier -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Carrier</label>
                <Dropdown 
                    v-model="shipment.carrier_id" 
                    :options="carriers" 
                    optionLabel="name" 
                    optionValue="id"
                    placeholder="Select Carrier" 
                    class="w-full"
                    :disabled="loading"
                />
            </div>

            <!-- Shipment Date -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Shipment Date *</label>
                <Calendar 
                    v-model="shipment.shipment_date" 
                    dateFormat="yy-mm-dd" 
                    class="w-full"
                    :disabled="loading"
                />
            </div>

            <!-- Tracking Number -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tracking Number</label>
                <InputText 
                    v-model="shipment.tracking_number" 
                    class="w-full"
                    :disabled="loading"
                />
            </div>

            <!-- Shipping Cost -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Cost</label>
                <InputNumber 
                    v-model="shipment.shipping_cost" 
                    mode="currency" 
                    currency="USD" 
                    locale="en-US"
                    class="w-full"
                    :disabled="loading"
                />
            </div>
        </div>

        <!-- Notes -->
        <div class="form-group mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <Textarea 
                v-model="shipment.notes" 
                rows="3" 
                class="w-full"
                :disabled="loading"
            />
        </div>

        <div class="border-t border-gray-200 pt-4 mb-4">
            <h3 class="text-lg font-medium mb-2">Shipment Items</h3>

            <!-- Item Selection -->
            <div v-if="newShipment && salesOrderItems.length > 0" class="mb-4">
                <div class="flex items-end gap-2 mb-2">
                    <div class="flex-grow">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Items to Ship</label>
                        <Dropdown 
                            v-model="selectedItems" 
                            :options="salesOrderItems" 
                            optionLabel="item.name"
                            dataKey="id"
                            placeholder="Select Items" 
                            class="w-full"
                            :disabled="loading"
                            multiple
                        >
                            <template #option="slotProps">
                                <div>
                                    {{ slotProps.option.item.name }} - 
                                    Qty: {{ slotProps.option.quantity }}
                                    {{ slotProps.option.remaining_quantity ? 
                                        `(${slotProps.option.remaining_quantity} remaining)` : '' }}
                                </div>
                            </template>
                        </Dropdown>
                    </div>
                    <Button 
                        label="Add" 
                        icon="pi pi-plus" 
                        @click="addSelectedItems"
                        :disabled="!selectedItems.length || loading"
                    />
                </div>
            </div>

            <!-- Items Table -->
            <DataTable :value="shipment.items" responsiveLayout="scroll" class="mb-4">
                <Column field="sales_order_item_id" header="Item">
                    <template #body="slotProps">
                        {{ salesOrderItems.find(item => item.id === slotProps.data.sales_order_item_id)?.item.name || 'Unknown Item' }}
                    </template>
                </Column>
                <Column field="quantity" header="Quantity">
                    <template #body="slotProps">
                        <InputNumber 
                            v-model="slotProps.data.quantity" 
                            :min="0.01" 
                            :step="1"
                            :disabled="loading"
                        />
                    </template>
                </Column>
                <Column field="bin_location_id" header="Bin Location">
                    <template #body="slotProps">
                        <Dropdown 
                            v-model="slotProps.data.bin_location_id" 
                            :options="binLocations" 
                            optionLabel="name" 
                            optionValue="id"
                            placeholder="Select Location" 
                            class="w-full"
                            :disabled="loading"
                        />
                    </template>
                </Column>
                <Column field="inventory_batch_id" header="Batch">
                    <template #body="slotProps">
                        <Dropdown 
                            v-model="slotProps.data.inventory_batch_id" 
                            :options="inventoryBatches" 
                            optionLabel="batch_number" 
                            optionValue="id"
                            placeholder="Select Batch" 
                            class="w-full"
                            :disabled="loading"
                            @focus="handleItemBatchChange(slotProps.data)"
                        />
                    </template>
                </Column>
                <Column header="Actions" style="width: 100px">
                    <template #body="slotProps">
                        <Button 
                            icon="pi pi-trash" 
                            severity="danger" 
                            text 
                            @click="removeItem(slotProps.index)"
                            :disabled="loading"
                        />
                    </template>
                </Column>
            </DataTable>

            <div v-if="shipment.items.length === 0" class="text-center py-4 bg-gray-50 rounded-md">
                <p class="text-gray-500">No items added to this shipment</p>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <Button 
                label="Cancel" 
                severity="secondary" 
                outlined 
                @click="$emit('close')"
                :disabled="loading"
            />
            <Button 
                label="Save" 
                icon="pi pi-save" 
                @click="saveShipment"
                :disabled="!isFormValid || loading"
                :loading="loading"
            />
        </div>
    </div>
</template>
