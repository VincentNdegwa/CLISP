<script setup>
import { ref, onMounted, computed, watch } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import Select from "primevue/select";
import InputNumber from "primevue/inputnumber";
import Textarea from "primevue/textarea";
import Calendar from "primevue/calendar";
import FormLayout from "@/Layouts/FormLayout.vue";
import { useResourceStore } from "@/Store/Resource";
import { useSupplierStore } from "@/Store/SupplierStore";
import { usePurchaseOrderStore } from "@/Store/PurchaseOrderStore";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import DataTable from "primevue/datatable";
import Column  from "primevue/column";
import Dialog from "primevue/dialog";
import Button from "primevue/button";

const props = defineProps({
    newPO: {
        type: Boolean,
        default: true,
    },
    data: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["close", "success"]);

const resourceStore = useResourceStore();
const supplierStore = useSupplierStore();
const purchaseOrderStore = usePurchaseOrderStore();

const purchaseOrder = ref({
    supplier_id: null,
    po_number: "",
    order_date: new Date(),
    expected_delivery: null,
    notes: "",
    status: "draft",
    items: [],
});

const newItem = ref({
    item_id: null,
    quantity: 1,
    unit_price: 0,
    total_price: 0,
});

const itemDialog = ref(false);
const editingItemIndex = ref(-1);
const submitted = ref(false);
const formLoading = ref(false);
const items = ref([]);
const suppliers = ref([]);
const errors = ref({});

// Computed property to manage overall loading state
const loading = computed(() => {
    return (
        resourceStore.loading ||
        supplierStore.loading ||
        purchaseOrderStore.loading ||
        formLoading.value
    );
});

// Populate data if editing
onMounted(async () => {
    if (!props.newPO && props.data) {
        purchaseOrder.value = {
            id: props.data.id,
            supplier_id: props.data.supplier_id,
            po_number: props.data.po_number,
            order_date: new Date(props.data.order_date),
            expected_delivery: props.data.expected_delivery ? new Date(props.data.expected_delivery) : null,
            notes: props.data.notes || "",
            status: props.data.status,
            items: props.data.items?.map(item => ({
                id: item.id,
                item_id: item.item_id,
                quantity: parseFloat(item.quantity),
                unit_price: parseFloat(item.unit_price),
                total_price: parseFloat(item.total_price),
                item_name: item.item?.item_name || "",
            })) || [],
        };
    } else {
        // Generate a unique PO Number if creating new PO
        purchaseOrder.value.po_number = generatePONumber();
    }

    await fetchItems();
    await fetchSuppliers();
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

const fetchSuppliers = async () => {
    try {
        await supplierStore.fetchSuppliers({
            rows: 100,
        });

        if (supplierStore.error) {
            errors.value.supplier_id = supplierStore.error;
            return;
        }
        suppliers.value = supplierStore.suppliers?.data || [];
    } catch (error) {
        errors.value.supplier_id = "Error fetching suppliers";
        console.error("Error fetching suppliers:", error);
    }
};

const generatePONumber = () => {
    const now = new Date();
    const year = now.getFullYear().toString().substr(-2);
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const random = Math.floor(1000 + Math.random() * 9000);
    return `PO-${year}${month}${day}-${random}`;
};

const openItemDialog = () => {
    newItem.value = {
        item_id: null,
        quantity: 1,
        unit_price: 0,
        total_price: 0,
    };
    editingItemIndex.value = -1;
    itemDialog.value = true;
};

const editItem = (item, index) => {
    newItem.value = { ...item };
    editingItemIndex.value = index;
    itemDialog.value = true;
};

const removeItem = (index) => {
    purchaseOrder.value.items.splice(index, 1);
    calculateTotals();
};

const saveItem = () => {
    if (!validateItem()) {
        return;
    }

    // Calculate total price
    newItem.value.total_price = newItem.value.quantity * newItem.value.unit_price;
    
    // Find the item name
    const selectedItem = items.value.find(item => item.id === newItem.value.item_id);
    newItem.value.item_name = selectedItem ? selectedItem.item_name : "";
    
    if (editingItemIndex.value === -1) {
        // Add new item
        purchaseOrder.value.items.push({ ...newItem.value });
    } else {
        // Update existing item
        purchaseOrder.value.items[editingItemIndex.value] = { ...newItem.value };
    }
    
    calculateTotals();
    itemDialog.value = false;
};

const calculateTotals = () => {
    // Calculate subtotal, tax, and total for the purchase order
    const subtotal = purchaseOrder.value.items.reduce((sum, item) => sum + item.total_price, 0);
    purchaseOrder.value.subtotal = subtotal;
    // Could add tax calculation here if needed
    purchaseOrder.value.total = subtotal;
};

const validateItem = () => {
    const itemErrors = {};
    
    if (!newItem.value.item_id) {
        itemErrors.item_id = 'Item is required';
    }
    
    if (!newItem.value.quantity || newItem.value.quantity <= 0) {
        itemErrors.quantity = 'Quantity must be greater than 0';
    }
    
    if (newItem.value.unit_price < 0) {
        itemErrors.unit_price = 'Price cannot be negative';
    }
    
    if (Object.keys(itemErrors).length > 0) {
        errors.value = { ...errors.value, ...itemErrors };
        return false;
    }
    
    return true;
};

const validateForm = () => {
    submitted.value = true;
    errors.value = {};

    if (!purchaseOrder.value.supplier_id) {
        errors.value.supplier_id = "Supplier is required";
    }

    if (!purchaseOrder.value.po_number) {
        errors.value.po_number = "PO number is required";
    }

    if (!purchaseOrder.value.order_date) {
        errors.value.order_date = "Order date is required";
    }

    if (purchaseOrder.value.items.length === 0) {
        errors.value.items = "At least one item is required";
    }

    return Object.keys(errors.value).length === 0;
};

const savePurchaseOrder = async () => {
    if (!validateForm()) {
        return;
    }

    formLoading.value = true;
    try {
        let result;

        if (props.newPO) {
            result = await purchaseOrderStore.createPurchaseOrder(purchaseOrder.value);
            if (result && !purchaseOrderStore.error) {
                purchaseOrderStore.success = "Purchase order created successfully";
                emit("success", "created", result);
                emit("close");
            }
        } else {
            result = await purchaseOrderStore.updatePurchaseOrder(purchaseOrder.value);
            if (result && !purchaseOrderStore.error) {
                purchaseOrderStore.success = "Purchase order updated successfully";
                emit("success", "updated", result);
                emit("close");
            }
        }
    } catch (error) {
        console.error("Error saving purchase order:", error);
        purchaseOrderStore.error = props.newPO
            ? "Failed to create purchase order"
            : "Failed to update purchase order";
    } finally {
        formLoading.value = false;
    }
};

const isFormValid = computed(() => {
    return (
        purchaseOrder.value.supplier_id &&
        purchaseOrder.value.po_number &&
        purchaseOrder.value.order_date &&
        purchaseOrder.value.items.length > 0 &&
        !formLoading.value
    );
});

const title = computed(() => {
    return props.newPO ? "Create Purchase Order" : "Edit Purchase Order";
});

// Calculate total price when quantity or unit price changes
watch(() => [newItem.value.quantity, newItem.value.unit_price], () => {
    newItem.value.total_price = newItem.value.quantity * newItem.value.unit_price;
}, { deep: true });

onMounted(() => {
    // Clear errors from stores
    resourceStore.clearErrors();
    supplierStore.clearErrors();
    purchaseOrderStore.clearErrors();

    return () => {
        resourceStore.clearErrors();
        supplierStore.clearErrors();
        purchaseOrderStore.clearErrors();
    };
});
</script>

<template>
    <FormLayout :title="title">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <!-- Supplier -->
            <div>
                <InputLabel value="Supplier" required="true" />
                <Select
                    v-model="purchaseOrder.supplier_id"
                    :options="suppliers"
                    optionLabel="name"
                    optionValue="id"
                    :loading="supplierStore.loading"
                    placeholder="Select a supplier"
                    class="w-full"
                />
                <InputError :message="errors.supplier_id" />
            </div>

            <!-- PO Number -->
            <div>
                <InputLabel value="PO Number" required="true" />
                <TextInput
                    v-model="purchaseOrder.po_number"
                    type="text"
                    class="w-full"
                    :disabled="!props.newPO" /> <!-- Disable editing PO number when updating -->
                />
                <InputError :message="errors.po_number" />
            </div>

            <!-- Order Date -->
            <div>
                <InputLabel value="Order Date" required="true" />
                <Calendar
                    v-model="purchaseOrder.order_date"
                    dateFormat="mm/dd/yy"
                    class="w-full"
                />
                <InputError :message="errors.order_date" />
            </div>

            <!-- Expected Delivery -->
            <div>
                <InputLabel value="Expected Delivery Date" />
                <Calendar
                    v-model="purchaseOrder.expected_delivery"
                    dateFormat="mm/dd/yy"
                    class="w-full"
                />
                <InputError :message="errors.expected_delivery" />
            </div>

            <!-- Notes -->
            <div class="col-span-2">
                <InputLabel value="Notes" />
                <Textarea
                    v-model="purchaseOrder.notes"
                    rows="3"
                    class="w-full"
                />
                <InputError :message="errors.notes" />
            </div>
        </div>

        <!-- Items Section -->
        <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-semibold">Order Items</h3>
                <Button 
                    label="Add Item" 
                    icon="pi pi-plus" 
                    @click="openItemDialog"
                />
            </div>
            
            <InputError :message="errors.items" class="mb-2" />

            <DataTable
                :value="purchaseOrder.items"
                class="p-datatable-sm"
                responsiveLayout="scroll"
                :paginator="purchaseOrder.items.length > 10"
                :rows="10"
                rowHover
                stripedRows
            >
                <Column field="item_name" header="Item"></Column>
                <Column field="quantity" header="Quantity"></Column>
                <Column field="unit_price" header="Unit Price">
                    <template #body="slotProps">
                        {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(slotProps.data.unit_price) }}
                    </template>
                </Column>
                <Column field="total_price" header="Total">
                    <template #body="slotProps">
                        {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(slotProps.data.total_price) }}
                    </template>
                </Column>
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-pencil"
                                text
                                rounded
                                severity="secondary"
                                @click="editItem(slotProps.data, slotProps.index)"
                            />
                            <Button
                                icon="pi pi-trash"
                                text
                                rounded
                                severity="danger"
                                @click="removeItem(slotProps.index)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Total section -->
            <div class="flex justify-end mt-4">
                <div class="w-1/3">
                    <div class="flex justify-between py-2">
                        <span class="font-semibold">Subtotal:</span>
                        <span>{{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(purchaseOrder.items.reduce((sum, item) => sum + item.total_price, 0)) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-t border-gray-200">
                        <span class="font-semibold">Total:</span>
                        <span class="font-bold">{{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(purchaseOrder.items.reduce((sum, item) => sum + item.total_price, 0)) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="purchaseOrderStore.error" class="p-3 bg-red-50 text-red-700 rounded mb-4">
            {{ purchaseOrderStore.error }}
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
                @click="savePurchaseOrder"
                :disabled="!isFormValid || formLoading"
                :loading="formLoading"
                type="button"
            >
                {{ props.newPO ? 'Create Purchase Order' : 'Update Purchase Order' }}
            </PrimaryRoseButton>
        </div>
    </FormLayout>

    <!-- Item Dialog -->
    <Dialog
        v-model:visible="itemDialog"
        :header="editingItemIndex === -1 ? 'Add Item' : 'Edit Item'"
        :modal="true"
        :closable="false"
        class="p-fluid w-full max-w-lg"
    >
        <div class="grid grid-cols-1 gap-4 mb-4">
            <div>
                <InputLabel value="Item" required="true" />
                <Select
                    v-model="newItem.item_id"
                    :options="items"
                    optionLabel="item_name"
                    optionValue="id"
                    :loading="resourceStore.loading"
                    placeholder="Select an item"
                    class="w-full"
                />
                <InputError :message="errors.item_id" />
            </div>
            <div>
                <InputLabel value="Quantity" required="true" />
                <InputNumber
                    v-model="newItem.quantity"
                    showButtons
                    :min="0"
                    class="w-full"
                />
                <InputError :message="errors.quantity" />
            </div>
            <div>
                <InputLabel value="Unit Price" required="true" />
                <InputNumber
                    v-model="newItem.unit_price"
                    mode="currency"
                    currency="USD"
                    locale="en-US"
                    :min="0"
                    class="w-full"
                />
                <InputError :message="errors.unit_price" />
            </div>
            <div>
                <InputLabel value="Total Price" />
                <InputNumber
                    v-model="newItem.total_price"
                    mode="currency"
                    currency="USD"
                    locale="en-US"
                    :min="0"
                    class="w-full"
                    disabled
                />
            </div>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button
                    label="Cancel"
                    icon="pi pi-times"
                    text
                    @click="itemDialog = false"
                />
                <Button
                    label="Save"
                    icon="pi pi-check"
                    @click="saveItem"
                />
            </div>
        </template>
    </Dialog>
</template>
