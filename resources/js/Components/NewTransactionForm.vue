<template>
    <div class="p-1 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-50 h-fit relative">
        <div class="text-2xl font-bold ms-6 mt-2 capitalize">
            {{
                newTransaction == true
                    ? `New ${transactionType}`
                    : `Update ${transactionType}`
            }}
        </div>
        <form
            @submit.prevent="validateAndSubmit"
            class="space-y-8 p-6 shadow-lg rounded-lg"
        >
            <!-- Row 2: Initiator and Receiver -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-if="isB2B">
                    <InputLabel
                        for="receiver_business_id"
                        value="Receiver Business "
                        :required="isB2B"
                    />

                    <Select
                        v-model="form.receiver_business_id"
                        :options="business"
                        :optionLabel="(option) => option.business_name"
                        :optionValue="(option) => option.business_id"
                        placeholder="Select Connected Business"
                        id="receiver_business_id"
                        :required="isB2B"
                        :class="{'p-invalid': validationErrors.receiver_business_id}"
                    />
                    <small v-if="validationErrors.receiver_business_id" class="p-error text-red-500">
                        {{ validationErrors.receiver_business_id }}
                    </small>
                </div>

                <div v-if="!isB2B">
                    <InputLabel
                        for="receiver_customer_id"
                        value="Receiver Customer "
                        :required="!isB2B"
                    />

                    <Select
                        v-model="form.receiver_customer_id"
                        :options="customer"
                        :optionLabel="(option) => option.full_names"
                        :optionValue="(option) => option.id"
                        placeholder="Select Connected Customer"
                        id="receiver_customer_id"
                        :required="!isB2B"
                        :class="{'p-invalid': validationErrors.receiver_customer_id}"
                    />
                    <small v-if="validationErrors.receiver_customer_id" class="p-error text-red-500">
                        {{ validationErrors.receiver_customer_id }}
                    </small>
                </div>
            </div>

            <!-- Row 3: Receiver Customer and Lease Dates -->
            <div
                class="flex gap-1"
                v-if="tr_with_dates.includes(transactionType)"
            >
                <div class="w-full">
                    <InputLabel
                        for="lease_start_date"
                        value="Lease Start Date"
                    />
                    <DatePicker
                        v-model="form.lease_start_date"
                        showIcon
                        fluid
                        iconDisplay="input"
                        dateFormat="yy-mm-dd"
                        :minDate="new Date()"
                        :class="{'p-invalid': validationErrors.lease_start_date}"
                    />
                    <small v-if="validationErrors.lease_start_date" class="p-error text-red-500">
                        {{ validationErrors.lease_start_date }}
                    </small>
                </div>

                <!-- Lease End Date -->
                <div class="w-full">
                    <InputLabel for="lease_end_date" value="Lease End Date" />
                    <DatePicker
                        v-model="form.lease_end_date"
                        showIcon
                        fluid
                        iconDisplay="input"
                        id="lease_end_date"
                        dateFormat="yy-mm-dd"
                        :minDate="new Date(form.lease_start_date)"
                        :class="{'p-invalid': validationErrors.lease_end_date}"
                    />
                    <small v-if="validationErrors.lease_end_date" class="p-error text-red-500">
                        {{ validationErrors.lease_end_date }}
                    </small>
                </div>
            </div>

            <!-- Transaction Items Section -->
            <div class="p-2 rounded-lg shadow-md">
                <div class="flex justify-between">
                    <h3 class="text-lg font-bold mb-4">Transaction Items</h3>
                    <div class="text-right">
                        <button
                            type="button"
                            @click="addItem"
                            class="btn bg-slate-900 text-white btn-sm"
                        >
                            Add Another Item
                        </button>
                    </div>
                </div>
                <div v-if="validationErrors.transaction_items" class="mb-2 p-2 bg-red-100 dark:bg-red-900/20 text-red-500 dark:text-red-400 rounded-md">
                    {{ validationErrors.transaction_items }}
                </div>
                <div
                    v-for="(item, index) in form.transaction_items"
                    :key="index"
                    class="w-full flex flex-col md:flex-row gap-2 md:gap-4 items-start mt-2 pb-2 border-b dark:border-slate-700 last:border-0"
                >
                    <!-- Item ID -->
                    <div class="w-full md:w-fit">
                        <InputLabel
                            :for="`item_id_${index}`"
                            value="Item"
                            :required="true"
                            class="w-full"
                        />
                        <Select
                            v-model="item.item_id"
                            :options="options"
                            :optionLabel="(option) => option.item_name"
                            :optionValue="(option) => option.id"
                            placeholder="Search Item..."
                            @update:modelValue="selectChange"
                            filter
                            @filter="selectSearch"
                            :class="{'p-invalid': getItemError(index, 'item_id')}"
                        />
                        <small v-if="getItemError(index, 'item_id')" class="p-error text-red-500">
                            Item is required
                        </small>
                    </div>

                    <!-- Quantity -->
                    <div class="md:w-[100px] md:max-w-fit w-full">
                        <InputLabel
                            :for="`quantity_${index}`"
                            value="Quantity"
                            class="w-full"
                            :required="true"
                        />
                        <TextInput
                            v-model="item.quantity"
                            :id="`quantity_${index}`"
                            type="number"
                            class="w-full"
                            min="0.01"
                            step="0.01"
                            required
                            :class="{'border-red-500': getItemError(index, 'quantity')}"
                        />
                        <small v-if="getItemError(index, 'quantity')" class="p-error text-red-500">
                            Quantity must be greater than 0
                        </small>
                    </div>

                    <!-- Price -->
                    <div class="md:w-[150px] w-full md:max-w-fit">
                        <InputLabel
                            :for="`price_${index}`"
                            value="Unit Price"
                            class="w-full"
                            :required="true"
                        />
                        <TextInput
                            v-model="item.price"
                            :id="`price_${index}`"
                            type="number"
                            class="w-full"
                            min="0"
                            step="0.01"
                            required
                            :class="{'border-red-500': getItemError(index, 'price')}"
                        />
                        <small v-if="getItemError(index, 'price')" class="p-error text-red-500">
                            Price must be valid
                        </small>
                    </div>

                    <!-- Remove Item Button -->
                    <div class="mt-7">
                        <button
                            type="button"
                            @click="removeItem(index)"
                            class="btn btn-outline btn-sm bg-rose-500 text-white hover:bg-rose-700"
                        >
                            Remove Item
                        </button>
                    </div>
                </div>
            </div>

            <!-- Transaction Details Section -->
            <div
                class="bg-slate-100 dark:bg-slate-700/40 p-4 rounded-lg shadow-md"
                v-if="tr_with_dates.includes(transactionType)"
            >
                <h3 class="text-lg font-bold mb-4">Transaction Details</h3>

                <div class="flex flex-col md:flex-row gap-2 mt-8">
                    <!-- Late Fees -->
                    <div class="flex-1 w-full">
                        <InputLabel for="late_fees" value="Late Fees" />
                        <TextInput
                            v-model="form.transaction_details.late_fees"
                            type="number"
                            min="0"
                            step="0.01"
                            id="late_fees"
                            class="w-full"
                        />
                    </div>

                    <!-- Damage Fees -->
                    <div class="flex-1 w-full">
                        <InputLabel for="damage_fees" value="Damage Fees" />
                        <TextInput
                            v-model="form.transaction_details.damage_fees"
                            type="number"
                            min="0"
                            step="0.01"
                            id="damage_fees"
                            class="w-full"
                        />
                    </div>

                    <!-- Shipping Fees -->
                    <div class="flex-1 w-full">
                        <InputLabel for="shipping_fees" value="Shipping Fees" />
                        <TextInput
                            v-model="form.transaction_details.shipping_fees"
                            type="number"
                            min="0"
                            step="0.01"
                            id="shipping_fees"
                            class="w-full"
                        />
                    </div>
                </div>
            </div>

            <!-- Form Errors Summary -->
            <div v-if="hasValidationErrors" class="p-3 bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-md">
                <p class="font-medium">Please fix the following errors:</p>
                <ul class="list-disc pl-5 mt-1">
                    <li v-for="(error, field) in validationErrors" :key="field" v-show="error && typeof error === 'string'">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <!-- Submit Button -->
            <div class="text-right flex gap-1">
                <PrimaryButton
                    class="flex-1"
                    type="submit"
                    :disabled="transactionStore.loading || loadingClose"
                >
                  {{ transactionStore.loading || loadingClose ? "Loading..." : newTransaction ? "Submit" : "Update" }}
                </PrimaryButton>
                <PrimaryRoseButton
                    :disabled="transactionStore.loading || loadingClose"
                    @click="closeForm"
                    class="flex-1"
                    type="button"
                    >Cancel</PrimaryRoseButton
                >
            </div>
        </form>
    </div>
    <AlertNotification
        :open="
            transactionStore.success != null || transactionStore.error != null
        "
        :message="
            transactionStore.success != null
                ? transactionStore.success
                : '' || transactionStore.error != null
                ? transactionStore.error
                : ''
        "
        :status="transactionStore.success ? 'success' : 'error'"
    />
