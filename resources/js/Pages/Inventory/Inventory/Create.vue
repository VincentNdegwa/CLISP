<script setup>
import { ref, onMounted, computed, watch } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import Select from "primevue/select";
import InputNumber from "primevue/inputnumber";
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
    status: {
        type: Array,
        default: () => [],
    }
});

const resourceStore = useResourceStore();
const warehouseStore = useWarehouseStore();
const binLocationStore = useBinLocationStore();
const inventoryStore = useInventoryStore();

const emit = defineEmits(["close", "success"]);

const inventory = ref({
    item_id: null,
    warehouse_id: null,
    bin_location_id: null,
    quantity: 0,
    min_stock_level: null,
    max_stock_level: null,
    reorder_point: null,
    status: 0, 
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
            quantity: parseFloat(props.data.quantity) || 0,
            min_stock_level: props.data.min_stock_level !== null ? parseFloat(props.data.min_stock_level) : null,
            max_stock_level: props.data.max_stock_level !== null ? parseFloat(props.data.max_stock_level) : null,
            reorder_point: props.data.reorder_point !== null ? parseFloat(props.data.reorder_point) : null,
            status: props.data.status || 'active',
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
        items.value = resourceStore.items?.data || [];
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
        warehouses.value = warehouseStore.warehouses?.data || [];
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
        binLocations.value = binLocationStore.binLocations?.data || [];
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

    if (typeof inventory.value.quantity !== 'number' || inventory.value.quantity < 0) {
        errors.value.quantity = "Quantity must be 0 or greater";
    }

    if (inventory.value.min_stock_level !== null && inventory.value.min_stock_level < 0) {
        errors.value.min_stock_level = "Minimum stock level must be 0 or greater";
    }

    if (inventory.value.max_stock_level !== null && inventory.value.max_stock_level < 0) {
        errors.value.max_stock_level = "Maximum stock level must be 0 or greater";
    }

    if (inventory.value.reorder_point !== null && inventory.value.reorder_point < 0) {
        errors.value.reorder_point = "Reorder point must be 0 or greater";
    }

    if (inventory.value.min_stock_level !== null && 
        inventory.value.max_stock_level !== null && 
        inventory.value.min_stock_level > inventory.value.max_stock_level) {
        errors.value.max_stock_level = "Maximum stock level must be greater than minimum stock level";
    }

    if (!(inventory.value.status>=0)) {
        errors.value.status = "Status is required";
    }

    return Object.keys(errors.value).length === 0;
};

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
        inventory.value.quantity >= 0 &&
        inventory.value.status>=0 &&
        !formLoading.value
    );
});

const title = computed(() => {
    return props.newInventory ? "Add New Inventory" : "Edit Inventory";
});

const statusOptions = computed(() => {
    return props.status.filter(option => option.value != null);
})

onMounted(() => {
    // Clear errors from stores
    warehouseStore.clearErrors();
    binLocationStore.clearErrors();

    return () => {
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

            <!-- Quantity -->
            <div>
                <InputLabel value="Quantity" required="true" />
                <InputNumber
                    v-model="inventory.quantity"
                    :min="0"
                    :step="1"
                    class="w-full"
                    :minFractionDigits="2"
                    :maxFractionDigits="2"
                    showButtons
                />
                <InputError
                    :message="
                        submitted && (inventory.quantity === null || inventory.quantity < 0)
                            ? 'Quantity must be 0 or greater'
                            : errors.quantity
                    "
                />
            </div>

            <!-- Min Stock Level -->
            <div>
                <InputLabel value="Minimum Stock Level" />
                <InputNumber
                    v-model="inventory.min_stock_level"
                    :min="0"
                    :step="1"
                    class="w-full"
                    :minFractionDigits="2"
                    :maxFractionDigits="2"
                    showButtons
                />
                <InputError :message="errors.min_stock_level" />
            </div>

            <!-- Max Stock Level -->
            <div>
                <InputLabel value="Maximum Stock Level" />
                <InputNumber
                    v-model="inventory.max_stock_level"
                    :min="0"
                    :step="1"
                    class="w-full"
                    :minFractionDigits="2"
                    :maxFractionDigits="2"
                    showButtons
                />
                <InputError :message="errors.max_stock_level" />
            </div>

            <!-- Reorder Point -->
            <div>
                <InputLabel value="Reorder Point" />
                <InputNumber
                    v-model="inventory.reorder_point"
                    :min="0"
                    :step="1"
                    class="w-full"
                    :minFractionDigits="2"
                    :maxFractionDigits="2"
                    showButtons
                />
                <InputError :message="errors.reorder_point" />
            </div>

            <!-- Status -->
            <div>
                <InputLabel value="Status" required="true" />
                <Select
                    v-model="inventory.status"
                    :options="statusOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Select Status"
                    class="w-full"
                />
                <InputError
                    :message=" errors.status"
                />
            </div>
        </div>

        <div v-if="inventoryStore.error" class="p-3 bg-red-50 text-red-700 rounded mb-4">
            {{ inventoryStore.error }}
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
                @click="saveInventory"
                :disabled="!isFormValid || formLoading"
                :loading="formLoading"
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