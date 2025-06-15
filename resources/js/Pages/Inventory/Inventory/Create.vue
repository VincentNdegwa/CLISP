<script setup>
import { ref, onMounted, computed } from 'vue';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import FormLayout from "@/Layouts/FormLayout.vue";
import { useResourceStore } from '@/Store/Resource';
import { useWarehouseStore } from '@/Store/Warehouse';
import { useBinLocationStore } from '@/Store/BinLocation';

const props = defineProps({
    newInventory: {
        type: Boolean,
        default: true
    },
    data: {
        type: Object,
        default: null
    }
});

const resourceStore = useResourceStore();
const warehouseStore = useWarehouseStore();
const binLocationStore = useBinLocationStore();

const emit = defineEmits(['close', 'newInventory', 'updateInventory']);

const inventory = ref({
    business_id: localStorage.getItem('business_id'),
    item_id: null,
    warehouse_id: null,
    bin_location_id: null,
    batch_number: '',
    quantity: 0,
    reorder_point: 0,
    expiry_date: null,
    cost_price: 0,
    notes: '',
    status: 0 // Default to In Stock
});

// Computed property to manage overall loading state
const loading = computed(() => {
    return resourceStore.loading || warehouseStore.loading || binLocationStore.loading;
});

const items = ref([]);
const warehouses = ref([]);
const binLocations = ref([]);
const errors = ref({});

// Populate data if editing
onMounted(async () => {
    if (!props.newInventory && props.data) {
        inventory.value = {
            id: props.data.id,
            business_id: props.data.business_id,
            item_id: props.data.item_id,
            warehouse_id: props.data.warehouse_id,
            bin_location_id: props.data.bin_location_id,
            batch_number: props.data.batch_number || '',
            quantity: props.data.quantity || 0,
            reorder_point: props.data.reorder_point || 0,
            expiry_date: props.data.expiry_date ? new Date(props.data.expiry_date) : null,
            notes: props.data.notes || '',
            status: props.data.status
        };
    }
    
    await fetchItems();
    await fetchWarehouses();
    
    if (inventory.value.warehouse_id) {
        await fetchBinLocations(inventory.value.warehouse_id);
    }
});

const fetchItems = async () => {
    try {
        await resourceStore.fetchResources({
            rows: 100
        });
        
        if (resourceStore.error) {
            errors.value.item_id = resourceStore.error;
            return;
        }
        items.value = resourceStore.items.data || [];
    } catch (error) {
        errors.value.item_id = 'Error fetching items';
        console.error('Error fetching items:', error);
    }
};

const fetchWarehouses = async () => {
    try {
        await warehouseStore.fetchWarehouses({
            rows: 100
        });
        
        if (warehouseStore.error) {
            errors.value.warehouse_id = warehouseStore.error;
            return;
        }
        warehouses.value = warehouseStore.warehouses.data || [];
    } catch (error) {
        errors.value.warehouse_id = 'Error fetching warehouses';
        console.error('Error fetching warehouses:', error);
    }
};

const fetchBinLocations = async (warehouseId) => {
    try {
        await binLocationStore.getBinLocationsByWarehouse(warehouseId, {
            rows: 100
        });
        
        if (binLocationStore.error) {
            errors.value.bin_location_id = binLocationStore.error;
            return;
        }
        binLocations.value = binLocationStore.binLocations.data || [];
    } catch (error) {
        errors.value.bin_location_id = 'Error fetching bin locations';
        console.error('Error fetching bin locations:', error);
    }
};

const handleWarehouseChange = async () => {
    inventory.value.bin_location_id = null; // Reset bin location when warehouse changes
    if (inventory.value.warehouse_id) {
        await fetchBinLocations(inventory.value.warehouse_id);
    }
};

const saveInventory = () => {
    if (props.newInventory) {
        emit('newInventory', inventory.value);
    } else {
        emit('updateInventory', inventory.value);
    }
};

const isFormValid = computed(() => {
    return inventory.value.item_id && 
           inventory.value.warehouse_id && 
           inventory.value.bin_location_id && 
           inventory.value.quantity >= 0;
});

