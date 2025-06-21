<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import FormLayout from "@/Layouts/FormLayout.vue";
import Calendar from "primevue/calendar";
import { useInventoryStore } from '@/Store/Inventory';

const props = defineProps({
    data: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'success']);
const inventoryStore = useInventoryStore();

// Batch operation data
const batchOperation = ref({
    operation: 'create', // create, adjust, expire, damage
    quantity: 0,
    batch_id: null,
    batch_data: {
        batch_number: '',
        lot_number: '',
        manufacturing_date: null,
        expiry_date: null,
        cost_price: null,
        supplier_id: null
    },
    damaged_quantity: 0,
    reason_id: null,
    adjust_inventory: true,
    notes: ''
});

// Form state
const currentQuantity = ref(0);
const formLoading = ref(false);
const errors = ref({});
const submitted = ref(false);

// Available batches for the inventory
const batches = ref([]);
const loadingBatches = ref(false);

// Batch operations options
const operationOptions = [
    { label: 'Create New Batch', value: 'create' },
    { label: 'Adjust Batch Quantity', value: 'adjust' },
    { label: 'Mark Batch as Expired', value: 'expire' },
    { label: 'Report Damaged Items', value: 'damage' }
];

// Get adjustment reasons from store
const reasons = computed(() => {
    return inventoryStore.getAdjustmentReasons;
});

const loadingReasons = computed(() => {
    return inventoryStore.loadingReasons;
});

// Load data on mount
onMounted(async () => {
    if (props.data) {
        currentQuantity.value = props.data.quantity || 0;
        
        // Fetch batches for this inventory
        await fetchBatches();
        
        try {
            await inventoryStore.fetchAdjustmentReasons();
            if (inventoryStore.error) {
                errors.value.reason_id = 'Error loading reasons';
            }
        } catch (error) {
            console.error('Error fetching adjustment reasons:', error);
            errors.value.reason_id = 'Error loading reasons';
        }
    }
});

// Fetch batches for the inventory item
const fetchBatches = async () => {
    loadingBatches.value = true;
    
    try {
        const response = await inventoryStore.fetchInventoryBatches(props.data.id);
        batches.value = response?.data || [];
    } catch (error) {
        console.error('Error fetching batches:', error);
        errors.value.batch_id = 'Error loading batches';
    } finally {
        loadingBatches.value = false;
    }
};

// Watch for operation changes to reset relevant fields
watch(() => batchOperation.value.operation, (newOperation) => {
    errors.value = {};
    
    // Reset fields based on operation
    if (newOperation === 'create') {
        batchOperation.value.batch_id = null;
    } else if (newOperation === 'adjust' || newOperation === 'expire') {
        batchOperation.value.batch_data = {
            batch_number: '',
            lot_number: '',
            manufacturing_date: null,
            expiry_date: null,
            cost_price: null,
            supplier_id: null
        };
    }
});

// Form validation
const validateForm = () => {
    submitted.value = true;
    errors.value = {};
    
    const { operation } = batchOperation.value;
    
    // Common validations
    if (['create', 'adjust'].includes(operation)) {
        if (!batchOperation.value.quantity || batchOperation.value.quantity <= 0) {
            errors.value.quantity = 'Quantity must be greater than 0';
        }
    }
    
    // Operation-specific validations
    if (operation === 'create') {
        if (!batchOperation.value.batch_data.batch_number) {
            errors.value.batch_number = 'Batch number is required';
        }
    } else {
        if (!batchOperation.value.batch_id) {
            errors.value.batch_id = 'Please select a batch';
        }
    }
    
    if (operation === 'damage') {
        if (!batchOperation.value.damaged_quantity || batchOperation.value.damaged_quantity <= 0) {
            errors.value.damaged_quantity = 'Damaged quantity must be greater than 0';
        }
        
        if (!batchOperation.value.reason_id) {
            errors.value.reason_id = 'Reason is required for damages';
        }
    }
    
    if (operation === 'expire' && !batchOperation.value.reason_id) {
        errors.value.reason_id = 'Reason is required for expiry';
    }
    
    return Object.keys(errors.value).length === 0;
};

