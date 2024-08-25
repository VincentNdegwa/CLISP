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
        class="min-h-screen bg-white text-slate-900 flex flex-col gap-10 items-center py-12"
    >
        <ul class="steps hidden md:grid">
            <li class="step step-warning">Register</li>
            <li class="step step-warning">Register Business</li>
            <li class="step step-warning">Choose Plan</li>
            <li class="step step-warning">Make Payment</li>
            <li class="step">Complete</li>
        </ul>

        <div class="w-full max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-2xl font-bold">Make Your Payment</h1>
                <p class="mt-4 text-slate-400">
                    Complete your payment to activate your subscription.
                </p>
                <h3 class="text-2xl font-bold">
                    Amount to pay {{ form.subscription.amount }}
                </h3>
            </div>

            <form @submit.prevent="makePayment" class="mt-8 space-y-6">
                <div>
                    <InputLabel for="cardName" value="Cardholder Name" />
                    <TextInput
                        id="cardName"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.cardName"
                        required
                        placeholder="Enter your name as it appears on your card"
                    />
                    <InputError class="mt-2" :message="form.errors.cardName" />
                </div>

                <div>
                    <InputLabel for="cardNumber" value="Card Number" />
                    <TextInput
                        id="cardNumber"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.cardNumber"
                        required
                        placeholder="1234 5678 9012 3456"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.cardNumber"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="expiryDate" value="Expiry Date" />
                        <TextInput
                            id="expiryDate"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.expiryDate"
                            required
                            placeholder="MM/YY"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.expiryDate"
                        />
                    </div>

                    <div>
                        <InputLabel for="cvc" value="CVC" />
                        <TextInput
                            id="cvc"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.cvc"
                            required
                            placeholder="CVC"
                        />
                        <InputError class="mt-2" :message="form.errors.cvc" />
                    </div>
                </div>

                <div>
                    <InputLabel for="billingAddress" value="Billing Address" />
                    <TextInput
                        id="billingAddress"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.billingAddress"
                        required
                        placeholder="Enter your billing address"
                    />
                    <InputError
                        class="mt-2"
                        :message="form.errors.billingAddress"
                    />
                </div>

                <PrimaryButton
                    class="mt-8 w-full"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Make Payment
                </PrimaryButton>
            </form>
        </div>
    </div>
</template>
