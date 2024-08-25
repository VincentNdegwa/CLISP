<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { router } from "@inertiajs/vue3";

export default {
    methods: {
        redirectToDashboard() {
            router.visit("/dashboard", {
                method: "get",
            });
        },
    },
    components: {
        PrimaryButton,
    },
};
</script>

<template>
    <div
        class="min-h-screen bg-gray-100 text-gray-900 flex flex-col gap-10 items-center py-12"
    >
        <ul class="steps hidden md:grid">
            <li class="step step-warning">Register</li>
            <li class="step step-warning">Register Business</li>
            <li class="step step-warning">Choose Plan</li>
            <li class="step step-warning">Make Payment</li>
            <li class="step step-warning">Complete</li>
        </ul>

        <div class="bg-white p-8 rounded-lg shadow-md text-center">
            <h2 class="text-2xl font-bold text-green-600">
                Registration Complete!
            </h2>
            <p class="mt-4 text-gray-700">
                You have successfully registered your account.
            </p>
            <p class="mt-2 text-gray-700">
                Please proceed to your dashboard to start using the platform.
            </p>
        </div>
        <PrimaryButton @click="redirectToDashboard">
            Go to Dashboard
        </PrimaryButton>
    </div>
</template>
