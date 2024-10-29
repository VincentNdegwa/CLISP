<template>
    <div class="flex flex-col gap-4 p-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">
                Amount to Pay ({{ currencyCode }})
            </label>
            <input
                type="text"
                :value="totalAmountToPay.toFixed(2)"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed sm:text-sm"
                readonly
            />
        </div>
        <div>
            <label
                for="amount-received"
                class="block text-sm font-medium text-gray-700"
                >Amount Received</label
            >
            <input
                v-model.number="amountReceived"
                type="number"
                id="amount-received"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 focus:ring-slate-500 rounded-md shadow-sm sm:text-sm"
                placeholder="Enter amount received"
            />
            <p
                v-if="amountReceived < totalAmountToPay"
                class="text-red-500 text-md font-bold mt-3"
            >
                Amount received cannot be less than the amount to pay.
            </p>
            <p
                v-else-if="balance > 0"
                class="text-green-500 text-md font-bold mt-3"
            >
                Change to give back: {{ balance.toFixed(2) }} {{ currencyCode }}
            </p>
        </div>
        <PrimaryButton
            @click="markAsPaid"
            :disabled="amountReceived < totalAmountToPay || isLoading"
        >
            Mark as Paid
        </PrimaryButton>
    </div>
</template>

<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";

export default {
    props: {
        transaction: {
            type: Object,
            required: true,
        },
        totalAmountToPay: {
            type: Number,
            required: true,
        },
        currencyCode: {
            type: String,
            required: true,
        },
        isLoading: {
            type: Boolean,
            required: true,
        },
    },
    components: {
        PrimaryButton,
    },
    data() {
        return {
            amountReceived: this.totalAmountToPay,
        };
    },
    computed: {
        balance() {
            return this.amountReceived > this.totalAmountToPay
                ? this.amountReceived - this.totalAmountToPay
                : 0;
        },
    },
    methods: {
        markAsPaid() {
            if (this.amountReceived >= this.totalAmountToPay) {
                this.$emit("cashPayment", {
                    amountReceived: this.amountReceived,
                    amountToPay: this.totalAmountToPay,
                    difference: this.amountReceived - this.totalAmountToPay,
                });
            }
        },
    },
    watch: {
        totalAmountToPay(newValue) {
            // Ensure amount received is not less than the total amount to pay
            this.amountReceived = newValue;
        },
    },
};
</script>
