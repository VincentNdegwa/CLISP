<template>
    <div
        class="h-fit relative p-1 w-full flex flex-col lg:flex-row gap-1 max-h-[100vh] overflow-y-scroll"
    >
        <!-- Payment Methods Section -->
        <div class="methods w-full lg:w-7/12 rounded-sm flex flex-col">
            <div class="text-xl font-bold">Payment Method</div>
            <div class="flex flex-col p-2 gap-2 mt-1">
                <Card
                    unstyled="false"
                    v-for="method in paymentMethods"
                    :key="method.name"
                    @click="confirmPayment(method)"
                    :class="[
                        'p-0 cursor-pointer transition-colors duration-200 rounded shadow-lg',
                        selectedMethod === method.name
                            ? 'bg-black'
                            : 'bg-white',
                    ]"
                >
                    <template #content>
                        <div class="flex gap-5 items-center p-4">
                            <i :class="method.icon"></i>
                            <div class="flex flex-col">
                                <div class="font-bold text-xl">
                                    {{ method.name }}
                                </div>
                                <small>{{ method.description }}</small>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="payment-ui min-h-[20vh] mt-5">
                <PayPalComponent
                    v-if="selectedMethod == 'PayPal'"
                    :transaction="PaymentProcess.data"
                    @close="closeModal"
                    @completedPayment="completedPayment"
                />
                <MpesaComponent
                    v-if="selectedMethod == 'M-Pesa'"
                    :transaction="PaymentProcess.data"
                    @stkPush="handleMpesaPayment"
                />
                <CashComponent
                    v-if="selectedMethod == 'Cash'"
                    :transaction="PaymentProcess.data"
                    @cashPayment="handleCashPayment"
                />
            </div>
            <div></div>
        </div>

        <div class="order w-full lg:w-5/12 rounded-sm">
            <p>Order Summary</p>
        </div>
    </div>
</template>

<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import Card from "primevue/card";
import PayPalComponent from "./PayPalComponent.vue";
import MpesaComponent from "./MpesaComponent.vue";
import CashComponent from "./CashComponent.vue";

export default {
    emits: ["close"],
    components: {
        PrimaryButton,
        PrimaryRoseButton,
        Card,
        PayPalComponent,
        MpesaComponent,
        CashComponent,
    },
    props: {
        PaymentProcess: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            selectedMethod: "Cash",
            paymentMethods: [
                {
                    name: "PayPal",
                    icon: "pi pi-paypal",
                    description: "Safe and easy way for online payment",
                },
                {
                    name: "M-Pesa",
                    icon: "pi pi-mobile",
                    description: "Convenient mobile money transfer",
                },
                {
                    name: "Cash",
                    icon: "pi pi-wallet",
                    description: "Pay with cash upon delivery",
                },
            ],
        };
    },
    methods: {
        confirmPayment(method) {
            this.selectedMethod = method.name;
        },
        handleMpesaPayment(paymentData) {
            console.log("M-Pesa payment initiated:", paymentData);
            // Handle the M-Pesa payment logic
        },
        handleCashPayment(paymentData) {
            console.log("Cash payment received:", paymentData);
            // Handle the cash payment logic
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

<style>
.bg-black {
    background-color: #ef4444 !important;
}
</style>
