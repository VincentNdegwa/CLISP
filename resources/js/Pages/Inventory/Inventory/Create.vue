<script setup>
import { ref, onMounted, computed } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import FormLayout from "@/Layouts/FormLayout.vue";
import { useResourceStore } from "@/Store/Resource";
import { useWarehouseStore } from "@/Store/Warehouse";
import { useBinLocationStore } from "@/Store/BinLocation";
import { useInventoryStore } from "@/Store/Inventory";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";

const props = defineProps({
    newInventory: {
        type: Boolean,
        default: true,
    },
    data: {
        type: Object,
        default: null,
    },
});

const resourceStore = useResourceStore();
const warehouseStore = useWarehouseStore();
const binLocationStore = useBinLocationStore();
const inventoryStore = useInventoryStore();

// Changed emit to only include close and success
const emit = defineEmits(["close", "success"]);

const inventory = ref({
    business_id: localStorage.getItem("business_id"),
    item_id: null,
    warehouse_id: null,
    bin_location_id: null,
    batch_number: "",
    quantity: 0,
    reorder_point: 0,
    expiry_date: null,
    cost_price: 0,
    notes: "",
    status: 0, // Default to In Stock
});

const submitted = ref(false);
const formLoading = ref(false);
const items = ref([]);
const warehouses = ref([]);
const binLocations = ref([]);
const errors = ref({});

// Computed property to manage overall loading state
const loading = computed(() => {
    return (
        resourceStore.loading ||
        warehouseStore.loading ||
        binLocationStore.loading ||
        formLoading.value
    );
});

// Populate data if editing
onMounted(async () => {
    if (!props.newInventory && props.data) {
        inventory.value = {
            id: props.data.id,
            business_id: props.data.business_id,
            item_id: props.data.item_id,
            warehouse_id: props.data.warehouse_id,
            bin_location_id: props.data.bin_location_id,
            batch_number: props.data.batch_number || "",
            quantity: props.data.quantity || 0,
            reorder_point: props.data.reorder_point || 0,
            expiry_date: props.data.expiry_date
                ? new Date(props.data.expiry_date)
                : null,
            notes: props.data.notes || "",
            status: props.data.status,
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
            rows: 100,
        });

        if (resourceStore.error) {
            errors.value.item_id = resourceStore.error;
            return;
        }
        items.value = resourceStore.items.data || [];
    } catch (error) {
        errors.value.item_id = "Error fetching items";
        console.error("Error fetching items:", error);
    }
};

const fetchWarehouses = async () => {
    try {
        await warehouseStore.fetchWarehouses({
            rows: 100,
        });

        if (warehouseStore.error) {
            errors.value.warehouse_id = warehouseStore.error;
            return;
        }
        warehouses.value = warehouseStore.warehouses.data || [];
    } catch (error) {
        errors.value.warehouse_id = "Error fetching warehouses";
        console.error("Error fetching warehouses:", error);
    }
};

const fetchBinLocations = async (warehouseId) => {
    try {
        await binLocationStore.getBinLocationsByWarehouse(warehouseId, {
            rows: 100,
        });

        if (binLocationStore.error) {
            errors.value.bin_location_id = binLocationStore.error;
            return;
        }
        binLocations.value = binLocationStore.binLocations.data || [];
    } catch (error) {
        errors.value.bin_location_id = "Error fetching bin locations";
        console.error("Error fetching bin locations:", error);
    }
};

const handleWarehouseChange = async () => {
    inventory.value.bin_location_id = null; // Reset bin location when warehouse changes
    if (inventory.value.warehouse_id) {
        await fetchBinLocations(inventory.value.warehouse_id);
    }
};