// Save the batch operation
const saveBatchOperation = async () => {
    if (!validateForm()) {
        return;
    }
    
    formLoading.value = true;
    
    try {
        const batchData = {
            operation: batchOperation.value.operation,
            quantity: batchOperation.value.quantity,
            batch_id: batchOperation.value.batch_id,
            batch_data: batchOperation.value.batch_data,
            damaged_quantity: batchOperation.value.damaged_quantity,
            reason_id: batchOperation.value.reason_id,
            adjust_inventory: batchOperation.value.adjust_inventory,
            notes: batchOperation.value.notes
        };
        
        // Format dates for API
        if (batchData.batch_data.manufacturing_date) {
            batchData.batch_data.manufacturing_date = formatDateForApi(batchData.batch_data.manufacturing_date);
        }
        
        if (batchData.batch_data.expiry_date) {
            batchData.batch_data.expiry_date = formatDateForApi(batchData.batch_data.expiry_date);
        }
        
        // Remove empty values
        Object.keys(batchData.batch_data).forEach(key => {
            if (batchData.batch_data[key] === '' || batchData.batch_data[key] === null) {
                delete batchData.batch_data[key];
            }
        });
        
        // Call the store method
        const result = await inventoryStore.processBatch(props.data.id, batchData);
        
        if (result && !inventoryStore.error) {
            emit('success', 'batch-processed', result);
            emit('close');
        }
    } catch (error) {
        console.error('Error processing batch operation:', error);
        errors.value.general = 'Failed to process batch operation';
    } finally {
        formLoading.value = false;
    }
};

// Format date for API
const formatDateForApi = (date) => {
    if (!date) return null;
    
    if (typeof date === 'string') {
        return date;
    }
    
    // Format to YYYY-MM-DD
    const d = new Date(date);
    return d.toISOString().split('T')[0];
};

// Compute if form is valid for submit button
const isFormValid = computed(() => {
    const { operation } = batchOperation.value;
    
    if (operation === 'create') {
        return batchOperation.value.quantity > 0 && 
               batchOperation.value.batch_data.batch_number;
    } else if (operation === 'adjust') {
        return batchOperation.value.quantity > 0 && 
               batchOperation.value.batch_id;
    } else if (operation === 'expire') {
        return batchOperation.value.batch_id && 
               batchOperation.value.reason_id;
    } else if (operation === 'damage') {
        return batchOperation.value.batch_id && 
               batchOperation.value.damaged_quantity > 0 && 
               batchOperation.value.reason_id;
    }
    
    return false;
});

// Title based on operation
const operationTitle = computed(() => {
    switch (batchOperation.value.operation) {
        case 'create': return 'Create New Batch';
        case 'adjust': return 'Adjust Batch Quantity';
        case 'expire': return 'Mark Batch as Expired';
        case 'damage': return 'Report Damaged Items';
        default: return 'Process Batch';
    }
});
</script>