const title = computed(() => {
    return props.newInventory ? 'Add New Inventory' : 'Edit Inventory';
});

const statusOptions = [
    { label: 'In Stock', value: 0 },
    { label: 'Low Stock', value: 1 },
    { label: 'Out of Stock', value: 2 },
    { label: 'Reserved', value: 3 },
    { label: 'Damaged', value: 4 },
    { label: 'Expired', value: 5 }
];

// Clear any store errors when component is unmounted
onMounted(() => {
    return () => {
        resourceStore.clearErrors();
        warehouseStore.clearErrors();
        binLocationStore.clearErrors();
    };
});
</script>

<template>
    <FormLayout>
        <template #header>
            <h2 class="text-xl font-bold mb-4">{{ title }}</h2>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Item -->
            <div>
                <InputLabel value="Item" required="true" />
                <Select
                    v-model="inventory.item_id"
                    :options="items"
                    optionLabel="item_name"
                    optionValue="id"
                    placeholder="Select Item"
                    class="w-full"
                    :disabled="!newInventory"
                    :loading="loading"
                />
                <InputError :message="errors.item_id" />
            </div>

            <!-- Warehouse -->
            <div>
                <InputLabel value="Warehouse" required="true" />
                <Select
                    v-model="inventory.warehouse_id"
                    :options="warehouses"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Select Warehouse"
                    class="w-full"
                    :disabled="!newInventory"
                    @change="handleWarehouseChange"
                    :loading="loading"
                />
                <InputError :message="errors.warehouse_id" />
            </div>

            <!-- Bin Location -->
            <div>
                <InputLabel value="Bin Location" required="true" />
                <Select
                    v-model="inventory.bin_location_id"
                    :options="binLocations"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Select Bin Location"
                    class="w-full"
                    :disabled="!newInventory"
                    :loading="loading"
                />
                <InputError :message="errors.bin_location_id" />
            </div>

            <!-- Batch Number -->
            <div>
                <InputLabel value="Batch Number" />
                <TextInput
                    v-model="inventory.batch_number"
                    type="text"
                    class="w-full"
                    :disabled="!newInventory"
                />
                <InputError :message="errors.batch_number" />
            </div>

            <!-- Quantity -->
            <div>
                <InputLabel value="Quantity" required="true" />
                <TextInput
                    v-model="inventory.quantity"
                    type="number"
                    min="0"
                    step="1"
                    class="w-full"
                />
                <InputError :message="errors.quantity" />
            </div>

            <!-- Reorder Point -->
            <div>
                <InputLabel value="Reorder Point" />
                <TextInput
                    v-model="inventory.reorder_point"
                    type="number"
                    min="0"
                    step="1"
                    class="w-full"
                />
                <InputError :message="errors.reorder_point" />
            </div>

            <!-- Expiry Date -->
            <div>
                <InputLabel value="Expiry Date" />
                <TextInput
                    v-model="inventory.expiry_date"
                    type="date"
                    class="w-full"
                />
                <InputError :message="errors.expiry_date" />
            </div>

            <!-- Status -->
            <div>
                <InputLabel value="Status" />
                <Select
                    v-model="inventory.status"
                    :options="statusOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Select Status"
                    class="w-full"
                />
                <InputError :message="errors.status" />
            </div>
        </div>

        <!-- Notes -->
        <div class="mb-4">
            <InputLabel value="Notes" />
            <Textarea
                v-model="inventory.notes"
                rows="3"
                class="w-full"
            />
            <InputError :message="errors.notes" />
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <PrimaryButton
                @click="$emit('close')"
                type="button"
                variant="secondary"
                class="mr-2"
            >
                Cancel
            </PrimaryButton>
            
            <PrimaryButton
                @click="saveInventory"
                :disabled="!isFormValid || loading"
                :loading="loading"
                type="button"
            >
                Save
            </PrimaryButton>
        </div>
    </FormLayout>
</template>