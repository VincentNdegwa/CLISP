<script>
import { Head, router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import Payment from "./Payment.vue";
import CompleteRegistration from "./CompleteRegistration.vue";

export default {
    props: ["business"],
    data() {
        const form = useForm({
            subscription: "",
            selected_plan: false,
            paid: false,
            business: this.business.business_id,
            cardName: "",
            subscription_name: "",
            subscription_amount: "",
            cardNumber: "",
            expiryDate: "",
            cvc: "",
            billingAddress: "",
        });

        return {
            plans: [
                {
                    name: "Basic",
                    price: "$10/month",
                    amount: "10",
                    features: [
                        "10 projects",
                        "24/7 Support",
                        "Basic Analytics",
                    ],
                    isPopular: false,
                },
                {
                    name: "Pro", // Added the missing name for this plan
                    price: "$30/month",
                    amount: "30",
                    features: [
                        "50 projects",
                        "24/7 Support",
                        "Advanced Analytics",
                    ],
                    isPopular: true,
                },
                {
                    name: "Premium",
                    price: "$50/month",
                    amount: "50",
                    features: [
                        "Unlimited projects",
                        "Priority Support",
                        "Premium Analytics",
                    ],
                    isPopular: false,
                },
            ],
            form,
            form,
            success_subscription: false,
            notification: {
                open: false,
                status: "info",
                message: "",
            },
        };
    },
    methods: {
        selectPlan(plan) {
            this.form.subscription = plan;
            this.form.subscription_name = plan.name;
            this.form.subscription_amount = plan.amount;
            this.form.selected_plan = true;
        },
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
    },
    components: {
        Head,
        Payment,
        CompleteRegistration,
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

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div
                    v-for="plan in plans"
                    :key="plan.name"
                    class="card w-full mt-6 bg-white shadow-xl border border-gray-200 p-6 rounded-lg transform hover:scale-105 transition-all duration-300"
                >
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-extrabold text-gray-900">
                                {{ plan.name }}
                            </h2>
                            <div
                                v-if="plan.isPopular"
                                class="bg-rose-500 text-white text-sm px-3 py-1 rounded-full"
                            >
                                Popular
                            </div>
                        </div>
                        <p class="text-3xl font-bold mt-4 text-gray-900">
                            {{ plan.price }}
                        </p>
                        <ul class="mt-6 space-y-3 text-gray-600">
                            <li
                                v-for="feature in plan.features"
                                :key="feature"
                                class="flex items-center"
                            >
                                <svg
                                    class="w-5 h-5 text-rose-500 mr-2"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.707a1 1 0 010 1.414l-9 9a1 1 0 01-1.414 0l-5-5a1 1 0 011.414-1.414L7 13.586l8.293-8.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                {{ feature }}
                            </li>
                        </ul>
                        <div class="card-actions justify-center mt-8">
                            <button
                                class="btn btn-primary w-full bg-rose-500 hover:bg-rose-600 text-white"
                                @click="selectPlan(plan)"
                            >
                                Select {{ plan.name }}
                            </button>
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
