<script setup>
import { ref, onMounted, watch } from 'vue';
import { useWarehouseStore } from '@/Store/Warehouse';
import { useBinLocationStore } from '@/Store/BinLocation';
import { useWarehouseZoneStore } from '@/Store/WarehouseZone'; // If you have this store
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import Dropdown from "primevue/dropdown";
import InputNumber from "primevue/inputnumber";
import FormLayout from "@/Layouts/FormLayout.vue";
import PrimaryRoseButton from '@/Components/PrimaryRoseButton.vue';

const props = defineProps({
    newBinLocation: {
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
const binLocationStore = useBinLocationStore();
const warehouseZoneStore = useWarehouseZoneStore(); // If you have this store

const binLocation = ref({
    name: '',
    code: '',
    warehouse_id: props.warehouseId || null,
    zone_id: null,
    aisle: '',
    rack: '',
    shelf: '',
    bin: '',
    capacity: 0,
    capacity_unit: '',
    status: 'active',
    location_type: 'standard'
});

const submitted = ref(false);
const warehouses = ref([]);
const zones = ref([]);
const errors = ref({});
const formLoading = ref(false);

// Options for status and location_type dropdowns
const statusOptions = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
    { label: 'Full', value: 'full' },
    { label: 'Maintenance', value: 'maintenance' }
];

const locationTypeOptions = [
    { label: 'Standard', value: 'standard' },
    { label: 'Receiving', value: 'receiving' },
    { label: 'Shipping', value: 'shipping' },
    { label: 'Quarantine', value: 'quarantine' },
    { label: 'Returns', value: 'returns' }
];

const capacityUnitOptions = [
    { label: 'kg', value: 'kg' },
    { label: 'lb', value: 'lb' },
    { label: 'pc', value: 'pc' },
    { label: 'cu ft', value: 'cu_ft' },
    { label: 'cu m', value: 'cu_m' }
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

const loadWarehouseZones = async (warehouseId) => {
    if (!warehouseId) return;
    
    try {
        await warehouseZoneStore.fetchZones({ warehouse_id: warehouseId });
        zones.value = warehouseZoneStore.zones.data || [];
    } catch (error) {
        console.error('Error loading warehouse zones:', error);
        errors.value.zone_id = 'Error loading warehouse zones';
    }
};

const initForm = () => {
    if (props.data) {
        binLocation.value = { ...props.data };
    } else {
        binLocation.value = {
            name: '',
            code: '',
            warehouse_id: props.warehouseId || null,
            zone_id: null,
            aisle: '',
            rack: '',
            shelf: '',
            bin: '',
            capacity: 0,
            capacity_unit: '',
            status: 'active',
            location_type: 'standard'
        };
    }
    submitted.value = false;
    
    // If we have a warehouse ID, load its zones
    if (binLocation.value.warehouse_id) {
        loadWarehouseZones(binLocation.value.warehouse_id);
    }
};

const cancel = () => {
    emit('close');
};

const save = async () => {
    submitted.value = true;
    errors.value = {};
    
    // Validate required fields
    if (!binLocation.value.name) {
        errors.value.name = 'Name is required';
    }
    
    if (!binLocation.value.code && props.newBinLocation) {
        errors.value.code = 'Code is required';
    }

    if (!props.warehouseId && !binLocation.value.warehouse_id) {
        errors.value.warehouse_id = 'Warehouse is required';
    }
    
    // If there are validation errors, don't submit
    if (Object.keys(errors.value).length > 0) {
        return;
    }

    // If we have a warehouseId from props, make sure it's set
    if (props.warehouseId && !binLocation.value.warehouse_id) {
        binLocation.value.warehouse_id = props.warehouseId;
    }

    formLoading.value = true;
    try {
        let result;
        
        if (props.newBinLocation) {
            // Create new bin location
            result = await binLocationStore.createBinLocation(binLocation.value);
            if (result) {
                binLocationStore.success = "Bin location created successfully";
                emit('success', 'created', result);
                emit('close');
            }
        } else {
            // Update existing bin location
            result = await binLocationStore.updateBinLocation(binLocation.value);
            if (result) {
                binLocationStore.success = "Bin location updated successfully";
                emit('success', 'updated', result);
                emit('close');
            }
        }
    } catch (error) {
        console.error("Error saving bin location:", error);
        binLocationStore.error = props.newBinLocation 
            ? "Failed to create bin location" 
            : "Failed to update bin location";
    } finally {
        formLoading.value = false;
    }
};

// Watch for changes in the data prop
watch(() => props.data, (newVal) => {
    if (newVal) {
        binLocation.value = { ...newVal };
        if (binLocation.value.warehouse_id) {
            loadWarehouseZones(binLocation.value.warehouse_id);
        }
    } else {
        initForm();
    }
}, { deep: true });

// Watch for changes in warehouse_id to load zones
watch(() => binLocation.value.warehouse_id, (newWarehouseId) => {
    if (newWarehouseId) {
        loadWarehouseZones(newWarehouseId);
    } else {
        zones.value = [];
    }
});

onMounted(() => {
    loadWarehouses();
    initForm();
});

const title = props.newBinLocation ? 'Add Bin Location' : 'Edit Bin Location';
</script>

<template>
    <FormLayout :title="title">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name Field -->
            <div>
                <InputLabel value="Name" required="true" />
                <TextInput
                    v-model="binLocation.name"
                    type="text"
                    class="w-full"
                    autofocus
                />
                <InputError :message="submitted && !binLocation.name ? 'Name is required' : errors.name" />
            </div>
            
            <!-- Code Field -->
            <div>
                <InputLabel value="Code" required="true" />
                <TextInput
                    v-model="binLocation.code"
                    type="text"
                    class="w-full"
                    :disabled="!props.newBinLocation"
                    placeholder="Will be auto-generated if empty"
                />
                <InputError :message="submitted && props.newBinLocation && !binLocation.code ? 'Code is required or will be auto-generated' : errors.code" />
            </div>
            
            <!-- Warehouse Field (if not provided via props) -->
            <div v-if="!warehouseId">
                <InputLabel value="Warehouse" required="true" />
                <Select
                    v-model="binLocation.warehouse_id"
                    :options="warehouses"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Select Warehouse"
                    class="w-full"
                    :loading="warehouseStore.loading"
                />
                <InputError :message="submitted && !binLocation.warehouse_id ? 'Warehouse is required' : errors.warehouse_id" />
            </div>
            
            <!-- Zone Field -->
            <div>
                <InputLabel value="Zone" />
                <Select
                    v-model="binLocation.zone_id"
                    :options="zones"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Select Zone (Optional)"
                    class="w-full"
                    :loading="warehouseZoneStore.loading"
                    :disabled="!binLocation.warehouse_id"
                />
                <InputError :message="errors.zone_id" />
            </div>
            
            <!-- Aisle Field -->
            <div>
                <InputLabel value="Aisle" />
                <TextInput
                    v-model="binLocation.aisle"
                    type="text"
                    class="w-full"
                />
                <InputError :message="errors.aisle" />
            </div>
            
            <!-- Rack Field -->
            <div>
                <InputLabel value="Rack" />
                <TextInput
                    v-model="binLocation.rack"
                    type="text"
                    class="w-full"
                />
                <InputError :message="errors.rack" />
            </div>
            
            <!-- Shelf Field -->
            <div>
                <InputLabel value="Shelf" />
                <TextInput
                    v-model="binLocation.shelf"
                    type="text"
                    class="w-full"
                />
                <InputError :message="errors.shelf" />
            </div>
            
            <!-- Bin Field -->
            <div>
                <InputLabel value="Bin" />
                <TextInput
                    v-model="binLocation.bin"
                    type="text"
                    class="w-full"
                />
                <InputError :message="errors.bin" />
            </div>
            
            <!-- Capacity Field -->
            <div>
                <InputLabel value="Capacity" />
                <InputNumber
                    v-model="binLocation.capacity"
                    class="w-full"
                    :minFractionDigits="2"
                    :maxFractionDigits="2"
                />
                <InputError :message="errors.capacity" />
            </div>
            
            <!-- Capacity Unit Field -->
            <div>
                <InputLabel value="Capacity Unit" />
                <Dropdown
                    v-model="binLocation.capacity_unit"
                    :options="capacityUnitOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Select Unit"
                    class="w-full"
                />
                <InputError :message="errors.capacity_unit" />
            </div>
            
            <!-- Status Field -->
            <div>
                <InputLabel value="Status" />
                <Dropdown
                    v-model="binLocation.status"
                    :options="statusOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Select Status"
                    class="w-full"
                />
                <InputError :message="errors.status" />
            </div>
            
            <!-- Location Type Field -->
            <div>
                <InputLabel value="Location Type" />
                <Dropdown
                    v-model="binLocation.location_type"
                    :options="locationTypeOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Select Type"
                    class="w-full"
                />
                <InputError :message="errors.location_type" />
            </div>
        </div>
        
        <div class="flex justify-end gap-2 mt-6">
            <PrimaryButton
                @click="cancel"
                :disabled="formLoading || props.loading"
                type="button"
                class="mr-2"
            >
                Cancel
            </PrimaryButton>
            
            <PrimaryRoseButton
                @click="save"
                :disabled="formLoading || props.loading || Object.keys(errors).length > 0|| warehouseZoneStore.loading || warehouseStore.loading"
                :loading="formLoading || props.loading"
                type="button"
            >
                {{ formLoading || props.loading ? 'Saving...' : (props.newBinLocation ? 'Create Bin Location' : 'Update Bin Location') }}
            </PrimaryRoseButton>
        </div>
    </FormLayout>
</template>