<script>
import { Head, router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import Payment from "./Payment.vue";
import CompleteRegistration from "./CompleteRegistration.vue";
import { currencyConvertor } from "@/Store/CurrencyConvertStore";

import SelectButton from "primevue/selectbutton";

export default {
    props: ["business", "plans_t"],
    data() {
        const form = useForm({
            subscription: "",
            selected_plan: false,
            paid: false,
            business: this.business?.business_id,
            cardName: "",
            subscription_name: "",
            subscription_amount: "",
            cardNumber: "",
            expiryDate: "",
            cvc: "",
            billingAddress: "",
        });

        return {
            form,
            form,
            success_subscription: false,
            notification: {
                open: false,
                status: "info",
                message: "",
            },
            billing_cycle: "monthly",
            billing_plans: [],
            cycles: ["monthly", "annually"],
        };
    },
    methods: {
        makePayment(data) {
            this.form = { ...data };

            axios
                .post("api/subscription/make", this.form)
                .then((res) => {
                    console.log(res);

                    if (res.data.error) {
                        this.notification.open = true;
                        this.notification.message = res.data.message;
                        this.notification.status = "error";
                    }
                    if (!res.data.err) {
                        this.notification.open = true;
                        this.notification.message = "Subscription Successfull";
                        this.notification.status = "success";
                        this.success_subscription = true;
                    }
                })
                .catch((err) => {
                    this.notification.open = true;
                    this.notification.message = "An error occurred!!";
                    this.notification.status = "error";
                });
        },
        currency(amount) {
            const converter = currencyConvertor();
            return converter.convertOtherCurrency(amount, "USD");
        },
        setPlans() {
            this.billing_plans = this.plans_t.map((plan) => {
                return plan.find(
                    (x_plan) => x_plan.billing_cycle == this.billing_cycle
                );
            });
        },
    },
    components: {
        Head,
        Payment,
        CompleteRegistration,
        SelectButton,
    },
    mounted() {
        this.setPlans();
    },
    watch: {
        billing_cycle: {
            deep: true,
            handler() {
                this.setPlans();
            },
        },
    },
};
</script>

<template>
    <Head title="Choose Plan" />
    <AlertNotification
        :status="notification.status"
        :message="notification.message"
        :open="notification.open"
    />

    <div
        class="min-h-screen bg-gray-100 text-gray-900 flex flex-col gap-10 items-center py-12"
        v-if="
            !form.subscription && !form.selected_plan && !success_subscription
        "
    >
        <ul class="steps hidden md:grid">
            <li class="step step-warning">Register</li>
            <li class="step step-warning">Register Business</li>
            <li class="step step-warning">Choose Plan</li>
            <li class="step">Make Payment</li>
            <li class="step">Complete</li>
        </ul>
        <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-2xl font-bold">Choose Your Plan</h1>
                <p class="mt-4 text-gray-500">
                    Find the plan that best suits your needs.
                </p>
            </div>

            <div class="w-full justify-self-end">
                <SelectButton
                    v-model="billing_cycle"
                    :options="cycles"
                    :optionLabel="
                        (option) =>
                            option.charAt(0).toUpperCase() + option.slice(1)
                    "
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div
                    v-for="plan in billing_plans"
                    :key="plan.name"
                    class="card w-full mt-6 bg-white shadow-md border border-gray-300 p-8 rounded-lg hover:shadow-lg transition-all duration-300"
                >
                    <div class="card-body">
                        <!-- Plan Header -->
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-gray-800">
                                {{ plan.name }} Plan
                            </h2>
                            <div
                                v-if="plan.isPopular"
                                class="bg-red-500 text-white text-xs px-3 py-1 rounded-full uppercase tracking-wider"
                            >
                                Popular
                            </div>
                        </div>

                        <!-- Price Section -->
                        <div class="mt-4 text-center">
                            <p class="text-4xl font-extrabold text-gray-900">
                                {{ currency(plan.price) }}
                            </p>
                            <span class="text-sm text-gray-500">
                                / {{ plan.billing_cycle }}
                            </span>
                        </div>

                        <!-- Features List -->
                        <ul class="mt-6 space-y-4 text-gray-700">
                            <li
                                v-for="feature in plan.features"
                                :key="feature"
                                class="flex items-start"
                            >
                                <svg
                                    class="w-5 h-5 text-red-500 mr-3"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.707a1 1 0 010 1.414l-9 9a1 1 0 01-1.414 0l-5-5a1 1 0 011.414-1.414L7 13.586l8.293-8.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span>{{ feature }}</span>
                            </li>
                        </ul>

                        <!-- Select Button -->
                        <div class="card-actions mt-8">
                            <a
                                :href="`checkout/subscription/${plan.price_id}`"
                                class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1"
                            >
                                Select {{ plan.name }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <Payment
        v-else-if="
            form.selected_plan && form.subscription && !success_subscription
        "
        :form="form"
        @makePayment="makePayment"
    />
    <CompleteRegistration
        v-else-if="
            form.selected_plan && form.subscription && success_subscription
        "
    />
</template>

<style scoped>
/* Customize the button animation */
.btn-primary {
    transition: background-color 0.3s ease-in-out, transform 0.2s ease-in-out;
}
.btn-primary:hover {
    transform: translateY(-2px);
}
</style>
