<template>
    <div
        class="h-fit relative p-1 w-full flex flex-col lg:flex-row gap-1 max-h-[100vh] overflow-y-scroll"
    >
        <!-- Payment Methods Section -->
        <div class="methods w-full lg:w-7/12 rounded-sm flex flex-col">
            <div class="text-xl font-bold">Payment Method</div>
            <div class="flex flex-col p-2 gap-2 mt-1">
                <Card
                    :unstyled="styledCard"
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
                    :totalUsdPriceToPay="totalUsdPriceToPay"
                    @close="closeModal"
                    @completedPayment="completedPayment"
                />
                <MpesaComponent
                    v-if="selectedMethod == 'M-Pesa'"
                    :transaction="PaymentProcess.data"
                    :totalAmountToPay="totalAmountToPay"
                    @stkPush="handleMpesaPayment"
                />
                <CashComponent
                    v-if="selectedMethod == 'Cash'"
                    :transaction="PaymentProcess.data"
                    :totalAmountToPay="totalAmountToPay"
                    :currencyCode="currency_code"
                    :isLoading="paymentStore.isLoading"
                    @cashPayment="handleCashPayment"
                />
                <div class="px-4 w-full">
                    <PrimaryRoseButton class="w-full" @click="cancelPayment"
                        >Close</PrimaryRoseButton
                    >
                </div>
            </div>
        </div>

        <div class="order w-full lg:w-5/12 rounded-sm p-1">
            <div
                class="flex flex-col gap-2 shadow-md h-full w-full rounded-lg p-1"
            >
                <p>Order Summary</p>
                <div
                    class="container-holder h-fit max-h-[75vh] overflow-y-scroll no-scrollbar w-full"
                >
                    <div class="p-2">
                        <div
                            class="product-detail flex flex-col border-b"
                            v-for="(item, index) in PaymentProcess.data.items"
                            :key="index"
                        >
                            <div class="text-l font-bold">{{ item.name }}</div>
                            <small>{{ item.description }}</small>
                            <div class="flex">
                                <div class="flex-grow">
                                    <div class="text-sm">
                                        {{ roundOffCurrency(item.price) }}
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <div class="text-sm">
                                        x{{ item.quantity }}
                                    </div>
                                </div>
                                <div class="flex-grow">
                                    <div class="text-sm">
                                        {{
                                            roundOffCurrency(
                                                item.price * item.quantity
                                            )
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-5 flex justify-between">
                    <div>Display Price:</div>
                    <div class="font-extrabold">{{ formattedAmountToPay }}</div>
                </div>
            </div>
        </div>

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
import { watch } from "vue";

export default {
    emits: ["paymentStatus", "close"],
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
    mounted() {
        this.getTotalPrice();
    },
    setup(_, { emit }) {
        const paymentStore = usePaymentStore();
        const responseData = paymentStore.data;

        const createPayment = async (params) => {
            await paymentStore.createPayment(params);
        };

        watch(
            () => paymentStore,
            (newData) => {
                const { error, message, errors, data } =
                    newData.data?.data || {};
                if (error === false) {
                    emit("paymentStatus", {
                        error: error,
                        message: message || errors,
                        data: data,
                    });
                }
            },
            { deep: true }
        );

        return {
            createPayment,
            paymentStore,
            responseData,
        };
    },
    data() {
        return {
            selectedMethod: "Cash",
            styledCard: true,
            totalAmountToPay: 0,
            formattedAmountToPay: 0,
            currency_code: "",
            totalUsdPriceToPay: 0,
            paymentMethods: [
                {
                    name: "PayPal",
                    icon: "pi pi-paypal",
                    description: "Safe and easy way for online payment",
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
                transaction_id: this.PaymentProcess.data.transaction.id,
                remaining_balance: null,
                payer_business:
                    this.PaymentProcess.data.transaction.receiver_business
                        .business_id,
                payee_business:
                    this.PaymentProcess.data.transaction.initiator.business_id,
                currency_code: this.currency_code,
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
            this.totalAmountToPay = this.PaymentProcess.data.items.reduce(
                (total, item) =>
                    total + parseFloat(item.price) * parseInt(item.quantity),
                0
            );
            this.formattedAmountToPay = this.roundOffCurrency(
                this.totalAmountToPay
            );
            this.totalUsdPriceToPay =
                this.PaymentProcess.data.transaction.totalUsdPrice;
        },
        roundOffCurrency(value) {
            this.currency_code = this.PaymentProcess.data.transaction.isB2B
                ? this.PaymentProcess.data.transaction.receiver_business
                      .currency_code
                : this.PaymentProcess.data.transaction.initiator.currency_code;

            if (this.currency_code.trim()) {
                return currencyConvertor().convertOtherCurrency(
                    value,
                    this.currency_code
                );
            }
            return parseFloat(value).toFixed(2);
        },
    },
};
</script>

<style>
.bg-black {
    background-color: #94a3b8 !important;
}
</style>
