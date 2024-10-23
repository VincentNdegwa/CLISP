<template>
    <div class="flex flex-col gap-4 p-4">
        <div>
            <label class="block text-sm font-medium text-gray-700"
                >Amount to Pay</label
            >
            <input
                type="text"
                :value="transaction.amount"
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
                v-model="amountReceived"
                type="number"
                id="amount-received"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-indigo-500 sm:text-sm"
                placeholder="Enter amount received"
            />
        </div>
        <PrimaryButton @click="markAsPaid">Mark as Paid</PrimaryButton>
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
    },
    components: {
        PrimaryButton,
    },
    data() {
        return {
            amountReceived: 0,
        };
    },
    methods: {
        markAsPaid() {
            // Emit an event to handle cash payment confirmation
            this.$emit("cashPayment", {
                amountReceived: this.amountReceived,
                amountToPay: this.transaction.amount,
            });
        },
    },
};
</script>
