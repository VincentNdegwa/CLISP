<script setup>
import NavButton from "@/Components/NavButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { router } from "@inertiajs/vue3";
import { ref, onMounted, onBeforeUnmount } from 'vue';

// Accept isDarkMode prop from parent
const props = defineProps({
    isDarkMode: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['toggle-dark-mode']);

const isHeaderVisible = ref(false);
const isScrolled = ref(false);

const toggleHeader = () => {
    isHeaderVisible.value = !isHeaderVisible.value;
};

const handleResize = () => {
    // Ensure header is visible on larger screens
    if (window.innerWidth >= 768) {
        isHeaderVisible.value = true;
    }
};

const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

const openRegister = () => {
    router.visit("register", { method: "get" });
};

const openLogin = () => {
    router.visit("login", { method: "get" });
};

// Function to toggle dark mode
const toggleDarkMode = () => {
    emit('toggle-dark-mode');
};

onMounted(() => {
    window.addEventListener("resize", handleResize);
    window.addEventListener("scroll", handleScroll);
    handleResize();
});

onBeforeUnmount(() => {
    window.removeEventListener("resize", handleResize);
    window.removeEventListener("scroll", handleScroll);
});
</script>

<template>
    <header
        :class="[
            'fixed top-0 left-0 right-0 z-50 transition-all duration-300 ease-in-out py-4 px-4 sm:px-6 lg:px-8',
            isScrolled ? 'bg-slate-900/90 backdrop-blur-md shadow-lg' : 'bg-slate-900/60 backdrop-blur-sm'
        ]"
    >
        <div class="container mx-auto flex items-center justify-between">
            <!-- Logo -->
            <div class="flex items-center">
                <div class="w-10 h-10 mr-3">
                    <ApplicationLogo />
                </div>
                <span class="text-white text-xl font-bold">CLISP</span>
            </div>

            <!-- Mobile menu button -->
            <button @click="toggleHeader" class="md:hidden text-white focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>

            <!-- Desktop navigation -->
            <nav class="hidden md:block">
                <ul class="flex space-x-8">
                    <NavButton>
                        <a href="#features" class="text-white hover:text-rose-400 transition-colors">Features</a>
                    </NavButton>
                    <NavButton>
                        <a href="#pricing" class="text-white hover:text-rose-400 transition-colors">Pricing</a>
                    </NavButton>
                    <NavButton>
                        <a href="#about" class="text-white hover:text-rose-400 transition-colors">About</a>
                    </NavButton>
                    <NavButton>
                        <a href="#testimonials" class="text-white hover:text-rose-400 transition-colors">Testimonials</a>
                    </NavButton>
                </ul>
            </nav>

            <!-- Auth buttons and theme toggle -->
            <div class="hidden md:flex items-center space-x-4">
                <!-- Theme toggle button -->
                <button 
                    @click="toggleDarkMode" 
                    class="w-10 h-10 rounded-full bg-slate-800/70 backdrop-blur-sm flex items-center justify-center hover:bg-slate-700 transition-colors border border-slate-700/30"
                    aria-label="Toggle dark mode"
                >
                    <svg v-if="isDarkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
                
                <PrimaryButton @click="openLogin" class="border border-white/20 backdrop-blur-sm hover:bg-white/10 transition-all">
                    Login
                </PrimaryButton>
                <PrimaryRoseButton @click="openRegister" class="shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all">
                    Register
                </PrimaryRoseButton>
            </div>
        </div>

        <!-- Mobile menu -->
        <div
            v-show="isHeaderVisible"
            class="md:hidden absolute top-full left-0 right-0 bg-slate-900/95 backdrop-blur-md shadow-lg mt-2 p-4 rounded-lg transition-all duration-300 ease-in-out"
        >
            <nav class="mb-6">
                <ul class="flex flex-col space-y-4">
                    <NavButton>
                        <a href="#features" class="text-white hover:text-rose-400 block py-2">Features</a>
                    </NavButton>
                    <NavButton>
                        <a href="#pricing" class="text-white hover:text-rose-400 block py-2">Pricing</a>
                    </NavButton>
                    <NavButton>
                        <a href="#about" class="text-white hover:text-rose-400 block py-2">About</a>
                    </NavButton>
                    <NavButton>
                        <a href="#testimonials" class="text-white hover:text-rose-400 block py-2">Testimonials</a>
                    </NavButton>
                </ul>
            </nav>
            <div class="flex flex-col space-y-3">
                <!-- Theme toggle button for mobile -->
                <button 
                    @click="toggleDarkMode" 
                    class="flex items-center justify-center py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition-colors"
                >
                    <svg v-if="isDarkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-300 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-200 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    {{ isDarkMode ? 'Light Mode' : 'Dark Mode' }}
                </button>
                
                <PrimaryRoseButton @click="openRegister" class="w-full">
                    Register
                </PrimaryRoseButton>
                <PrimaryButton @click="openLogin" class="w-full">
                    Login
                </PrimaryButton>
            </div>
        </div>
    </header>
</template>
