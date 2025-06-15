<script setup>
import { ref, onMounted, computed } from 'vue';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import FormLayout from "@/Layouts/FormLayout.vue";
import { useInventoryStore } from '@/Store/Inventory';

const props = defineProps({
    data: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'success']);
const inventoryStore = useInventoryStore();

const adjustment = ref({
    quantity: 0,
    adjustment_type: 'increase', // 'increase' or 'decrease'
    reason_id: null,
    notes: ''
});

const currentQuantity = ref(0);
const formLoading = ref(false);
const errors = ref({});
const submitted = ref(false);

const reasons = computed(() => {
    return inventoryStore.getAdjustmentReasons;
});

const loadingReasons = computed(() => {
    return inventoryStore.loadingReasons;
});

const newQuantity = computed(() => {
    const current = parseFloat(currentQuantity.value) || 0;
    const adjustmentAmount = parseFloat(adjustment.value.quantity) || 0;
    
    let result;
    if (adjustment.value.adjustment_type === 'increase') {
        result = current + adjustmentAmount;
    } else {
        result = Math.max(0, current - adjustmentAmount);
    }    
    return Number(result.toFixed(2));
});
onMounted(async () => {
    if (props.data) {
        currentQuantity.value = props.data.quantity || 0;
    }
    
    // Fetch adjustment reasons from the store
    try {
        await inventoryStore.fetchAdjustmentReasons();
        if (inventoryStore.error) {
            errors.value.reason_id = 'Error loading reasons';
        }
    } catch (error) {
        console.error('Error fetching adjustment reasons:', error);
        errors.value.reason_id = 'Error loading reasons';
    }
});

const adjustmentTypeOptions = [
    { label: 'Add', value: 'increase' },
    { label: 'Subtract', value: 'decrease' }
];

const validateForm = () => {
    submitted.value = true;
    errors.value = {};

    if (!adjustment.value.quantity || adjustment.value.quantity <= 0) {
        errors.value.quantity = 'Quantity must be greater than 0';
    }

    if (!adjustment.value.reason_id) {
        errors.value.reason_id = 'Reason is required';
    }

    // Check if trying to decrease more than available
    if (adjustment.value.adjustment_type === 'decrease' && adjustment.value.quantity > currentQuantity.value) {
        errors.value.quantity = `Cannot decrease more than the current quantity (${currentQuantity.value})`;
    }

    return Object.keys(errors.value).length === 0;
};

const saveAdjustment = async () => {
    if (!validateForm()) {
        return;
    }

    formLoading.value = true;

    try {
        const adjustmentData = {
            quantity: adjustment.value.quantity,
            adjustment_type: adjustment.value.adjustment_type,
            reason_id: adjustment.value.reason_id,
            notes: adjustment.value.notes
        };

        // Call the store method directly
        const result = await inventoryStore.adjustQuantity(props.data.id, adjustmentData);
        
        if (result && !inventoryStore.error) {
            emit('success', 'adjusted', result);
            emit('close');
        }
    } catch (error) {
        console.error('Error adjusting inventory quantity:', error);
        errors.value.general = 'Failed to adjust inventory quantity';
    } finally {
        formLoading.value = false;
    }
};

const isFormValid = computed(() => {
    return adjustment.value.quantity > 0 && 
           adjustment.value.reason_id !== null &&
           !(adjustment.value.adjustment_type === 'decrease' && adjustment.value.quantity > currentQuantity.value);
});
</script>

<template>
    <FormLayout title="Adjust Inventory Quantity">
        <!-- Item Details Section -->
        <div class="mb-6 p-4  rounded-md">
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
                    <p class="text-sm text-gray-500">Current Quantity</p>
                    <p class="font-medium">{{ currentQuantity }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Batch Number</p>
                    <p class="font-medium">{{ data.batch_number || 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Adjustment Type -->
            <div>
                <InputLabel value="Adjustment Type" required="true" />
                <Select 
                    v-model="adjustment.adjustment_type" 
                    :options="adjustmentTypeOptions" 
                    optionLabel="label" 
                    optionValue="value"
                    placeholder="Select Type" 
                    class="w-full"
                    :disabled="formLoading"
                />
                <InputError :message="submitted && !adjustment.adjustment_type ? 'Adjustment type is required' : errors.adjustment_type" />
            </div>

            <!-- Quantity -->
            <div>
                <InputLabel :value="`Quantity to ${adjustment.adjustment_type === 'increase' ? 'Add' : 'Subtract'}`" required="true" />
                <TextInput
                    v-model="adjustment.quantity" 
                    type="number"
                    min="0.01" 
                    step="1"
                    class="w-full"
                    :disabled="formLoading"
                />
                <InputError :message="submitted && (!adjustment.quantity || adjustment.quantity <= 0) ? 'Quantity must be greater than 0' : errors.quantity" />
            </div>

            <!-- Reason -->
            <div>
                <InputLabel value="Reason" required="true" />
                <Select 
                    v-model="adjustment.reason_id" 
                    :options="reasons" 
                    optionLabel="name" 
                    optionValue="id"
                    placeholder="Select Reason" 
                    class="w-full"
                    :disabled="formLoading || loadingReasons"
                    :loading="loadingReasons"
                />
                <InputError :message="submitted && !adjustment.reason_id ? 'Reason is required' : errors.reason_id" />
            </div>

            <!-- New Quantity (Calculated) -->
            <div>
                <InputLabel value="New Quantity" />
                <TextInput
                    v-model="newQuantity"
                    type="text"
                    readonly
                    class="w-full"
                />
            
            </div>
        </div>

        <!-- Notes -->
        <div class="mb-4">
            <InputLabel value="Notes" />
            <Textarea 
                v-model="adjustment.notes" 
                rows="3" 
                class="w-full"
                placeholder="Enter details about this adjustment"
                :disabled="formLoading"
            />
            <InputError :message="errors.notes" />
        </div>

        <!-- General error message -->
        <div v-if="errors.general" class="text-red-500 text-sm mb-4">
            {{ errors.general }}
        </div>

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
                @click="saveAdjustment"
                :disabled="!isFormValid || formLoading || loadingReasons"
                :loading="formLoading"
                type="button"
            >
                {{ formLoading ? 'Saving...' : 'Save Adjustment' }}
            </PrimaryRoseButton>
        </div>
    </FormLayout>
</template>