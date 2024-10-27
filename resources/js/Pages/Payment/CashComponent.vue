<template>
    <div class="flex flex-col gap-4 p-4">
        <div>
            <label class="block text-sm font-medium text-gray-700"
                >Amount to Pay</label
            >
            <input
                type="text"
                :value="totalAmountToPay"
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
                class="mt-1 block w-full px-3 py-2 border border-gray-300 focus:ring-slate-500 rounded-md shadow-sm sm:text-sm"
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
        totalAmountToPay: {
            type: Number,
            required: true,
        },
    },
    components: {
        PrimaryButton,
    },
    data() {
        return {
            amountReceived: 0,
            amountPaying: this.totalAmountToPay,
        };
    },
    methods: {
        markAsPaid() {
            this.$emit("cashPayment", {
                amountReceived: this.amountReceived,
                amountToPay: this.amountPaying,
            });
        },
    },
};
</script>
