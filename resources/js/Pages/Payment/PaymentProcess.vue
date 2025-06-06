<template>
    <AlertNotification
        :open="notification.open"
        :status="notification.status"
        :message="notification.message"
    />
    <div
        class="h-fit relative p-2 w-full flex flex-col lg:flex-row gap-4 max-h-[100vh] overflow-y-scroll"
    >
        <!-- Payment Methods Section -->
        <div
            class="methods w-full lg:w-7/12 rounded-sm flex flex-col bg-gray-100 p-4"
        >
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">
                Payment Method
            </h2>
            <div class="flex flex-col gap-3">
                <Card
                    :unstyled="styledCard"
                    v-for="method in paymentMethods"
                    :key="method.name"
                    @click="confirmPayment(method)"
                    :class="[
                        'p-4 cursor-pointer transition-colors duration-200 rounded-lg shadow',
                        selectedMethod === method.name
                            ? 'bg-slate-600 text-white'
                            : 'bg-white text-gray-800',
                    ]"
                >
                    <template #content>
                        <div class="flex gap-4 items-center">
                            <i :class="method.icon" class="text-2xl"></i>
                            <div>
                                <h3 class="font-bold text-lg">
                                    {{ method.name }}
                                </h3>
                                <p class="text-sm">{{ method.description }}</p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Payment UI -->
            <div class="payment-ui mt-6">
                <PayPalComponent
                    v-if="selectedMethod === 'PayPal'"
                    :transaction="paymentProcess.data"
                    :totalUsdPriceToPay="totalUsdPriceToPay"
                    @close="closeModal"
                    @completedPayment="completedPayment"
                />
                <MpesaComponent
                    v-if="selectedMethod === 'M-Pesa'"
                    :transaction="paymentProcess.data"
                    :totalAmountToPay="totalAmountToPay"
                    @stkPush="handleMpesaPayment"
                />
                <CashComponent
                    v-if="selectedMethod === 'Cash'"
                    :transaction="paymentProcess.data"
                    :totalAmountToPay="totalAmountToPay"
                    :currencyCode="currency_code"
                    :isLoading="paymentStore.isLoading"
                    @cashPayment="handleCashPayment"
                />
                <div class="w-full mt-4">
                    <PrimaryRoseButton class="w-full" @click="cancelPayment"
                        >Close</PrimaryRoseButton
                    >
                </div>
            </div>
        </div>

        <!-- Order Summary Section -->
        <div class="order w-full lg:w-5/12 rounded-sm p-4 bg-gray-50">
            <div class="flex flex-col gap-3 shadow-md h-full rounded-lg p-4">
                <h2 class="text-2xl font-semibold text-gray-700">
                    Order Summary
                </h2>
                <div
                    class="container-holder max-h-[75vh] overflow-y-scroll no-scrollbar w-full"
                >
                    <div
                        class="p-3 border-b"
                        v-for="(item, index) in paymentProcess.data.items"
                        :key="index"
                    >
                        <div class="flex justify-between">
                            <div class="text-lg font-semibold">
                                {{ item.name }}
                            </div>
                            <div class="text-gray-600 text-sm">
                                x{{ item.quantity }}
                            </div>
                        </div>
                        <p class="text-gray-500 text-sm mb-2">
                            {{ item.description }}
                        </p>
                        <div class="flex justify-between text-sm">
                            <div>{{ roundOffCurrency(item.price) }}</div>
                            <div class="font-semibold">
                                {{
                                    roundOffCurrency(item.price * item.quantity)
                                }}
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="pt-4 flex justify-between items-center text-lg font-bold"
                >
                    <span>Total:</span>
                    <span class="text-rose-600">{{
                        formattedAmountToPay
                    }}</span>
                </div>
            </div>
        </div>

        <!-- Notification Section -->
        <AlertNotification
            :open="
                paymentStore.successMessage != null ||
                paymentStore.errorMessage != null
            "
            :message="paymentStore.successMessage || paymentStore.errorMessage"
            :status="paymentStore.successMessage ? 'success' : 'error'"
        />
    </div>
</template>

