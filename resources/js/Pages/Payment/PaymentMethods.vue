<template>
    <div class="h-fit relative p-8">
        <h2 class="text-slate-900 text-3xl font-extrabold mb-6 text-center">
            Select Payment Method
        </h2>

        <!-- Grid layout for payment method cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- M-Pesa Card (Coming Soon) -->
            <div
                class="flex flex-col items-center bg-gray-200 rounded-lg cursor-not-allowed"
                disabled
            >
                <img
                    src="/images/M-PESA.jpeg"
                    alt="M-Pesa"
                    class="w-full h-32 opacity-50"
                />
                <span class="text-lg font-semibold my-4 text-gray-500">
                    M-Pesa (Coming Soon)
                </span>
            </div>
            <!-- PayPal Card -->
            <div
                class="flex flex-col items-center bg-white shadow-lg rounded-lg cursor-pointer hover:shadow-xl transition duration-200"
                @click="confirmPayment('PayPal')"
            >
                <img
                    src="/images/PayPal-Logo.png"
                    alt="PayPal"
                    class="w-full h-32"
                />
                <span class="text-lg font-semibold my-4">PayPal</span>
            </div>

            <!-- Cash Card -->
            <div
                class="flex flex-col items-center bg-white shadow-lg rounded-lg cursor-pointer hover:shadow-xl transition duration-200"
                @click="confirmPayment('Cash')"
            >
                <img src="/images/cash.jpg" alt="Cash" class="w-full h-32" />
                <span class="text-lg font-semibold my-4">Cash</span>
            </div>
        </div>

        <!-- Confirm/Cancel Buttons -->
        <div class="flex justify-between mt-10 gap-3">
            <PrimaryRoseButton @click="cancelPayment" class="flex-1">
                Cancel
            </PrimaryRoseButton>
            <PrimaryButton
                :disabled="selectedMethod == null"
                class="flex-1"
                @click="confirm"
            >
                Confirm
            </PrimaryButton>
        </div>
    </div>
</template>

<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";

export default {
    emits: ["close"],
    components: {
        PrimaryButton,
        PrimaryRoseButton,
    },
    data() {
        return {
            selectedMethod: null,
        };
    },
    methods: {
        confirmPayment(method) {
            this.selectedMethod = method;
        },
        cancelPayment() {
            this.selectedMethod = null;
            this.$emit("close", this.selectedMethod);
        },
        confirm() {
            this.$emit("close", this.selectedMethod);
        },
    },
};
</script>

<style scoped>
button:disabled {
    opacity: 0.6;
}
</style>
