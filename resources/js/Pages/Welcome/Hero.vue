<script setup>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import PrimaryTransparentButton from "@/Components/PrimaryTransparentButton.vue";
import { router } from "@inertiajs/vue3";

const openRegister = () => {
    router.visit("register", { method: "get" });
};
</script>

<template>
    <section class="relative min-h-screen overflow-hidden">

        <!-- Animated background with gradient and particles -->
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 z-0">
            <div class="absolute inset-0 opacity-20">
                <div class="particles-container"></div>
            </div>
        </div>

        <!-- Glowing accent elements -->
        <div class="absolute top-1/4 -left-20 w-80 h-80 bg-rose-500 rounded-full filter blur-3xl opacity-10 animate-pulse"></div>
        <div class="absolute bottom-1/4 -right-20 w-80 h-80 bg-blue-500 rounded-full filter blur-3xl opacity-10 animate-pulse delay-1000"></div>

        <!-- Grid pattern overlay -->
        <div class="absolute inset-0 bg-grid-pattern opacity-5 z-0"></div>


        <div class="relative z-10 container mx-auto px-4 sm:px-6 lg:px-8 h-full">
            <!-- Hero content -->
            <div class="flex flex-col md:flex-row items-center justify-between h-screen pt-24 md:pt-0">
                <div class="md:w-1/2 text-center md:text-left mb-12 md:mb-0">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6">
                        Revolutionize Your <span class="text-rose-500">Inventory</span> Management
                    </h1>
                    <p class="text-slate-300 dark:text-slate-300 text-lg md:text-xl mb-8 max-w-lg mx-auto md:mx-0">
                        Share resources, optimize costs, and grow your business with our cutting-edge inventory sharing platform.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center md:justify-start space-y-4 sm:space-y-0 sm:space-x-4">
                        <PrimaryRoseButton @click="openRegister" class="px-8 py-3 text-lg shadow-lg shadow-rose-500/20">
                            Get Started
                        </PrimaryRoseButton>
                        <PrimaryTransparentButton class="px-8 py-3 text-lg">
                            Learn More
                        </PrimaryTransparentButton>
                    </div>
                    <div class="mt-8 text-slate-400 dark:text-slate-400 text-sm flex items-center justify-center md:justify-start">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 text-rose-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-bold text-white">2,000+</span> businesses already joined
                    </div>
                </div>

                <div class="md:w-1/2 relative">
                    <!-- 3D-like dashboard mockup with glow effect -->
                    <div class="relative mx-auto w-full max-w-lg">
                        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-rose-500 to-blue-500 rounded-lg filter blur-xl opacity-30 animate-pulse"></div>
                        <div class="relative bg-slate-800 dark:bg-slate-900 border border-slate-700 dark:border-slate-800 rounded-lg shadow-2xl overflow-hidden">
                            <img src="images/bg_mockup.png" alt="CLISP Dashboard" class="w-full h-auto" onerror="this.src='/images/no_data.svg'">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent opacity-40"></div>
                        </div>

                        <!-- Floating elements for 3D effect -->
                        <div class="absolute -top-4 -right-4 w-fit p-4 h-fit bg-slate-800 dark:bg-slate-900 border border-slate-700 dark:border-slate-800 rounded-lg shadow-lg transform rotate-6 flex items-center gap-2 justify-center">
                            <div class="text-rose-500 font-bold text-xl">+20%</div>
                            <div class="text-xs text-slate-300">Efficiency</div>
                        </div>

                        <div class="absolute -bottom-4 -left-4 w-32 h-16 bg-slate-800 dark:bg-slate-900 border border-slate-700 dark:border-slate-800 rounded-lg shadow-lg transform -rotate-3 flex items-center justify-center p-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <div class="text-xs text-slate-300">Live Updates</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating stats bar at bottom -->
        <div class="absolute bottom-0 left-0 right-0 bg-slate-800/80 dark:bg-slate-900/80 backdrop-blur-md border-t border-slate-700/50 dark:border-slate-800/50 py-4 hidden lg:block">
            <div class="container mx-auto px-8 flex justify-between">
                <div class="flex items-center space-x-2">
                    <div class="text-2xl font-bold text-white">2000+</div>
                    <div class="text-slate-300">Active Users</div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="text-2xl font-bold text-white">96%</div>
                    <div class="text-slate-300">Satisfaction Rate</div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="text-2xl font-bold text-white">20%</div>
                    <div class="text-slate-300">Resource Optimization</div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="text-2xl font-bold text-white">24/7</div>
                    <div class="text-slate-300">Customer Support</div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.bg-grid-pattern {
    background-image:
        linear-gradient(to right, rgba(255,255,255,0.05) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255,255,255,0.05) 1px, transparent 1px);
    background-size: 40px 40px;
}

.dark .bg-grid-pattern {
    background-image:
        linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255,255,255,0.03) 1px, transparent 1px);
}

.particles-container {
    position: relative;
    width: 100%;
    height: 100%;
}

.particles-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: radial-gradient(circle at center, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 40px 40px;
}

.dark .particles-container::before {
    background-image: radial-gradient(circle at center, rgba(255,255,255,0.07) 1px, transparent 1px);
}

@keyframes pulse {
    0%, 100% {
        opacity: 0.1;
    }
    50% {
        opacity: 0.2;
    }
}

.animate-pulse {
    animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.delay-1000 {
    animation-delay: 1s;
}
</style>
