<script setup>
import { ref, onMounted, watch } from 'vue';
import { useWarehouseStore } from '@/Store/Warehouse';
import { useWarehouseZoneStore } from '@/Store/WarehouseZone';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import Select from "primevue/select";
import Dropdown from "primevue/dropdown";
import Textarea from "primevue/textarea";
import Checkbox from 'primevue/checkbox';
import InputNumber from 'primevue/inputnumber';
import FormLayout from "@/Layouts/FormLayout.vue";
import PrimaryRoseButton from '@/Components/PrimaryRoseButton.vue';

const props = defineProps({
    newZone: {
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
    },
    warehouseId: {
        type: Number,
        default: null
    }
});

const emit = defineEmits(['close', 'success']);

const warehouseStore = useWarehouseStore();
const warehouseZoneStore = useWarehouseZoneStore();
const zone = ref({
    name: '',
    code: '',
    warehouse_id: props.warehouseId || null,
    description: '',
    zone_type: 'storage',
    temperature_controlled: false,
    min_temperature: null,
    max_temperature: null,
    temperature_unit: 'C',
    status: 'active'
});
const submitted = ref(false);
const warehouses = ref([]);
const zoneTypes = ref([]);
const errors = ref({});
const formLoading = ref(props.loading||false);

// Status options for dropdown
const statusOptions = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
    { label: 'Maintenance', value: 'maintenance' }
];

// Temperature unit options
const temperatureUnitOptions = [
    { label: '°C (Celsius)', value: 'C' },
    { label: '°F (Fahrenheit)', value: 'F' }
];

const loadWarehouses = async () => {
    try {
        await warehouseStore.fetchWarehouses();
        warehouses.value = warehouseStore.warehouses.data || [];
    } catch (error) {
        errors.value.warehouse_id = 'Error loading warehouses';
        console.error('Error loading warehouses:', error);
    }
};

const loadZoneTypes = async () => {
    try {
        await warehouseZoneStore.fetchZoneTypes();
        zoneTypes.value = warehouseZoneStore.zoneTypes || [];
    } catch (error) {
        errors.value.zone_type = 'Error loading zone types';
        console.error('Error loading zone types:', error);
    }
};

const initForm = () => {
    if (props.data) {
        zone.value = { ...props.data };
    } else {
        zone.value = {
            name: '',
            code: '',
            warehouse_id: props.warehouseId || null,
            description: '',
            zone_type: 'storage',
            temperature_controlled: false,
            min_temperature: null,
            max_temperature: null,
            temperature_unit: 'C',
            status: 'active'
        };
    }
    submitted.value = false;
};

const cancel = () => {
    emit('close');
};

const save = async () => {
    submitted.value = true;
    errors.value = {};
    
    // Validate required fields
    if (!zone.value.name) {
        errors.value.name = 'Name is required';
    }

    if (!props.warehouseId && !zone.value.warehouse_id) {
        errors.value.warehouse_id = 'Warehouse is required';
    }
    
    if (!zone.value.zone_type) {
        errors.value.zone_type = 'Zone type is required';
    }
    
    // Validate temperature fields if temperature controlled
    if (zone.value.temperature_controlled) {
        if (zone.value.min_temperature === null || zone.value.min_temperature === '') {
            errors.value.min_temperature = 'Minimum temperature is required for temperature controlled zones';
        }
        
        if (zone.value.max_temperature === null || zone.value.max_temperature === '') {
            errors.value.max_temperature = 'Maximum temperature is required for temperature controlled zones';
        } else if (parseFloat(zone.value.max_temperature) < parseFloat(zone.value.min_temperature)) {
            errors.value.max_temperature = 'Maximum temperature must be greater than or equal to minimum temperature';
        }
        
        if (!zone.value.temperature_unit) {
            errors.value.temperature_unit = 'Temperature unit is required for temperature controlled zones';
        }
    }
    
    // If there are validation errors, don't submit
    if (Object.keys(errors.value).length > 0) {
        return;
    }

    // If we have a warehouseId from props, make sure it's set
    if (props.warehouseId && !zone.value.warehouse_id) {
        zone.value.warehouse_id = props.warehouseId;
    }

    formLoading.value = true;
    try {
        let result;
        
        if (props.newZone) {
            // Create new zone
            result = await warehouseZoneStore.createZone(zone.value);
            if (result) {
                warehouseZoneStore.success = "Warehouse zone created successfully";
                emit('success', 'created', result);
                emit('close');
            }
        } else {
            // Update existing zone
            result = await warehouseZoneStore.updateZone(zone.value);
            if (result) {
                warehouseZoneStore.success = "Warehouse zone updated successfully";
                emit('success', 'updated', result);
                emit('close');
            }
        }
    } catch (error) {
        console.error("Error saving zone:", error);
        warehouseZoneStore.error = props.newZone 
            ? "Failed to create warehouse zone" 
            : "Failed to update warehouse zone";
    } finally {
        formLoading.value = false;
    }
};

