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
    <Head title="Complete Registration" />
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
                        <div class="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center text-white font-semibold shadow-lg shadow-rose-500/30">5</div>
                        <span class="mt-2 text-xs text-rose-600 dark:text-rose-400 font-medium">Complete</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-white/10 backdrop-blur-sm border border-slate-200 dark:border-white/10 p-8 rounded-lg shadow-lg">
                <div class="text-center">
                    <div class="flex justify-center mb-6">
                        <div class="w-16 h-16 bg-rose-100 dark:bg-rose-500/20 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    
                    <h1 class="text-3xl font-bold text-slate-800 dark:text-white">Registration Complete!</h1>
                    <p class="mt-4 text-slate-600 dark:text-slate-300">
                        Thank you for registering with CLISP. Your account has been successfully created.
                    </p>
                    
                    <div class="mt-8 p-6 bg-slate-50 dark:bg-white/5 rounded-lg border border-slate-200 dark:border-white/10">
                        <h2 class="text-xl font-semibold text-rose-600 dark:text-rose-400">Your Subscription Details</h2>
                        <div class="mt-4 space-y-3 text-left">
                            <div class="flex justify-between">
                                <span class="text-slate-600 dark:text-slate-300">Plan:</span>
                                <span class="text-slate-800 dark:text-white font-medium">{{ form.subscription.plan }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600 dark:text-slate-300">Amount:</span>
                                <span class="text-slate-800 dark:text-white font-medium">{{ form.subscription.amount }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600 dark:text-slate-300">Billing Cycle:</span>
                                <span class="text-slate-800 dark:text-white font-medium">{{ form.subscription.billing_cycle }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-600 dark:text-slate-300">Start Date:</span>
                                <span class="text-slate-800 dark:text-white font-medium">{{ form.subscription.start_date }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <PrimaryButton @click="redirectToDashboard" class="inline-block w-full py-3 bg-rose-500 hover:bg-rose-600 text-white font-medium rounded-lg shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all">
                            Go to Dashboard
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
