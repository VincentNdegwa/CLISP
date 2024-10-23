<template>
    <div class="flex flex-col gap-4 p-4">
        <div>
            <label for="mpesa-number" class="block text-sm font-medium text-gray-700">M-Pesa Number</label>
            <input
                v-model="mpesaNumber"
                type="text"
                id="mpesa-number"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-indigo-500 sm:text-sm"
                placeholder="Enter your M-Pesa number"
            />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Amount to Pay</label>
            <input
                type="text"
                :value="transaction.amount"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed sm:text-sm"
                readonly
            />
        </div>
        <PrimaryButton @click="requestSTKPush">Request STK Push</PrimaryButton>
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
            mpesaNumber: "",
        };
    },
    methods: {
        requestSTKPush() {
            // Emit an event to handle the M-Pesa payment logic, passing the M-Pesa number
            this.$emit("stkPush", { mpesaNumber: this.mpesaNumber, amount: this.transaction.amount });
        },
    },
};
</script>
