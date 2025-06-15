<script>
import { ref, onMounted, computed } from 'vue';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';
import axios from 'axios';


export default {
    components: {
        InputNumber,
        Dropdown,
        Button,
        Textarea
    },
    props: {
        data: {
            type: Object,
            required: true
        },
        loading: {
            type: Boolean,
            default: false
        }
    },
    emits: ['close', 'updateInventory'],
    setup(props, { emit }) {
        const adjustment = ref({
            quantity: 0,
            adjustment_type: 'add', // 'add' or 'subtract'
            reason_id: null,
            notes: ''
        });

        const reasons = ref([]);
        const currentQuantity = ref(0);
        const newQuantity = computed(() => {
            if (adjustment.value.adjustment_type === 'add') {
                return currentQuantity.value + adjustment.value.quantity;
            } else {
                return Math.max(0, currentQuantity.value - adjustment.value.quantity);
            }
        });

        onMounted(async () => {
            if (props.data) {
                currentQuantity.value = props.data.quantity || 0;
            }
            
            await fetchReasons();
        });

        const fetchReasons = async () => {
            try {
                const response = await axios.get('/api/stock-adjustment-reasons', {
                    params: {
                        business_id: localStorage.getItem('business_id'),
                        rows: 100
                    }
                });
                reasons.value = response.data.data || [];
                
                // If no reasons exist yet, create default ones
                if (reasons.value.length === 0) {
                    reasons.value = [
                        { id: 1, name: 'Inventory Count' },
                        { id: 2, name: 'Damaged Goods' },
                        { id: 3, name: 'Returned Items' },
                        { id: 4, name: 'System Correction' },
                        { id: 5, name: 'Lost Items' },
                        { id: 6, name: 'Expired Items' }
                    ];
                }
            } catch (error) {
                console.error('Error fetching adjustment reasons:', error);
            }
        };

        const adjustmentTypeOptions = [
            { label: 'Add', value: 'add' },
            { label: 'Subtract', value: 'subtract' }
        ];

        const saveAdjustment = () => {
            const adjustmentData = {
                quantity: adjustment.value.quantity,
                adjustment_type: adjustment.value.adjustment_type,
                reason_id: adjustment.value.reason_id,
                notes: adjustment.value.notes
            };
            
            emit('updateInventory', { 
                id: props.data.id, 
                adjustment: adjustmentData 
            });
        };

        const isFormValid = computed(() => {
            return adjustment.value.quantity > 0 && 
                   adjustment.value.reason_id !== null;
        });

        return {
            adjustment,
            reasons,
            currentQuantity,
            newQuantity,
            adjustmentTypeOptions,
            saveAdjustment,
            isFormValid
        };
    }
};
</script>

<template>
    <div class="p-4">
        <h2 class="text-xl font-bold mb-4">Adjust Inventory Quantity</h2>
        
        <div class="mb-6 p-4 bg-gray-50 rounded-md">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Item</p>
                    <p class="font-medium">{{ data.item?.name }}</p>
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
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment Type *</label>
                <Dropdown 
                    v-model="adjustment.adjustment_type" 
                    :options="adjustmentTypeOptions" 
                    optionLabel="label" 
                    optionValue="value"
                    placeholder="Select Type" 
                    class="w-full"
                    :disabled="loading"
                />
            </div>

            <!-- Quantity -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity to {{ adjustment.adjustment_type === 'add' ? 'Add' : 'Subtract' }} *</label>
                <InputNumber 
                    v-model="adjustment.quantity" 
                    :min="0.01" 
                    :step="1"
                    class="w-full"
                    :disabled="loading"
                />
            </div>

            <!-- Reason -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                <Dropdown 
                    v-model="adjustment.reason_id" 
                    :options="reasons" 
                    optionLabel="name" 
                    optionValue="id"
                    placeholder="Select Reason" 
                    class="w-full"
                    :disabled="loading"
                />
            </div>

            <!-- New Quantity (Calculated) -->
            <div class="form-group">
                <label class="block text-sm font-medium text-gray-700 mb-1">New Quantity</label>
                <div class="p-2 bg-gray-100 rounded border border-gray-300 text-lg font-medium">
                    {{ newQuantity }}
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="form-group mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <Textarea 
                v-model="adjustment.notes" 
                rows="3" 
                class="w-full"
                placeholder="Enter details about this adjustment"
                :disabled="loading"
            />
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
                label="Save Adjustment" 
                icon="pi pi-check" 
                @click="saveAdjustment"
                :disabled="!isFormValid || loading"
                :loading="loading"
            />
        </div>
    </div>
</template>
