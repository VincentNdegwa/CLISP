<template>
    <div class="p-1">
        <div class="text-2xl font-bold">New Transaction</div>
        <form
            @submit.prevent="submitForm"
            class="space-y-8 p-6 bg-white shadow-lg rounded-lg"
        >
            <!-- Row 1: Type and Status -->
            <!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <InputLabel for="type" value="Type" />
                <TextInput v-model="form.type" type="text" id="type" required />
            </div>

            <div>
                <InputLabel for="status" value="Status" />
                <TextInput
                    v-model="form.status"
                    type="text"
                    id="status"
                    required
                />
            </div>
        </div> -->

            <!-- Row 2: Initiator and Receiver -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- <div>
                <InputLabel for="initiator_id" value="Initiator ID" />
                <TextInput
                    v-model="form.initiator_id"
                    type="number"
                    id="initiator_id"
                    required
                />
            </div> -->

                <!-- Receiver Business ID -->
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
            <div class="flex gap-1">
                <div>
                    <InputLabel
                        for="lease_start_date"
                        value="Lease Start Date"
                    />
                    <TextInput
                        v-model="form.lease_start_date"
                        type="date"
                        id="lease_start_date"
                    />
                </div>

                <!-- Lease End Date -->
                <div>
                    <InputLabel for="lease_end_date" value="Lease End Date" />
                    <TextInput
                        v-model="form.lease_end_date"
                        type="date"
                        id="lease_end_date"
                    />
                </div>
            </div>

            <!-- Transaction Items Section -->
            <div class="bg-gray-100 p-4 rounded-lg shadow-md">
                <h3 class="text-lg font-bold mb-4">Transaction Items</h3>

                <div
                    v-for="(item, index) in form.transaction_items"
                    :key="index"
                    class="flex gap-1 items-center"
                >
                    <!-- Item ID -->
                    <div>
                        <InputLabel
                            :for="`item_id_${index}`"
                            value="Item"
                            required="true"
                        />

                        <select
                            v-model="item.item_id"
                            required
                            :id="`item_id_${index}`"
                            class="select w-full max-w-xs m-0 bg-white text-slate-950"
                        >
                            <option disabled selected>Select item</option>
                            <option>Homer</option>
                            <option>Marge</option>
                            <option>Bart</option>
                            <option>Lisa</option>
                            <option>Maggie</option>
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <InputLabel
                            :for="`quantity_${index}`"
                            value="Quantity"
                        />
                        <TextInput
                            v-model="item.quantity"
                            :id="`quantity_${index}`"
                            type="number"
                            min="0"
                            required
                        />
                    </div>

                    <!-- Price -->
                    <div>
                        <InputLabel :for="`price_${index}`" value="Price" />
                        <TextInput
                            v-model="item.price"
                            :id="`price_${index}`"
                            type="number"
                            min="0"
                            required
                        />
                    </div>

                    <!-- Remove Item Button -->
                    <div class="">
                        <button
                            type="button"
                            @click="removeItem(index)"
                            class="btn btn-outline btn-sm bg-rose-100 text-red-500"
                        >
                            Remove Item
                        </button>
                    </div>
                </div>

                <div class="text-right">
                    <button
                        type="button"
                        @click="addItem"
                        class="btn btn-primary btn-sm"
                    >
                        Add Another Item
                    </button>
                </div>
            </div>

            <!-- Transaction Details Section -->
            <div class="bg-gray-100 p-4 rounded-lg shadow-md">
                <h3 class="text-lg font-bold mb-4">Transaction Details</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Due Date -->
                    <div>
                        <InputLabel for="due_date" value="Due Date" />
                        <TextInput
                            v-model="form.transaction_details.due_date"
                            type="date"
                            id="due_date"
                        />
                    </div>

                    <!-- Return Date -->
                    <div>
                        <InputLabel for="return_date" value="Return Date" />
                        <TextInput
                            v-model="form.transaction_details.return_date"
                            type="date"
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
            <div class="text-right">
                <PrimaryButton type="submit">Submit</PrimaryButton>
            </div>
        </form>
    </div>
</template>

<script>
import { ref } from "vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

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
        isB2B: {
            type: Boolean,
            required: true,
        },
    },
    components: {
        InputLabel,
        PrimaryButton,
        TextInput,
    },
    setup(props) {
        const form = ref({
            type: props.transactionType,
            status: "pending",
            initiator_id: props.initiatorBusiness,
            receiver_business_id: null,
            receiver_customer_id: null,
            transaction_items: [{ item_id: null, quantity: 0, price: 0 }],
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
                quantity: 0,
                price: 0,
            });
        };

        const removeItem = (index) => {
            if (form.value.transaction_items.length > 1) {
                form.value.transaction_items.splice(index, 1);
            }
        };

        const submitForm = () => {
            // Handle form submission (e.g., API call)
            console.log("Form submitted", form.value);
        };

        return { form, addItem, removeItem, submitForm };
    },
};
</script>

<style scoped>
/* Additional styling can be added here if needed */
</style>