const validateForm = () => {
    submitted.value = true;
    errors.value = {};

    if (!inventory.value.item_id) {
        errors.value.item_id = "Item is required";
    }

    if (!inventory.value.warehouse_id) {
        errors.value.warehouse_id = "Warehouse is required";
    }

    if (!inventory.value.bin_location_id) {
        errors.value.bin_location_id = "Bin location is required";
    }

    if (inventory.value.quantity < 0) {
        errors.value.quantity = "Quantity must be 0 or greater";
    }

    if (inventory.value.reorder_point < 0) {
        errors.value.reorder_point = "Reorder point must be 0 or greater";
    }

    return Object.keys(errors.value).length === 0;
};

// Modified to handle API calls directly
const saveInventory = async () => {
    if (!validateForm()) {
        return;
    }

    formLoading.value = true;
    try {
        let result;

        if (props.newInventory) {
            result = await inventoryStore.addInventoryItem(inventory.value);
            if (result && !inventoryStore.error) {
                inventoryStore.success = "Inventory added successfully";
                emit("success", "created", result);
                emit("close");
            }
        } else {
            result = await inventoryStore.updateInventoryItem(inventory.value);
            if (result && !inventoryStore.error) {
                inventoryStore.success = "Inventory updated successfully";
                emit("success", "updated", result);
                emit("close");
            }
        }
    } catch (error) {
        console.error("Error saving inventory:", error);
        inventoryStore.error = props.newInventory
            ? "Failed to add inventory"
            : "Failed to update inventory";
    } finally {
        formLoading.value = false;
    }
};

const isFormValid = computed(() => {
    return (
        inventory.value.item_id &&
        inventory.value.warehouse_id &&
        inventory.value.bin_location_id &&
        inventory.value.quantity >= 0
    );
});

const title = computed(() => {
    return props.newInventory ? "Add New Inventory" : "Edit Inventory";
});

const statusOptions = [
    { label: "In Stock", value: 0 },
    { label: "Low Stock", value: 1 },
    { label: "Out of Stock", value: 2 },
    { label: "Reserved", value: 3 },
    { label: "Damaged", value: 4 },
    { label: "Expired", value: 5 },
];

onMounted(() => {
    // resourceStore.clearErrors();
    warehouseStore.clearErrors();
    binLocationStore.clearErrors();

    return () => {
        // resourceStore.clearErrors();
        warehouseStore.clearErrors();
        binLocationStore.clearErrors();
    };
});
</script>

<template>
    <FormLayout :title="title">
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
                    :loading="resourceStore.loading"
                />
                <InputError
                    :message="
                        submitted && !inventory.item_id
                            ? 'Item is required'
                            : errors.item_id
                    "
                />
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
                    :loading="warehouseStore.loading"
                />
                <InputError
                    :message="
                        submitted && !inventory.warehouse_id
                            ? 'Warehouse is required'
                            : errors.warehouse_id
                    "
                />
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
                    :disabled="!inventory.warehouse_id || !newInventory"
                    :loading="binLocationStore.loading"
                />
                <InputError
                    :message="
                        submitted && !inventory.bin_location_id
                            ? 'Bin location is required'
                            : errors.bin_location_id
                    "
                />
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
                <InputError
                    :message="
                        submitted && inventory.quantity < 0
                            ? 'Quantity must be 0 or greater'
                            : errors.quantity
                    "
                />
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
                <InputError
                    :message="
                        submitted && inventory.reorder_point < 0
                            ? 'Reorder point must be 0 or greater'
                            : errors.reorder_point
                    "
                />
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
            <Textarea v-model="inventory.notes" rows="3" class="w-full" />
            <InputError :message="errors.notes" />
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <PrimaryButton
                @click="$emit('close')"
                :disabled="formLoading"
                type="button"
                class="mr-2"
            >
            Cancel
            </PrimaryButton>
            <PrimaryRoseButton
                @click="save"
                :disabled="!isFormValid || formLoading"
                :loading="formLoading || props.loading"
                type="button"
            >
                {{
                    formLoading
                        ? "Saving..."
                        : props.newInventory
                        ? "Add Inventory"
                        : "Update Inventory"
                }}
            </PrimaryRoseButton>
        </div>
    </FormLayout>
</template>