watch(() => props.data, (newVal) => {
    if (newVal) {
        zone.value = { ...newVal };
    } else {
        initForm();
    }
}, { deep: true });

onMounted(() => {
    loadWarehouses();
    loadZoneTypes();
    initForm();
});

const title = props.newZone ? 'Add Warehouse Zone' : 'Edit Warehouse Zone';
</script>

<template>
    <FormLayout :title="title">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name Field -->
            <div>
                <InputLabel value="Name" required="true" />
                <TextInput
                    v-model="zone.name"
                    type="text"
                    class="w-full"
                    autofocus
                />
                <InputError :message="submitted && !zone.name ? 'Name is required' : errors.name" />
            </div>
            
            <!-- Code Field -->
            <div>
                <InputLabel value="Code" />
                <TextInput
                    v-model="zone.code"
                    type="text"
                    class="w-full"
                    :disabled="!newZone"
                    placeholder="Will be auto-generated if empty"
                />
                <InputError :message="errors.code" />
            </div>
            
            <!-- Warehouse Field (if not provided via props) -->
            <div v-if="!warehouseId">
                <InputLabel value="Warehouse" required="true" />
                <Select
                    v-model="zone.warehouse_id"
                    :options="warehouses"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Select Warehouse"
                    class="w-full"
                    :loading="warehouseStore.loading"
                />
                <InputError :message="submitted && !zone.warehouse_id ? 'Warehouse is required' : errors.warehouse_id" />
            </div>
            
            <!-- Zone Type Field -->
            <div>
                <InputLabel value="Zone Type" required="true" />
                <Select
                    v-model="zone.zone_type"
                    :options="zoneTypes"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Select Zone Type"
                    class="w-full"
                    :loading="warehouseZoneStore.loading"
                />
                <InputError :message="submitted && !zone.zone_type ? 'Zone type is required' : errors.zone_type" />
            </div>
            
            <!-- Status Field -->
            <div>
                <InputLabel value="Status" />
                <Dropdown
                    v-model="zone.status"
                    :options="statusOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Select Status"
                    class="w-full"
                />
                <InputError :message="errors.status" />
            </div>
        </div>
        
        <!-- Description Field -->
        <div class="mt-4">
            <InputLabel value="Description" />
            <Textarea
                v-model="zone.description"
                rows="3"
                class="w-full"
                autoResize
            />
            <InputError :message="errors.description" />
        </div>
        
        <!-- Temperature Controlled Field -->
        <div class="flex items-center mt-4">
            <Checkbox
                v-model="zone.temperature_controlled"
                :binary="true"
            />
            <span class="ml-2">Temperature Controlled Zone</span>
        </div>
        
        <!-- Temperature Fields (conditional on temperature_controlled) -->
        <div v-if="zone.temperature_controlled" class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
                <InputLabel value="Min Temperature" required="true" />
                <InputNumber
                    v-model="zone.min_temperature"
                    class="w-full"
                    :minFractionDigits="1"
                    :maxFractionDigits="1"
                />
                <InputError :message="submitted && zone.temperature_controlled && !zone.min_temperature ? 'Minimum temperature is required' : errors.min_temperature" />
            </div>
            
            <div>
                <InputLabel value="Max Temperature" required="true" />
                <InputNumber
                    v-model="zone.max_temperature"
                    class="w-full"
                    :minFractionDigits="1"
                    :maxFractionDigits="1"
                />
                <InputError :message="submitted && zone.temperature_controlled && !zone.max_temperature ? 'Maximum temperature is required' : errors.max_temperature" />
            </div>
            
            <div>
                <InputLabel value="Temperature Unit" required="true" />
                <Dropdown
                    v-model="zone.temperature_unit"
                    :options="temperatureUnitOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Select Unit"
                    class="w-full"
                />
                <InputError :message="submitted && zone.temperature_controlled && !zone.temperature_unit ? 'Temperature unit is required' : errors.temperature_unit" />
            </div>
        </div>
        
        <div class="flex justify-end gap-3 mt-6">
            <PrimaryButton
                @click="cancel"
                :disabled="formLoading || props.loading"
                type="button"
            >
                Cancel
            </PrimaryButton>
            
            <PrimaryRoseButton
                @click="save"
                :disabled="formLoading || props.loading || warehouseStore.loading || warehouseZoneStore.loading || Object.keys(errors).length > 0"
                :loading="formLoading || props.loading"
                type="button"
            >
                {{ formLoading || props.loading ? 'Saving...' : (newZone ? 'Create Zone' : 'Update Zone') }}
            </PrimaryRoseButton>
        </div>
    </FormLayout>
</template>