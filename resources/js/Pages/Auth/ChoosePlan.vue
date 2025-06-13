<script>
import { Head, router, useForm } from "@inertiajs/vue3";
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
            business_id: this.business?.business_id,
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
        getUrl(plan) {
            return `checkout/subscription/${this.business?.business_id}/${plan.price_id}`;
        },
    },
    components: {
        Head,
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
        class="min-h-screen bg-white dark:bg-gradient-to-br dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 text-slate-900 dark:text-white flex flex-col items-center py-12"
        v-if="
            !form.subscription && !form.selected_plan && !success_subscription
        "
    >
        <!-- Steps indicator -->
        <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 mb-10">
            <div class="relative">
                <!-- Steps line -->
                <div class="absolute top-4 left-0 w-full h-1 bg-slate-200 dark:bg-white/10 rounded-full"></div>
                
                <!-- Steps circles -->
                <div class="relative z-10 flex justify-between items-center">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center text-white font-semibold shadow-lg shadow-rose-500/30">1</div>
                        <span class="mt-2 text-xs text-rose-600 dark:text-rose-400 font-medium">Register</span>
                    </div>
                    
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center text-white font-semibold shadow-lg shadow-rose-500/30">2</div>
                        <span class="mt-2 text-xs text-rose-600 dark:text-rose-400 font-medium">Business</span>
                    </div>
                    
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center text-white font-semibold shadow-lg shadow-rose-500/30">3</div>
                        <span class="mt-2 text-xs text-rose-600 dark:text-rose-400 font-medium">Plan</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-white/20 flex items-center justify-center text-slate-500 dark:text-white/70 font-semibold">4</div>
                        <span class="mt-2 text-xs text-slate-500 dark:text-white/50 font-medium">Payment</span>
                    </div>
                    
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-white/20 flex items-center justify-center text-slate-500 dark:text-white/70 font-semibold">5</div>
                        <span class="mt-2 text-xs text-slate-500 dark:text-white/50 font-medium">Complete</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Choose Your Plan</h1>
                <p class="mt-4 text-slate-600 dark:text-slate-300">
                    Find the plan that best suits your needs.
                </p>
            </div>

            <div class="w-full flex justify-end mt-8">
                <SelectButton
                    v-model="billing_cycle"
                    :options="cycles"
                    :optionLabel="
                        (option) =>
                            option.charAt(0).toUpperCase() + option.slice(1)
                    "
                    class="bg-slate-100 dark:bg-white/10 rounded-lg overflow-hidden"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div
                    v-for="plan in billing_plans"
                    :key="plan.name"
                    class="card w-full mt-6 bg-white dark:bg-white/10 backdrop-blur-sm border border-slate-200 dark:border-white/10 p-8 rounded-lg hover:shadow-xl transition-all duration-300"
                >
                    <div class="card-body">
                        <!-- Plan Header -->
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-semibold text-slate-900 dark:text-white">
                                {{ plan.name }} Plan
                            </h2>
                            <div
                                v-if="plan.isPopular"
                                class="bg-rose-500 text-white text-xs px-3 py-1 rounded-full uppercase tracking-wider shadow-lg shadow-rose-500/20"
                            >
                                Popular
                            </div>
                        </div>

                        <!-- Price Section -->
                        <div class="mt-4 text-center">
                            <p class="text-4xl font-extrabold text-slate-900 dark:text-white">
                                {{ currency(plan.price) }}
                            </p>
                            <span class="text-sm text-slate-600 dark:text-slate-300">
                                / {{ plan.billing_cycle }}
                            </span>
                        </div>

                        <!-- Features List -->
                        <ul class="mt-6 space-y-4 text-slate-600 dark:text-slate-300">
                            <li
                                v-for="feature in plan.features"
                                :key="feature"
                                class="flex items-start"
                            >
                                <svg
                                    class="w-5 h-5 text-rose-600 dark:text-rose-400 mr-3"
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
                                :href="getUrl(plan)"
                                class="w-full grid place-items-center bg-rose-500 hover:bg-rose-600 text-white font-medium py-3 rounded-lg shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all"
                            >
                                Select {{ plan.name }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
