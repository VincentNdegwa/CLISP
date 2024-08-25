<script setup>
import { ref } from "vue";

// Define your plan options
const plans = ref([
    {
        name: "Basic",
        price: "$10/month",
        features: ["10 projects", "24/7 Support", "Basic Analytics"],
        isPopular: false,
    },
    {
        name: "Pro",
        price: "$30/month",
        features: ["50 projects", "24/7 Support", "Advanced Analytics"],
        isPopular: true, // Popular plan indicator
    },
    {
        name: "Premium",
        price: "$50/month",
        features: [
            "Unlimited projects",
            "Priority Support",
            "Premium Analytics",
        ],
        isPopular: false,
    },
]);

// Function to handle plan selection
const selectPlan = (plan) => {
    console.log(`Selected plan: ${plan.name}`);
};
</script>

<template>
    <div
        class="min-h-screen bg-slate-900 text-white flex flex-col items-center py-12"
    >
        <ul class="steps hidden md:grid">
            <li class="step step-warning">Register</li>
            <li class="step step-warning">Register Business</li>
            <li class="step">Choose Plan</li>
            <li class="step">Make Payment</li>
            <li class="step">Complete</li>
        </ul>
        <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold">Choose Your Plan</h1>
                <p class="mt-4 text-slate-400">
                    Find the plan that best suits your needs.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div
                    v-for="plan in plans"
                    :key="plan.name"
                    class="card w-full bg-slate-800 shadow-xl border border-slate-700 p-6 rounded-lg transform hover:scale-105 transition-all duration-300"
                >
                    <div class="card-body">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-extrabold">
                                {{ plan.name }}
                            </h2>
                            <div
                                v-if="plan.isPopular"
                                class="bg-indigo-600 text-white text-sm px-3 py-1 rounded-full"
                            >
                                Popular
                            </div>
                        </div>
                        <p class="text-3xl font-bold mt-4">{{ plan.price }}</p>
                        <ul class="mt-6 space-y-3 text-slate-400">
                            <li
                                v-for="feature in plan.features"
                                :key="feature"
                                class="flex items-center"
                            >
                                <svg
                                    class="w-5 h-5 text-indigo-500 mr-2"
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
                                class="btn btn-primary w-full bg-indigo-600 hover:bg-indigo-700"
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
