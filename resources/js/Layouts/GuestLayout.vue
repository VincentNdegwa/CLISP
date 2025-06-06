<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div class="min-h-screen flex flex-col bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 relative overflow-hidden">
        <!-- Animated particles -->
        <div class="absolute inset-0 opacity-20">
            <div class="absolute top-0 -left-4 w-72 h-72 bg-rose-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
            <div class="absolute top-0 -right-4 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-20 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>

        <!-- Grid pattern overlay -->
        <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>

        <!-- Content container -->
        <div class="flex flex-col items-center justify-center w-full flex-1 px-4 sm:px-6 py-12 z-10">
            <!-- Logo section -->
            <!-- <div class="mb-8">
                <Link href="/">
                    <ApplicationLogo class="w-20 h-20 fill-current text-rose-500" />
                </Link>
            </div> -->

            <!-- Card container -->
            <div class="w-full sm:max-w-md backdrop-blur-sm bg-white/10 dark:bg-slate-800/50 border border-white/10 shadow-xl rounded-xl overflow-hidden">
                <!-- Card header -->
                <div class="px-6 py-4 border-b border-white/10">
                    <h2 class="text-xl font-bold text-center text-white">
                        <slot name="header">Welcome to CLISP</slot>
                    </h2>
                </div>

                <!-- Card body -->
                <div class="px-6 py-6">
                    <slot />
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-slate-400">
                <p> 2024 CLISP. All rights reserved.</p>
                <div class="mt-2 flex justify-center space-x-4">
                    <Link href="/legal" class="hover:text-rose-400 transition-colors">Terms</Link>
                    <Link href="/legal?tab=privacy" class="hover:text-rose-400 transition-colors">Privacy</Link>
                    <Link href="/security" class="hover:text-rose-400 transition-colors">Security</Link>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.bg-grid-pattern {
    background-size: 40px 40px;
    background-image: 
        linear-gradient(to right, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
}

@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
