<script>
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head } from "@inertiajs/vue3";
export default {
    props: ["form"],
    data() {
        return {};
    },
    methods: {
        makePayment() {
            this.$emit("makePayment", this.form);
        },
    },
    components: {
        InputLabel,
        TextInput,
        InputError,
        PrimaryButton,
        Head,
    },
};
</script>

<template>
    <Head title="Payment" />
    <div
        class="min-h-screen bg-gradient-to-br from-white to-slate-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 text-slate-800 dark:text-white flex flex-col items-center py-12"
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
                        <div class="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center text-white font-semibold shadow-lg shadow-rose-500/30">4</div>
                        <span class="mt-2 text-xs text-rose-600 dark:text-rose-400 font-medium">Payment</span>
                    </div>

                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-white/20 flex items-center justify-center text-slate-500 dark:text-white/70 font-semibold">5</div>
                        <span class="mt-2 text-xs text-slate-500 dark:text-white/50 font-medium">Complete</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-slate-800 dark:text-white">Make Your Payment</h1>
                <p class="mt-4 text-slate-600 dark:text-slate-300">
                    Complete your payment to activate your subscription.
                </p>
                <h3 class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-4">
                    Amount to pay: {{ form.subscription.amount }}
                </h3>
            </div>

            <form @submit.prevent="makePayment" class="mt-8 space-y-6">
                <div class="bg-white dark:bg-white/10 backdrop-blur-sm border border-slate-200 dark:border-white/10 p-8 rounded-lg shadow-lg">
                    <div class="space-y-5">
                        <div>
                            <InputLabel for="cardName" value="Cardholder Name" />
                            <TextInput
                                id="cardName"
                                type="text"
                                v-model="form.cardName"
                                required
                                placeholder="Enter your name as it appears on your card"
                            />
                            <InputError class="mt-2 text-rose-600 dark:text-rose-400" :message="form.errors.cardName" />
                        </div>

                        <div>
                            <InputLabel for="cardNumber" value="Card Number" />
                            <TextInput
                                id="cardNumber"
                                type="text"
                                v-model="form.cardNumber"
                                required
                                placeholder="1234 5678 9012 3456"
                            />
                            <InputError
                                class="mt-2 text-rose-600 dark:text-rose-400"
                                :message="form.errors.cardNumber"
                            />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="expiryDate" value="Expiry Date" />
                                <TextInput
                                    id="expiryDate"
                                    type="text"
                                    v-model="form.expiryDate"
                                    required
                                    placeholder="MM/YY"
                                />
                                <InputError
                                    class="mt-2 text-rose-600 dark:text-rose-400"
                                    :message="form.errors.expiryDate"
                                />
                            </div>

                            <div>
                                <InputLabel for="cvc" value="CVC" />
                                <TextInput
                                    id="cvc"
                                    type="text"
                                    v-model="form.cvc"
                                    required
                                    placeholder="CVC"
                                />
                                <InputError class="mt-2 text-rose-600 dark:text-rose-400" :message="form.errors.cvc" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="billingAddress" value="Billing Address" />
                            <TextInput
                                id="billingAddress"
                                type="text"
                                v-model="form.billingAddress"
                                required
                                placeholder="Enter your billing address"
                            />
                            <InputError
                                class="mt-2 text-rose-600 dark:text-rose-400"
                                :message="form.errors.billingAddress"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="mt-8 w-full py-3 bg-rose-500 hover:bg-rose-600 text-white font-medium rounded-lg shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all flex justify-center items-center"
                        :class="{ 'opacity-75': form.processing }"
                        :disabled="form.processing"
                    >
                        Make Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