<template>
    <FormLayout :title="operationTitle">
        <!-- Item Details Section -->
        <div class="mb-6 p-4 rounded-md">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Item</p>
                    <p class="font-medium">{{ data.item?.item_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">SKU</p>
                    <p class="font-medium">{{ data.item?.sku }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Warehouse</p>
                    <p class="font-medium">{{ data.warehouse?.name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Bin Location</p>
                    <p class="font-medium">{{ data.bin_location?.name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Current Total Quantity</p>
                    <p class="font-medium">{{ currentQuantity }}</p>
                </div>
            </div>
        </div>

        <!-- Operation Type Selection -->
        <div class="mb-4">
            <InputLabel value="Operation Type" required="true" />
            <Select 
                v-model="batchOperation.operation" 
                :options="operationOptions" 
                optionLabel="label" 
                optionValue="value"
                placeholder="Select Operation" 
                class="w-full"
                :disabled="formLoading"
            />
            <InputError :message="submitted && !batchOperation.operation ? 'Operation type is required' : errors.operation" />
        </div>

        <!-- Conditional Fields Based on Operation Type -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Create New Batch - Batch Fields -->
            <template v-if="batchOperation.operation === 'create'">
                <!-- Batch Number -->
                <div>
                    <InputLabel value="Batch Number" required="true" />
                    <TextInput
                        v-model="batchOperation.batch_data.batch_number"
                        type="text"
                        class="w-full"
                        :disabled="formLoading"
                    />
                    <InputError :message="submitted && !batchOperation.batch_data.batch_number ? 'Batch number is required' : errors.batch_number" />
                </div>
                
                <!-- Lot Number -->
                <div>
                    <InputLabel value="Lot Number" />
                    <TextInput
                        v-model="batchOperation.batch_data.lot_number"
                        type="text"
                        class="w-full"
                        :disabled="formLoading"
                    />
                    <InputError :message="errors.lot_number" />
                </div>
                
                <!-- Quantity -->
                <div>
                    <InputLabel value="Quantity" required="true" />
                    <TextInput
                        v-model="batchOperation.quantity"
                        type="number"
                        min="0.01"
                        step="1"
                        class="w-full"
                        :disabled="formLoading"
                    />
                    <InputError :message="submitted && (!batchOperation.quantity || batchOperation.quantity <= 0) ? 'Quantity must be greater than 0' : errors.quantity" />
                </div>
                
                <!-- Cost Price -->
                <div>
                    <InputLabel value="Cost Price" />
                    <TextInput
                        v-model="batchOperation.batch_data.cost_price"
                        type="number"
                        min="0"
                        step="0.01"
                        class="w-full"
                        :disabled="formLoading"
                    />
                    <InputError :message="errors.cost_price" />
                </div>
                
                <!-- Manufacturing Date -->
                <div>
                    <InputLabel value="Manufacturing Date" />
                    <Calendar
                        v-model="batchOperation.batch_data.manufacturing_date"
                        dateFormat="yy-mm-dd"
                        class="w-full"
                        :disabled="formLoading"
                    />
                    <InputError :message="errors.manufacturing_date" />
                </div>
                
                <!-- Expiry Date -->
                <div>
                    <InputLabel value="Expiry Date" />
                    <Calendar
                        v-model="batchOperation.batch_data.expiry_date"
                        dateFormat="yy-mm-dd"
                        class="w-full"
                        :disabled="formLoading"
                    />
                    <InputError :message="errors.expiry_date" />
                </div>
                
                <!-- Adjust Inventory -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        v-model="batchOperation.adjust_inventory" 
                        class="mr-2 h-4 w-4 text-blue-600" 
                    />
                    <label class="text-sm text-gray-700">Adjust inventory quantity</label>
                </div>

                <!-- Reason for Expire/Damage -->
                <div>
                    <InputLabel value="Reason" required="true" />
                    <Select 
                        v-model="batchOperation.reason_id" 
                        :options="reasons" 
                        optionLabel="name" 
                        optionValue="id"
                        placeholder="Select Reason" 
                        class="w-full"
                        :disabled="formLoading || loadingReasons"
                        :loading="loadingReasons"
                    />
                    <InputError :message="submitted && !batchOperation.reason_id ? 'Reason is required' : errors.reason_id" />
                </div>
                
            </template>

            <!-- Adjust/Expire/Damage - Require Batch Selection -->
            <template v-else>
                <!-- Batch Selection -->
                <div class="md:col-span-2">
                    <InputLabel value="Select Batch" required="true" />
                    <Select 
                        v-model="batchOperation.batch_id" 
                        :options="batches" 
                        optionLabel="batch_number" 
                        optionValue="id"
                        placeholder="Select Batch" 
                        class="w-full"
                        :disabled="formLoading || loadingBatches"
                        :loading="loadingBatches"
                    />
                    <InputError :message="submitted && !batchOperation.batch_id ? 'Batch selection is required' : errors.batch_id" />
                </div>
                
                <!-- Quantity for Adjust -->
                <div v-if="batchOperation.operation === 'adjust'">
                    <InputLabel value="New Quantity" required="true" />
                    <TextInput
                        v-model="batchOperation.quantity"
                        type="number"
                        min="0"
                        step="1"
                        class="w-full"
                        :disabled="formLoading"
                    />
                    <InputError :message="submitted && (!batchOperation.quantity || batchOperation.quantity <= 0) ? 'Quantity must be greater than 0' : errors.quantity" />
                </div>
                
                <!-- Quantity for Damage -->
                <div v-if="batchOperation.operation === 'damage'">
                    <InputLabel value="Damaged Quantity" required="true" />
                    <TextInput
                        v-model="batchOperation.damaged_quantity"
                        type="number"
                        min="0.01"
                        step="1"
                        class="w-full"
                        :disabled="formLoading"
                    />
                    <InputError :message="submitted && (!batchOperation.damaged_quantity || batchOperation.damaged_quantity <= 0) ? 'Damaged quantity must be greater than 0' : errors.damaged_quantity" />
                </div>
                
                <!-- Adjust Inventory -->
                <div v-if="['expire', 'damage'].includes(batchOperation.operation)" class="flex items-center">
                    <input 
                        type="checkbox" 
                        v-model="batchOperation.adjust_inventory" 
                        class="mr-2 h-4 w-4 text-blue-600" 
                    />
                    <label class="text-sm text-gray-700">Adjust inventory quantity</label>
                </div>
            </template>
        </div>

        <!-- Notes -->
        <div class="mb-4">
            <InputLabel value="Notes" />
            <Textarea 
                v-model="batchOperation.notes" 
                rows="3" 
                class="w-full"
                placeholder="Enter details about this batch operation"
                :disabled="formLoading"
            />
            <InputError :message="errors.notes" />
        </div>

        <!-- General error message -->
        <div v-if="errors.general" class="text-red-500 text-sm mb-4">
            {{ errors.general }}
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-2 mt-6">
            <PrimaryButton
                @click="$emit('close')"
                :disabled="formLoading"
                type="button"
                class="mr-2"
            >
                Cancel
            </PrimaryButton>
            
            <PrimaryRoseButton
                @click="saveBatchOperation"
                :disabled="!isFormValid || formLoading || loadingReasons || loadingBatches"
                :loading="formLoading"
                type="button"
            >
                {{ formLoading ? 'Processing...' : 'Process Batch' }}
            </PrimaryRoseButton>
        </div>
    </FormLayout>
</template>