<script setup>
import { Head } from "@inertiajs/vue3";
import Hero from "./Hero.vue";
import Features from "./Features.vue";
import Products from "./Products.vue";
import Goals from "./Goals.vue";
import Pricing from "./Pricing.vue";
import Testimonials from "./Testimonials.vue";
import CustomerDetails from "./CustomerDetails.vue";
import Footer from "./Footer.vue";
import Header from "./Header.vue";
import { ref, onMounted, watch, provide } from "vue";

// Theme management
const isDarkMode = ref(false);

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    updateTheme();
};

const updateTheme = () => {
    // Update HTML class for global theme
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

// Check for system preference or saved preference
onMounted(() => {
    // Check for saved theme preference or use system preference
    const savedTheme = localStorage.getItem('theme');
    
    if (savedTheme) {
        isDarkMode.value = savedTheme === 'dark';
    } else {
        // Check system preference
        isDarkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    
    // Initial theme setup
    updateTheme();
    
    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            isDarkMode.value = e.matches;
            updateTheme();
        }
    });
});

// Expose theme state to child components
provide('isDarkMode', isDarkMode);
provide('toggleDarkMode', toggleDarkMode);
</script>

<template>
    <Head title="CLISP | Inventory Sharing Platform" />
    <div class="relative text-slate-800 bg-gradient-to-b from-white to-slate-50 dark:text-slate-100 dark:from-slate-900 dark:to-slate-800">
        <Header :isDarkMode="isDarkMode" @toggle-dark-mode="toggleDarkMode" />
        <Hero />
        <Features />
        <Products />
        <Goals />
        <Pricing />
        <Testimonials />
        <CustomerDetails />
        <Footer />
        
        <!-- Floating theme toggle button -->
        <button 
            @click="toggleDarkMode" 
            class="fixed bottom-6 right-6 w-12 h-12 rounded-full bg-slate-800/70 dark:bg-slate-700/70 backdrop-blur-sm shadow-lg flex items-center justify-center z-50 hover:bg-slate-700 dark:hover:bg-slate-600 transition-colors border border-slate-700/30 dark:border-slate-500/30"
            aria-label="Toggle dark mode"
        >
            <svg v-if="isDarkMode" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>
    </div>
</template>
