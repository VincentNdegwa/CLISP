<template>
    <AlertNotification
        :open="notification.open"
        :status="notification.status"
        :message="notification.message"
    />
    <div class="flex flex-col p-4 gap-5 bg-white rounded shadow-md">
        <div class="flex justify-between items-center">
            <div class="text-2xl font-bold">Checkout Transaction</div>
            <div
                @click="closeModal"
                class="bg-rose-500 h-10 w-10 grid place-items-center rounded-md text-white"
            >
                <i class="pi pi-times"></i>
            </div>
        </div>
        <div class="bg-slate-100 p-4 rounded-lg">
            <h2 class="text-lg font-bold text-slate-900 mb-2">Details</h2>
            <div v-if="transactionData.receiver_business">
                <p>
                    <strong>Business Name:</strong>
                    {{ transactionData.receiver_business.business_name }}
                </p>
                <p>
                    <strong>Email:</strong>
                    {{ transactionData.receiver_business.email }}
                </p>
                <p>
                    <strong>Phone:</strong>
                    {{ transactionData.receiver_business.phone_number }}
                </p>
                <p>
                    <strong>Location:</strong>
                    {{ transactionData.receiver_business.location }}
                </p>
                <p>
                    <strong>Currency:</strong>
                    {{ transactionData.receiver_business.currency_code }}
                </p>
            </div>
            <div v-else-if="transactionData.receiver_customer">
                <p>
                    <strong>Customer Name:</strong>
                    {{ transactionData.receiver_customer.full_names }}
                </p>
                <p>
                    <strong>Email:</strong>
                    {{ transactionData.receiver_customer.email }}
                </p>
                <p>
                    <strong>Phone:</strong>
                    {{ transactionData.receiver_customer.phone_number }}
                </p>
                <p>
                    <strong>Address:</strong>
                    {{ transactionData.receiver_customer.address }}
                </p>
            </div>
        </div>

        <!-- Payment Method Options -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div
                v-for="method in paymentMethods"
                :key="method.displayName"
                class="card cursor-pointer bg-white ring-2 flex flex-col p-2 h-32 items-center justify-center rounded-lg"
                :class="{
                    'ring-rose-500': selectedMethod === method.displayName,
                    'ring-slate-200': selectedMethod !== method.displayName,
                }"
                @click="selectMethod(method.displayName)"
            >
                <div
                    :class="{
                        'bg-rose-500': selectedMethod === method.displayName,
                        'bg-slate-200': selectedMethod !== method.displayName,
                    }"
                    class="rounded-full h-5 w-5 grid place-items-center self-end mb-1"
                >
                    <div class="rounded-full bg-white h-4 w-4"></div>
                </div>
                <div class="text-center text-slate-900">
                    <i :class="method.icon" class="text-2xl"></i>
                    <div class="mt-2 font-semibold">
                        {{ method.displayName }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div
            v-if="selectedMethod"
            class="flex flex-col gap-3 mt-5 bg-slate-50 p-4 rounded-lg"
        >
            <div class="text-lg font-semibold text-slate-900">
                Selected Payment: {{ selectedMethod }}
            </div>
            <div class="flex flex-col gap-2">
                <label class="text-slate-900"
                    >Amount To Receive ({{
                        transactionData.initiator.currency_code
                    }}):</label
                >
                <InputNumber
                    v-model="transactionData.totalPrice"
                    mode="currency"
                    fluid
                    disabled
                    :currency="transactionData.initiator.currency_code"
                />
            </div>
            <div class="flex flex-col gap-2">
                <label class="text-slate-900"
                    >Amount Reeceived ({{
                        transactionData.initiator.currency_code
                    }}):</label
                >
                <InputNumber
                    v-model="amountPaid"
                    showButtons
                    mode="currency"
                    fluid
                    @update:modelValue="(event) => listenAmount(event)"
                    :currency="transactionData.initiator.currency_code"
                />

                <p v-if="errorMessage" class="text-red-500 text-sm mt-1">
                    {{ errorMessage }}
                </p>
            </div>
            <div class="flex gap-3 mt-4">
                <button
                    @click="recordPayment"
                    :disabled="!canProceedCheckout"
                    :class="[
                        !canProceedCheckout
                            ? 'bg-slate-200 text-slate-900 cursor-not-allowed '
                            : 'bg-rose-500 text-white hover:bg-rose-600',
                        'px-4 py-2 rounded',
                    ]"
                >
                    Record Payment
                </button>
                <button
                    @click="cancelPayment"
                    class="bg-slate-400 text-slate-900 px-4 py-2 rounded hover:bg-slate-500"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { useUserStore } from "@/Store/UserStore";
