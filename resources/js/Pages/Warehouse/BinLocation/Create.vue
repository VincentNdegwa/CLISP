<script setup>
import { ref, onMounted, watch } from 'vue';
import { useWarehouseStore } from '@/Store/Warehouse';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import Checkbox from 'primevue/checkbox';
import FormLayout from "@/Layouts/FormLayout.vue";

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

const emit = defineEmits(['close', 'newBinLocation', 'updateBinLocation']);

const warehouseStore = useWarehouseStore();
const binLocation = ref({
    name: '',
    code: '',
    warehouse_id: props.warehouseId || null,
    description: '',
    is_active: true
});
const submitted = ref(false);
const warehouses = ref([]);
const errors = ref({});

const loadWarehouses = async () => {
    try {
        await warehouseStore.fetchWarehouses();
        warehouses.value = warehouseStore.warehouses.data || [];
    } catch (error) {
        errors.value.warehouse_id = 'Error loading warehouses';
        console.error('Error loading warehouses:', error);
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
            description: '',
            is_active: true
        };
    }
    submitted.value = false;
};

const cancel = () => {
    emit('close');
};

const save = () => {
    submitted.value = true;
    errors.value = {};
    
    // Validate required fields
    if (!binLocation.value.name) {
        errors.value.name = 'Name is required';
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

    if (props.newBinLocation) {
        emit('newBinLocation', binLocation.value);
    } else {
        emit('updateBinLocation', binLocation.value);
    }
};

// Watch for changes in the data prop
watch(() => props.data, (newVal) => {
    if (newVal) {
        binLocation.value = { ...newVal };
    } else {
        initForm();
    }
}, { deep: true });

onMounted(() => {
    loadWarehouses();
    initForm();
});

const title = props.newBinLocation ? 'Add Bin Location' : 'Edit Bin Location';
</script>

<template>
    <FormLayout>
        <template #header>
            <h2 class="text-xl font-bold mb-4">{{ title }}</h2>
        </template>
        
        <div class="space-y-4">
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
                <InputLabel value="Code" />
                <TextInput
                    v-model="binLocation.code"
                    type="text"
                    class="w-full"
                />
                <InputError :message="errors.code" />
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
                    :loading="loading"
                />
                <InputError :message="submitted && !binLocation.warehouse_id ? 'Warehouse is required' : errors.warehouse_id" />
            </div>
            
            <!-- Description Field -->
            <div>
                <InputLabel value="Description" />
                <Textarea
                    v-model="binLocation.description"
                    rows="3"
                    class="w-full"
                    autoResize
                />
                <InputError :message="errors.description" />
            </div>
            
            <!-- Active Field -->
            <div class="flex items-center">
                <Checkbox
                    v-model="binLocation.is_active"
                    :binary="true"
                />
                <span class="ml-2">Active</span>
            </div>
        </div>
        
        <div class="flex justify-end gap-2 mt-6">
            <PrimaryButton
                @click="cancel"
                :disabled="loading"
                type="button"
                variant="secondary"
                class="mr-2"
            >
                Cancel
            </PrimaryButton>
            
            <PrimaryButton
                @click="save"
                :disabled="loading"
                :loading="loading"
                type="button"
            >
                Save
            </PrimaryButton>
        </div>
    </FormLayout>
</template>