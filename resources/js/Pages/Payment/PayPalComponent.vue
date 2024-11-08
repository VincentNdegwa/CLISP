<template>
    <div>
        <div :class="loading ? '' : 'hidden'">
            <LoadingUI />
        </div>
        <div :class="['h-fit relative p-8', loading ? 'hidden' : '']">
            <div class="flex w-full flex-col">
                <div id="paypal-button-container"></div>
                <p id="result-message" class="text-slate-900">
                    {{ resultMessage }}
                </p>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingUI from "@/Components/LoadingUI.vue";
import { useUserStore } from "@/Store/UserStore";

export default {
    name: "PayPalButton",
    emits: ["close", "completedPayment"],
    props: {
        transaction: {
            type: Object,
            required: true,
        },
        totalUsdPriceToPay: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            resultMessage: "",
            loading: true,
            currency_code: "USD",
            paymentDetails: {
                payer_name: null,
                payer_email: null,
                payment_method: "PayPal",
                payment_reference: null,
                paid_amount: null,
                transaction_fee: 0,
                transaction_id: this.transaction.transaction.id,
                remaining_balance: null,
                payer_id:
                    this.transaction.transaction.receiver_business.business_id,
                payee_business:
                    this.transaction.transaction.initiator.business_id,
                currency_code: "USD",
                business_id:
                    useUserStore().business ||
                    this.transaction.transaction.initiator.business_id,
                isB2B: true,
            },
        };
    },
    components: {
        LoadingUI,
    },
    mounted() {
        if (window.paypal) {
            this.renderPayPalButton();
        } else {
            console.error("PayPal SDK not loaded.");
        }
    },
    methods: {
        renderPayPalButton() {
            const totalValue = this.transaction.items
                .reduce((total, item) => {
                    return (
                        total +
                        parseFloat(item.usdPrice) * parseInt(item.quantity)
                    );
                }, 0)
                .toFixed(2);

            // if (totalValue != this.totalUsdPriceToPay) {
            //     alert(
            //         `totalValue: ${totalValue}, usdPrice: ${this.totalUsdPriceToPay}`
            //     );
            //     return;
            // }

            const orderPayload = {
                intent: "CAPTURE",
                purchase_units: [
                    {
                        amount: {
                            currency_code: this.currency_code,
                            value: totalValue,
                            breakdown: {
                                item_total: {
                                    currency_code: this.currency_code,
                                    value: totalValue,
                                },
                            },
                        },
                        items: this.transaction.items.map((item) => ({
                            name: `Item ${item.id}`,
                            unit_amount: {
                                currency_code: this.currency_code,
                                value: item.usdPrice,
                            },
                            quantity: item.quantity,
                        })),
                    },
                ],
            };

            // Render PayPal buttons

            const self = this;
            if (document.getElementById("paypal-button-container")) {
                window.paypal
                    .Buttons({
                        style: {
                            shape: "rect",
                            layout: "vertical",
                            color: "gold",
                            label: "paypal",
                        },
                        async createOrder() {
                            try {
                                const response = await fetch(
                                    "/api/paypal/create-order",
                                    {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/json",
                                        },
                                        body: JSON.stringify(orderPayload),
                                    }
                                );

                                const orderData = await response.json();
                                if (orderData.id) {
                                    return orderData.id;
                                }

                                const errorDetail = orderData?.details?.[0];
                                const errorMessage = errorDetail
                                    ? `${errorDetail.issue} ${errorDetail.description} (${orderData.debug_id})`
                                    : JSON.stringify(orderData);

                                throw new Error(errorMessage);
                            } catch (error) {
                                console.error("Error creating order:", error);
                                this.resultMessage = `Error creating order: ${error.message}`;
                            }
                        },
                        async onApprove(data, actions) {
                            try {
                                const response = await fetch(
                                    `/api/paypal/capture-order/${data.orderID}`,
                                    {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/json",
                                        },
                                    }
                                );

                                const orderData = await response.json();
                                const errorDetail = orderData?.details?.[0];

                                if (
                                    errorDetail?.issue === "INSTRUMENT_DECLINED"
                                ) {
                                    return actions.restart();
                                } else if (errorDetail) {
                                    throw new Error(
                                        `${errorDetail.description} (${orderData.debug_id})`
                                    );
                                } else if (!orderData.purchase_units) {
                                    throw new Error(JSON.stringify(orderData));
                                } else {
                                    const transaction =
                                        orderData?.purchase_units?.[0]?.payments
                                            ?.captures?.[0];
                                    self.resultMessage = `Transaction ${transaction.status}: ${transaction.id}`;

                                    if (orderData.status === "COMPLETED") {
                                        self.paymentDetails.payer_email =
                                            orderData.payment_source.paypal.email_address;
                                        self.paymentDetails.payer_name =
                                            orderData.purchase_units[0].shipping.name.full_name;
                                        self.paymentDetails.payment_reference =
                                            orderData.id;
                                        self.paymentDetails.paid_amount =
                                            orderData.purchase_units[0].payments.captures[0].seller_receivable_breakdown.gross_amount.value;
                                        self.paymentDetails.remaining_balance =
                                            self.totalUsdPriceToPay -
                                            self.paymentDetails.paid_amount;
                                        self.paymentDetails.transaction_fee =
                                            orderData.purchase_units[0].payments.captures[0].seller_receivable_breakdown.paypal_fee.value;

                                        self.$emit(
                                            "completedPayment",
                                            self.paymentDetails
                                        );
                                    }
                                }
                            } catch (error) {
                                console.error("Error capturing order:", error);
                                self.resultMessage = `Error capturing order: ${error.message}`;
                            }
                        },
                    })
                    .render("#paypal-button-container");
            } else {
                console.error("#paypal-button-container element not found.");
            }
            // Set loading to false once the buttons are rendered
            self.loading = false;
        },
    },
};
</script>

<style scoped>
/* Add any styles you need for your PayPal button here */
</style>
