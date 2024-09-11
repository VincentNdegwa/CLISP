<template>
    <div class="p-1 h-fit relative">
        <div class="text-2xl font-bold ms-6 mt-2">New Transaction</div>
        <form
            @submit.prevent="submitForm"
            class="space-y-8 p-6 bg-white shadow-lg rounded-lg"
        >
            <!-- Row 2: Initiator and Receiver -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-if="isB2B">
                    <InputLabel
                        for="receiver_business_id"
                        value="Receiver Business "
                        :required="isB2B"
                    />

                    <select
                        v-model="form.receiver_business_id"
                        id="receiver_business_id"
                        :required="isB2B"
                        class="select w-full max-w-xs bg-white text-slate-950 ring-1 ring-slate-300 hover:ring-slate-300"
                    >
                        <option disabled selected>
                            Select Connected Business
                        </option>
                        <option
                            :value="item.business_id"
                            v-for="(item, index) in business"
                            :key="index"
                        >
                            {{ item.business_name }}
                        </option>
                    </select>
                </div>

                <div v-if="!isB2B">
                    <InputLabel
                        for="receiver_customer_id"
                        value="Receiver Customer "
                        :required="!isB2B"
                    />

                    <select
                        v-model="form.receiver_customer_id"
                        id="receiver_customer_id"
                        :required="!isB2B"
                        class="select w-full max-w-xs bg-white text-slate-950 ring-1 ring-slate-300 hover:ring-slate-300"
                    >
                        <option disabled selected>
                            Select Connected Business
                        </option>
                        <option
                            :value="item.id"
                            v-for="(item, index) in customer"
                            :key="index"
                        >
                            {{ item.full_names }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Row 3: Receiver Customer and Lease Dates -->
            <div
                class="flex gap-1"
                v-if="tr_with_dates.includes(transactionType)"
            >
                <div>
                    <InputLabel
                        for="lease_start_date"
                        value="Lease Start Date"
                    />
                    <DatePicker
                        v-model="form.lease_start_date"
                        showIcon
                        fluid
                        iconDisplay="input"
                    />
                </div>

                <!-- Lease End Date -->
                <div>
                    <InputLabel for="lease_end_date" value="Lease End Date" />
                    <DatePicker
                        v-model="form.lease_end_date"
                        showIcon
                        fluid
                        iconDisplay="input"
                        id="lease_end_date"
                    />
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
                <div
                    v-for="(item, index) in form.transaction_items"
                    :key="index"
                    class="w-full flex flex-col md:flex-row gap-2 md:gap-4 items-center mt-2"
                >
                    <!-- Item ID -->
                    <div class="w-full md:w-fit">
                        <InputLabel
                            :for="`item_id_${index}`"
                            value="Item"
                            required="true"
                            class="w-full"
                        />

                        <v-select
                            id="autocomplete"
                            required
                            v-model="item.item_id"
                            :options="options"
                            :get-option-label="(option) => option.item_name"
                            :reduce="(option) => option.id"
                            :filterable="false"
                            placeholder="Search Item..."
                            @search="onSearch"
                            @update:modelValue="onInput"
                            @option:selected="onOptionSelected"
                            class="md:max-w-60 md:w-60 bg-white text-slate-950 p-1 b-0 ring-0 w-full relative"
                        />
                    </div>

                    <!-- Quantity -->
                    <div class="md:w-[100px] md:max-w-fit w-full">
                        <InputLabel
                            :for="`quantity_${index}`"
                            value="Quantity"
                            class="w-full"
                            required="true"
                        />
                        <TextInput
                            v-model="item.quantity"
                            :id="`quantity_${index}`"
                            type="text"
                            class="w-full"
                            min="0.01"
                            required
                        />
                    </div>

                    <!-- Price -->
                    <div class="md:w-[150px] w-full md:max-w-fit">
                        <InputLabel
                            :for="`price_${index}`"
                            value="Unit Price"
                            class="w-full"
                            required="true"
                        />
                        <TextInput
                            v-model="item.price"
                            :id="`price_${index}`"
                            type="number"
                            class="w-full"
                            min="0"
                            required
                        />
                    </div>

                    <!-- Remove Item Button -->
                    <div class="mt-4">
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
                class="bg-gray-100 p-4 rounded-lg shadow-md"
                v-if="tr_with_dates.includes(transactionType)"
            >
                <h3 class="text-lg font-bold mb-4">Transaction Details</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Due Date -->
                    <div>
                        <InputLabel for="due_date" value="Due Date" />

                        <DatePicker
                            v-model="form.transaction_details.due_date"
                            showIcon
                            fluid
                            iconDisplay="input"
                            id="due_date"
                        />
                    </div>

                    <!-- Return Date -->
                    <div>
                        <InputLabel for="return_date" value="Return Date" />

                        <DatePicker
                            v-model="form.transaction_details.return_date"
                            showIcon
                            fluid
                            iconDisplay="input"
                            id="return_date"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <!-- Late Fees -->
                    <div>
                        <InputLabel for="late_fees" value="Late Fees" />
                        <TextInput
                            v-model="form.transaction_details.late_fees"
                            type="number"
                            min="0"
                            id="late_fees"
                        />
                    </div>

                    <!-- Damage Fees -->
                    <div>
                        <InputLabel for="damage_fees" value="Damage Fees" />
                        <TextInput
                            v-model="form.transaction_details.damage_fees"
                            type="number"
                            min="0"
                            id="damage_fees"
                        />
                    </div>

                    <!-- Shipping Fees -->
                    <div>
                        <InputLabel for="shipping_fees" value="Shipping Fees" />
                        <TextInput
                            v-model="form.transaction_details.shipping_fees"
                            type="number"
                            min="0"
                            id="shipping_fees"
                        />
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-right flex gap-1">
                <PrimaryButton
                    class="flex-1"
                    type="submit"
                    :disabled="transactionStore.loading || loadingClose"
                    >Submit</PrimaryButton
                >
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
import { ref } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryRoseButton from "./PrimaryRoseButton.vue";
import vSelect from "vue-select";
import { useResourceStore } from "@/Store/Resource";
import { useTransactionStore } from "@/Store/TransactionStore";
import DatePicker from "primevue/datepicker";

export default {
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
    },
    components: {
        InputLabel,
        PrimaryButton,
        TextInput,
        PrimaryRoseButton,
        vSelect,
        DatePicker,
    },
    setup(props, { emit }) {
        const resourceStore = useResourceStore();
        const transactionStore = useTransactionStore();
        const loadingClose = ref(false);
        const form = ref({
            type: props.transactionType,
            status: "pending",
            initiator_id: props.initiatorBusiness,
            receiver_business_id: null,
            receiver_customer_id: null,
            transaction_items: [{ item_id: null, quantity: 1, price: 0 }],
            lease_start_date: null,
            lease_end_date: null,
            transaction_details: {
                due_date: null,
                return_date: null,
                late_fees: null,
                damage_fees: null,
                shipping_fees: null,
            },
        });

        const addItem = () => {
            form.value.transaction_items.push({
                item_id: null,
                quantity: 1,
                price: 0,
            });
        };

        const removeItem = (index) => {
            if (form.value.transaction_items.length > 1) {
                form.value.transaction_items.splice(index, 1);
            }
        };

        const submitForm = async () => {
            await transactionStore.addTransaction(form.value);

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
            emit("close");
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
    },
};
</script>

<style>
@import url(".././../css/select.css");
.p-inputtext {
    background: white !important;
    color: black !important;
}
</style>