import axios from "axios";
import InputNumber from "primevue/inputnumber";

export default {
    emits: ["close", "successPayment"],
    props: {
        transactionData: {
            type: Object,
            required: true,
        },
    },
    components: {
        InputNumber,
    },
    data() {
        return {
            paymentMethods: [
                { name: "cash", displayName: "Cash", icon: "pi pi-wallet" },
                { name: "bank", displayName: "Bank", icon: "pi pi-building" },
                {
                    name: "paybill",
                    displayName: "Paybill",
                    icon: "pi pi-credit-card",
                },
                {
                    name: "till",
                    displayName: "Till Number",
                    icon: "pi pi-wallet",
                },
                { name: "coupon", displayName: "Coupon", icon: "pi pi-ticket" },
                { name: "paypal", displayName: "PayPal", icon: "pi pi-paypal" },
                {
                    name: "credit_card",
                    displayName: "Credit Card",
                    icon: "pi pi-credit-card",
                },
            ],
            selectedMethod: null,
            amountPaid: "",
            errorMessage: "",
            notification: { open: false, status: "error", message: "" },
            paymentDetails: {
                payer_name: null,
                payer_email: null,
                payment_method: this.selectedMethod,
                payment_reference: null,
                paid_amount: null,
                transaction_id: this.transactionData.id,
                remaining_balance: null,
                payer_business:
                    this.transactionData.receiver_business.business_id,
                payee_business: this.transactionData.initiator.business_id,
                currency_code: this.transactionData.initiator.currency_code,
                business_id:
                    useUserStore().business ||
                    this.transactionData.initiator.business_id,
            },
            canProceedCheckout: true,
        };
    },
    methods: {
        selectMethod(method) {
            this.selectedMethod = method;
            this.amountPaid = this.transactionData.totalPrice;
        },
        openNotification(message, status) {
            this.notification.open = true;
            this.notification.message = message;
            this.notification.status = status;
        },
        async recordPayment() {
            if (!this.amountPaid || this.amountPaid <= 0) {
                this.errorMessage = "Please enter a valid amount.";
                this.openNotification(this.errorMessage, "error");
                return;
            }
            if (!this.checkAmount(this.amountPaid)) {
                return;
            }
            this.canProceedCheckout = false;
            this.paymentDetails.paid_amount = this.amountPaid;
            this.paymentDetails.payment_method = this.selectedMethod;
            this,
                (this.paymentDetails.remaining_balance =
                    this.transactionData.totalPrice - this.amountPaid);

            try {
                const response = await axios.post(
                    "/api/payments/record-payment",
                    this.paymentDetails
                );
                let data = response.data;
                if (!data.error) {
                    this.openNotification(data.message, "success");
                    this.$emit("successPayment", data.data);
                    this.closeModal();
                } else {
                    this.openNotification(data.message, "error");
                }
            } catch (error) {
                this.openNotification(error.message, "error");
            } finally {
                this.canProceedCheckout = true;
            }
        },
        cancelPayment() {
            this.selectedMethod = null;
            this.amountPaid = "";
            this.errorMessage = "";
        },
        closeModal() {
            this.$emit("close");
        },
        checkAmount(value) {
            this.notification.open = false;
            const newValue = parseFloat(value);
            if (newValue < this.transactionData.totalPrice) {
                this.errorMessage =
                    "Amount Paid can't be less than Amount To Receive";
                this.openNotification(this.errorMessage, "error");
                this.canProceedCheckout = false;
                return false;
            } else if (newValue > this.transactionData.totalPrice) {
                this.errorMessage =
                    "Amount paid can't be more than Amount To Receive";
                this.openNotification(this.errorMessage, "error");
                this.canProceedCheckout = false;

                return false;
            } else {
                this.errorMessage = null;
                this.canProceedCheckout = true;
                return true;
            }
        },
        listenAmount(value) {
            this.checkAmount(value);
        },
    },
};
</script>

<style scoped>
.card {
    transition: all 0.3s ease;
}
.card:hover {
    transform: scale(1.05);
}
</style>
