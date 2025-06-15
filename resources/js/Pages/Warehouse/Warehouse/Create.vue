<script setup>
import { ref, onMounted, watch } from 'vue';
import { useWarehouseStore } from '@/Store/Warehouse';
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import Textarea from "primevue/textarea";
import Checkbox from 'primevue/checkbox';
import FormLayout from "@/Layouts/FormLayout.vue";
import PrimaryRoseButton from '@/Components/PrimaryRoseButton.vue';

const props = defineProps({
    newWarehouse: {
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
});

const emit = defineEmits(['close', 'success']);

const warehouseStore = useWarehouseStore();
const warehouse = ref({
    name: '',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
    phone: '',
    email: '',
    is_active: true,
    notes: ''
});
const submitted = ref(false);
const errors = ref({});
const formLoading = ref(false);

const initForm = () => {
    if (props.data) {
        warehouse.value = { ...props.data };
    } else {
        warehouse.value = {
            name: '',
            address: '',
            city: '',
            state: '',
            postal_code: '',
            country: '',
            phone: '',
            email: '',
            code: '',
            is_active: true,
            notes: ''
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
    if (!warehouse.value.name) {
        errors.value.name = 'Name is required';
    }
    if (!warehouse.value.address) {
        errors.value.address = 'Address is required';
    }
    if (!warehouse.value.city) {
        errors.value.city = 'City is required';
    }
    if (!warehouse.value.state) {
        errors.value.state = 'State is required';
    }
    if (!warehouse.value.postal_code) {
        errors.value.postal_code = 'Postal code is required';
    }
    if (!warehouse.value.country) {
        errors.value.country = 'Country is required';
    }
    
    // If there are validation errors, don't submit
    if (Object.keys(errors.value).length > 0) {
        return;
    }

    formLoading.value = true;
    try {
        let result;
        
        if (props.newWarehouse) {
            // Create new warehouse
            result = await warehouseStore.createWarehouse(warehouse.value);
            if (result) {
                warehouseStore.success = "Warehouse created successfully";
                emit('success', 'created', result);
                emit('close');
            }
        } else {
            // Update existing warehouse
            result = await warehouseStore.updateWarehouse(warehouse.value);
            if (result) {
                warehouseStore.success = "Warehouse updated successfully";
                emit('success', 'updated', result);
                emit('close');
            }
        }
    } catch (error) {
        console.error("Error saving warehouse:", error);
        warehouseStore.error = props.newWarehouse 
            ? "Failed to create warehouse" 
            : "Failed to update warehouse";
    } finally {
        formLoading.value = false;
    }
};

// Watch for changes in the data prop
watch(() => props.data, (newVal) => {
    if (newVal) {
        warehouse.value = { ...newVal };
    } else {
        initForm();
    }
}, { deep: true });

onMounted(() => {
    initForm();
});

const title = props.newWarehouse ? 'Add Warehouse' : 'Edit Warehouse';
</script>

<template>
    <FormLayout :title="title" >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name Field -->
            <div>
                <InputLabel value="Name" required="true" />
                <TextInput
                    v-model="warehouse.name"
                    type="text"
                    class="w-full"
                    :disabled="formLoading || props.loading"
                    autofocus
                />
                <InputError :message="submitted && !warehouse.name ? 'Name is required' : errors.name" />
            </div>
            
            <!-- Address Field -->
            <div>
                <InputLabel value="Address" required="true" />
                <TextInput
                    v-model="warehouse.address"
                    type="text"
                    class="w-full"
                    :disabled="formLoading || props.loading"
                />
                <InputError :message="submitted && !warehouse.address ? 'Address is required' : errors.address" />
            </div>
            
            <!-- City Field -->
            <div>
                <InputLabel value="City" required="true" />
                <TextInput
                    v-model="warehouse.city"
                    type="text"
                    class="w-full"
                    :disabled="formLoading || props.loading"
                />
                <InputError :message="submitted && !warehouse.city ? 'City is required' : errors.city" />
            </div>
            
            <!-- State Field -->
            <div>
                <InputLabel value="State" required="true" />
                <TextInput
                    v-model="warehouse.state"
                    type="text"
                    class="w-full"
                    :disabled="formLoading || props.loading"
                />
                <InputError :message="submitted && !warehouse.state ? 'State is required' : errors.state" />
            </div>
            
            <!-- Postal Code Field -->
            <div>
                <InputLabel value="Postal Code" required="true" />
                <TextInput
                    v-model="warehouse.postal_code"
                    type="text"
                    class="w-full"
                    :disabled="formLoading || props.loading"
                />
                <InputError :message="submitted && !warehouse.postal_code ? 'Postal code is required' : errors.postal_code" />
            </div>
            
            <!-- Country Field -->
            <div>
                <InputLabel value="Country" required="true" />
                <TextInput
                    v-model="warehouse.country"
                    type="text"
                    class="w-full"
                    :disabled="formLoading || props.loading"
                />
                <InputError :message="submitted && !warehouse.country ? 'Country is required' : errors.country" />
            </div>
            
            <!-- Phone Field -->
            <div>
                <InputLabel value="Phone" />
                <TextInput
                    v-model="warehouse.phone"
                    type="tel"
                    class="w-full"
                    :disabled="formLoading || props.loading"
                />
                <InputError :message="errors.phone" />
            </div>
            
            <!-- Email Field -->
            <div>
                <InputLabel value="Email" />
                <TextInput
                    v-model="warehouse.email"
                    type="email"
                    class="w-full"
                    :disabled="formLoading || props.loading"
                />
                <InputError :message="errors.email" />
            </div>
            <!-- Code Field -->
            <div>
                <InputLabel value="Code" />
                <TextInput
                    v-model="warehouse.code"
                    type="text"
                    class="w-full"
                    :disabled="formLoading || props.loading"
                />
                <InputError :message="errors.code" />
            </div>
        </div>
        
        <!-- Notes Field -->
        <div class="mt-4">
            <InputLabel value="Notes" />
            <Textarea
                v-model="warehouse.notes"
                rows="3"
                class="w-full"
                :disabled="formLoading || props.loading"
                autoResize
            />
            <InputError :message="errors.notes" />
        </div>
        
        <!-- Active Field -->
        <div class="flex items-center mt-4">
            <Checkbox
                v-model="warehouse.is_active"
                :binary="true"
                :disabled="formLoading || props.loading"
            />
            <span class="ml-2">Active</span>
        </div>
        
        <div class="flex justify-end gap-2 mt-6">
            <PrimaryButton
                @click="cancel"
                :disabled="formLoading || props.loading"
                type="button"
                variant="secondary"
                class="mr-2"
            >
                Cancel
            </PrimaryButton>
            
            <PrimaryRoseButton
                @click="save"
                :disabled="formLoading || props.loading"
                :loading="formLoading || props.loading"
                type="button"
            >
                {{  props.loading || formLoading ? 'Saving...' : (props.newWarehouse ? 'Create Warehouse' : 'Update Warehouse') }}
            </PrimaryRoseButton>
        </div>
    </FormLayout>
</template>