<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import Card from "primevue/card";
import PayPalComponent from "./PayPalComponent.vue";
import MpesaComponent from "./MpesaComponent.vue";
import CashComponent from "./CashComponent.vue";
import { currencyConvertor } from "@/Store/CurrencyConvertStore";
import { usePaymentStore } from "@/Store/PaymentStore";
import { getCurrentInstance, watch } from "vue";
import { useUserStore } from "@/Store/UserStore";

export default {
    emits: ["paymentStatus", "close", "successPayment"],
    components: {
        PrimaryButton,
        PrimaryRoseButton,
        Card,
        PayPalComponent,
        MpesaComponent,
        CashComponent,
    },
    props: {
        paymentProcess: {
            type: Object,
            required: true,
        },
    },
    mounted() {
        this.getTotalPrice();
    },
    setup(props, context) {
        const paymentStore = usePaymentStore();
        const responseData = paymentStore.data;

        const createPayment = async (params) => {
            await paymentStore.createPayment(params);
            const responseData = paymentStore.data;

            if (responseData) {
                const { error, message, errors, data } = responseData;
                context.emit("paymentStatus", {
                    error,
                    message: message || errors,
                    data,
                });
            }
        };

        return {
            createPayment,
            paymentStore,
            responseData,
        };
    },
    data() {
        return {
            selectedMethod: "PayPal",
            styledCard: true,
            totalAmountToPay: 0,
            formattedAmountToPay: 0,
            currency_code: "",
            totalUsdPriceToPay: 0,
            notification: { open: false, status: "error", message: "" },
            paymentMethods: [
                {
                    name: "PayPal",
                    icon: "pi pi-paypal",
                    description: "Safe and easy online payment",
                },
                {
                    name: "Cash",
                    icon: "pi pi-wallet",
                    description: "Pay with cash upon delivery",
                },
            ],
            paymentDetails: {
                payer_name: null,
                payer_email: null,
                payment_method: this.selectedMethod,
                payment_reference: null,
                paid_amount: null,
                transaction_id: this.paymentProcess.data.transaction.id,
                remaining_balance: null,
                payer_id:
                    this.paymentProcess.data.transaction.receiver_business
                        .business_id,
                payee_business:
                    this.paymentProcess.data.transaction.initiator.business_id,
                currency_code: this.currency_code,
                business_id:
                    useUserStore().business ||
                    this.paymentProcess.data.transaction.receiver_business
                        .business_id,
                isB2B: true,
            },
        };
    },
    methods: {
        confirmPayment(method) {
            this.selectedMethod = method.name;
        },
        handleMpesaPayment(paymentData) {
            console.log("M-Pesa payment initiated:", paymentData);
        },
        handleCashPayment(paymentData) {
            const { amountReceived, amountToPay, difference } = paymentData;
            this.paymentDetails.currency_code = this.currency_code;
            this.paymentDetails.payment_method = this.selectedMethod;
            this.paymentDetails.paid_amount = amountReceived;
            this.paymentDetails.remaining_balance = difference;
            this.createPayment(this.paymentDetails);
        },
        cancelPayment() {
            this.selectedMethod = null;
            this.$emit("close", this.selectedMethod);
        },
        getTotalPrice() {
            this.totalAmountToPay = this.paymentProcess.data.items.reduce(
                (total, item) =>
                    total + parseFloat(item.price) * parseInt(item.quantity),
                0
            );
            this.formattedAmountToPay = this.roundOffCurrency(
                this.totalAmountToPay
            );
            this.totalUsdPriceToPay =
                this.paymentProcess.data.transaction.totalUsdPrice;
        },
        roundOffCurrency(value) {
            this.currency_code = this.paymentProcess.data.transaction.isB2B
                ? this.paymentProcess.data.transaction.receiver_business
                      .currency_code
                : this.paymentProcess.data.transaction.initiator.currency_code;

            return this.currency_code.trim()
                ? currencyConvertor().convertOtherCurrency(
                      value,
                      this.currency_code
                  )
                : parseFloat(value).toFixed(2);
        },
        closeModal() {
            this.$emit("close");
        },
        completedPayment(paymentData) {
            this.createPayment(paymentData);
        },
        openNotification(message, status) {
            this.notification.open = true;
            this.notification.message = message;
            this.notification.status = status;
        },
    },
};
</script>

<style>
.bg-slate-600 {
    background-color: #475569 !important;
}
</style>