</template>

<script>
import { ref, watch, computed } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryRoseButton from "./PrimaryRoseButton.vue";
import vSelect from "vue-select";
import { useResourceStore } from "@/Store/Resource";
import { useTransactionStore } from "@/Store/TransactionStore";
import DatePicker from "primevue/datepicker";
import Select from "primevue/select";

export default {
    emits: ["closeMe"],

    props: {
        initiatorBusiness: {
            type: Number,
            required: true,
        },
        business: {
            type: Array,
            required: true,
        },
        customer: {
            type: Array,
            required: true,
        },
        transactionType: {
            type: String,
            required: true,
        },
        products: {
            type: Array,
            required: true,
        },
        isB2B: {
            type: Boolean,
            required: true,
        },
        newTransaction: {
            type: Boolean,
            default: true,
        },
        transactionData: {
            type: Object || {},
            default: null,
            required: false,
        },
    },
    components: {
        InputLabel,
        PrimaryButton,
        TextInput,
        PrimaryRoseButton,
        vSelect,
        DatePicker,
        Select,
    },

    setup(props, { emit }) {
        const resourceStore = useResourceStore();
        const transactionStore = useTransactionStore();
        const loadingClose = ref(false);
        const validationErrors = ref({});
        const itemErrors = ref([]);

        const form = ref({
            type: props.transactionType,
            status: "pending",
            initiator_id: props.initiatorBusiness,
            receiver_business_id: null,
            receiver_customer_id: null,
            transaction_items: [{ item_id: null, quantity: "1", price: "0" }],
            lease_start_date: null,
            lease_end_date: null,
            transaction_details: {
                due_date: null,
                return_date: null,
                late_fees: "0",
                damage_fees: "0",
                shipping_fees: "0",
            },
        });
        
        // Check if there are validation errors
        const hasValidationErrors = computed(() => {
            return Object.keys(validationErrors.value).length > 0;
        });

        const initializeForm = () => {
            if (props.newTransaction == true) {
                form.value = {
                    type: props.transactionType,
                    status: "pending",
                    initiator_id: props.initiatorBusiness,
                    receiver_business_id: null,
                    receiver_customer_id: null,
                    transaction_items: [
                        { item_id: null, quantity: "1", price: "0" },
                    ],
                    lease_start_date: null,
                    lease_end_date: null,
                    transaction_details: {
                        due_date: null,
                        return_date: null,
                        late_fees: "0",
                        damage_fees: "0",
                        shipping_fees: "0",
                    },
                };
            } else {
                form.value = {
                    id: props.transactionData.id,
                    type: props.transactionType,
                    status: "pending",
                    initiator_id: props.initiatorBusiness,
                    receiver_business_id:
                        props.transactionData?.receiver_business?.business_id ||
                        null,
                    receiver_customer_id:
                        props.transactionData?.receiver_customer?.id || null,
                    transaction_items: props.transactionData?.items || [
                        { item_id: null, quantity: "1", price: "0" },
                    ],
                    lease_start_date:
                        new Date(props.transactionData?.lease_start_date) ||
                        null,
                    lease_end_date:
                        new Date(props.transactionData?.lease_end_date) || null,
                    transaction_details: {
                        due_date:
                            new Date(
                                props.transactionData?.details?.due_date
                            ) || null,
                        return_date:
                            new Date(
                                props.transactionData?.details?.return_date
                            ) || null,
                        late_fees:
                            props.transactionData?.details?.late_fees || "0",
                        damage_fees:
                            props.transactionData?.details?.damage_fees || "0",
                        shipping_fees:
                            props.transactionData?.details?.shipping_fees ||
                            "0",
                    },
                };
            }
            // Reset validation errors when form is initialized
            validationErrors.value = {};
            itemErrors.value = [];
        };

        // Watch for changes in the newTransaction prop or transactionData
        watch(
            [() => props.newTransaction, () => props.transactionData],
            () => {
                initializeForm();
            },
            { deep: true, immediate: true }
        );

        const addItem = () => {
            form.value.transaction_items.push({
                item_id: null,
                quantity: "1",
                price: 0,
            });
            // Add a placeholder for this item's errors
            itemErrors.value.push({});
        };

        const removeItem = (index) => {
            if (form.value.transaction_items.length > 1) {
                form.value.transaction_items.splice(index, 1);
                // Also remove the errors for this item
                itemErrors.value.splice(index, 1);
            }
        };
        
        const getItemError = (index, field) => {
            if (!itemErrors.value[index]) return false;
            return itemErrors.value[index][field];
        };

        const validateForm = () => {
            const errors = {};
            const items = form.value.transaction_items;
            
            // Reset itemErrors array to match the number of items
            itemErrors.value = Array(items.length).fill().map(() => ({}));
            
            // Validate receiver (business or customer)
            if (props.isB2B) {
                if (!form.value.receiver_business_id) {
                    errors.receiver_business_id = 'Please select a business';
                }
            } else {
                if (!form.value.receiver_customer_id) {
                    errors.receiver_customer_id = 'Please select a customer';
                }
            }
            
            // Validate transaction dates if applicable
            if (props.tr_with_dates && props.tr_with_dates.includes(form.value.type)) {
                if (!form.value.lease_start_date) {
                    errors.lease_start_date = 'Start date is required';
                }
                if (!form.value.lease_end_date) {
                    errors.lease_end_date = 'End date is required';
                } else if (form.value.lease_start_date && form.value.lease_end_date && 
                           new Date(form.value.lease_end_date) <= new Date(form.value.lease_start_date)) {
                    errors.lease_end_date = 'End date must be after start date';
                }
            }
            
            // Validate items
            let hasItemErrors = false;
            
            items.forEach((item, index) => {
                const itemError = {};
                
                if (!item.item_id) {
                    itemError.item_id = true;
                    hasItemErrors = true;
                }
                
                if (!item.quantity || parseFloat(item.quantity) <= 0) {
                    itemError.quantity = true;
                    hasItemErrors = true;
                }
                
                if (!item.price || isNaN(parseFloat(item.price))) {
                    itemError.price = true;
                    hasItemErrors = true;
                }
                
                itemErrors.value[index] = itemError;
            });
            
            if (hasItemErrors) {
                errors.transaction_items = 'Please complete all transaction items with valid information';
            }
            
            validationErrors.value = errors;
            return Object.keys(errors).length === 0;
        };

        const validateAndSubmit = async () => {
            if (!validateForm()) {
                // Form is invalid, don't submit
                return;
            }
            
            // Form is valid, proceed with submission
            await submitForm();
        };

        const submitForm = async () => {
            // Convert the dates to YYYY-MM-DD before submitting
            if (form.value.lease_start_date) {
                form.value.lease_start_date = form.value.lease_start_date
                    .toISOString()
                    .slice(0, 10);
            }
            if (form.value.lease_end_date) {
                form.value.lease_end_date = form.value.lease_end_date
                    .toISOString()
                    .slice(0, 10);
            }
            if (form.value.transaction_details.due_date) {
                form.value.transaction_details.due_date =
                    form.value.transaction_details.due_date
                        .toISOString()
                        .slice(0, 10);
            }
            if (form.value.transaction_details.return_date) {
                form.value.transaction_details.return_date =
                    form.value.transaction_details.return_date
                        .toISOString()
                        .slice(0, 10);
            }

            if (props.newTransaction == true) {
                await transactionStore.addTransaction(form.value);
            } else {
                await transactionStore.updateTransaction(
                    form.value?.id,
                    form.value
                );
            }

            if (transactionStore.success) {
                loadingClose.value = true;
                setTimeout(() => {
                    closeForm();
                }, 1000);
                setTimeout(() => {
                    loadingClose.value = false;
                }, 3000);
            }
        };

        const closeForm = () => {
            emit("closeMe");
        };

        return {
            form,
            addItem,
            removeItem,
            submitForm,
            resourceStore,
            transactionStore,
            closeForm,
            loadingClose,
            validateForm,
            validateAndSubmit,
            validationErrors,
            hasValidationErrors,
            itemErrors,
            getItemError
        };
    },

    data() {
        return {
            options: this.products,
            queries: {
                search: "",
            },
            filteredOptions: this.products,
            tr_types: ["purchase", "sale", "leasing", "borrowing"],
            tr_with_dates: ["leasing", "borrowing"],
        };
    },
    methods: {
        async onSearch(searchQuery) {
            if (searchQuery.trim()) {
                this.queries.search = searchQuery.trim();
                await this.resourceStore.fetchResources(this.queries);

                const newOptions = this.resourceStore.items.data;

                const selectedItems = this.form.transaction_items
                    .map((item) => {
                        return this.options.find(
                            (option) => option.id === item.item_id
                        );
                    })
                    .filter((item) => item);

                this.options = [...new Set([...selectedItems, ...newOptions])];
            } else {
                await this.resourceStore.fetchResources();
                this.options = this.resourceStore.items.data;
            }
        },

        onInput(value) {
            if (value == null) {
                this.options = this.products;
            } else {
                this.options = [];
                this.options = this.products;
            }
        },
        onOptionSelected(selectedOption) {
            this.form.transaction_items.map((item) => {
                if (selectedOption.id == item.item_id) {
                    if (!item.price) {
                        item.price = selectedOption.price;
                        return item;
                    }
                    return item;
                }
                return item;
            });
        },
        selectChange(selectedOption) {
            this.form.transaction_items.map((item) => {
                if (selectedOption == item.item_id) {
                    item.price = this.products.find(
                        (x) => x.id == selectedOption
                    )?.price;

                    return item;
                }
                return item;
            });
        },
        selectSearch(filter) {
            this.onSearch(filter.value);
        },
    },
};
</script>

<style>
/* @import url(".././../css/select.css"); */
</style>
