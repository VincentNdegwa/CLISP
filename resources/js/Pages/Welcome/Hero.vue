<script>
import NavButton from "@/Components/NavButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import PrimaryTransparentButton from "@/Components/PrimaryTransparentButton.vue";
import { router } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";

export default {
    components: {
        NavButton,
        PrimaryRoseButton,
        PrimaryButton,
        PrimaryTransparentButton,
        ApplicationLogo,
    },
    data() {
        return {
            isHeaderVisible: true,
            isScrolled: false,
        };
    },
    methods: {
        toggleHeader() {
            this.isHeaderVisible = !this.isHeaderVisible;
        },
        handleResize() {
            // Ensure header is visible on larger screens
            if (window.innerWidth >= 768) {
                this.isHeaderVisible = true;
            }
        },
        handleScroll() {
            this.isScrolled = window.scrollY > 50;
        },
        openRegister() {
            router.visit("register", { method: "get" });
        },
        openLogin() {
            router.visit("login", { method: "get" });
        },
    },
    mounted() {
        window.addEventListener("resize", this.handleResize);
        window.addEventListener("scroll", this.handleScroll);
        this.handleResize();
    },
    beforeDestroy() {
        window.removeEventListener("resize", this.handleResize);
        window.removeEventListener("scroll", this.handleScroll);
    },
};
</script>

<template>
    <section class="relative min-h-screen overflow-hidden">
        <!-- Animated background with gradient and particles -->
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 z-0">
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
            <!-- Navbar -->
            <header
                :class="[
                    'fixed top-0 left-0 right-0 z-900 transition-all duration-300 ease-in-out py-4 px-4 sm:px-6 lg:px-8',
                    isScrolled ? 'bg-slate-900/90 backdrop-blur-md shadow-lg' : 'bg-transparent'
                ]"
            >
                <div class="container mx-auto flex items-center justify-between">
                    <!-- Logo im -->
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
                                <a href="#products" class="text-white hover:text-rose-400 transition-colors">Products</a>
                            </NavButton>
                            <NavButton>
                                <a href="#goals" class="text-white hover:text-rose-400 transition-colors">Goals</a>
                            </NavButton>
                            <NavButton>
                                <a href="#pricing" class="text-white hover:text-rose-400 transition-colors">Pricing</a>
                            </NavButton>
                            <NavButton>
                                <a href="#testimonials" class="text-white hover:text-rose-400 transition-colors">Testimonials</a>
                            </NavButton>
                        </ul>
                    </nav>

                    <!-- Auth buttons -->
                    <div class="hidden md:flex space-x-4">
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
                                <a href="#products" class="text-white hover:text-rose-400 block py-2">Products</a>
                            </NavButton>
                            <NavButton>
                                <a href="#goals" class="text-white hover:text-rose-400 block py-2">Goals</a>
                            </NavButton>
                            <NavButton>
                                <a href="#pricing" class="text-white hover:text-rose-400 block py-2">Pricing</a>
                            </NavButton>
                            <NavButton>
                                <a href="#testimonials" class="text-white hover:text-rose-400 block py-2">Testimonials</a>
                            </NavButton>
                        </ul>
                    </nav>
                    <div class="flex flex-col space-y-3">
                        <PrimaryRoseButton @click="openRegister" class="w-full">
                            Register
                        </PrimaryRoseButton>
                        <PrimaryButton @click="openLogin" class="w-full">
                            Login
                        </PrimaryButton>
                    </div>
                </div>
            </header>

            <!-- Hero content -->
            <div class="flex flex-col md:flex-row items-center justify-between h-screen pt-24 md:pt-0">
                <div class="md:w-1/2 text-center md:text-left mb-12 md:mb-0">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6">
                        <span class="text-rose-500">Empowering</span> Local Businesses with Smart <span class="text-rose-500">Inventory</span> Solutions
                    </h1>

                    <!-- <p class="text-lg md:text-xl text-slate-300 mb-8 max-w-2xl">
                        <b>CLISP</b> offers a seamless platform to share, rent, and purchase inventory, enhancing resource efficiency and fostering local collaboration.
                    </p> -->

                    <div class="flex flex-col sm:flex-row justify-center md:justify-start space-y-4 sm:space-y-0 sm:space-x-4">
                        <PrimaryRoseButton class="px-8 py-3 text-lg shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all">
                            Get Started
                        </PrimaryRoseButton>
                        <PrimaryTransparentButton class="px-8 py-3 text-lg border border-white/20 backdrop-blur-sm hover:bg-white/10 transition-all">
                            Learn More
                        </PrimaryTransparentButton>
                    </div>

                    <div class="mt-12 flex items-center justify-center md:justify-start space-x-6">
                        <div class="flex -space-x-2">
                            <img class="w-10 h-10 rounded-full border-2 border-white" src="https://randomuser.me/api/portraits/women/12.jpg" alt="User">
                            <img class="w-10 h-10 rounded-full border-2 border-white" src="https://randomuser.me/api/portraits/men/32.jpg" alt="User">
                            <img class="w-10 h-10 rounded-full border-2 border-white" src="https://randomuser.me/api/portraits/women/23.jpg" alt="User">
                        </div>
                        <div class="text-sm text-slate-300">
                            <span class="font-bold text-white">2,000+</span> businesses already joined
                        </div>
                    </div>
                </div>

                <div class="md:w-1/2 relative">
                    <!-- 3D-like dashboard mockup with glow effect -->
                    <div class="relative mx-auto w-full max-w-lg">
                        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-rose-500 to-blue-500 rounded-lg filter blur-xl opacity-30 animate-pulse"></div>
                        <div class="relative bg-slate-800 border border-slate-700 rounded-lg shadow-2xl overflow-hidden">
                            <img src="images/bg_mockup.png" alt="CLISP Dashboard" class="w-full h-auto" onerror="this.src='/images/no_data.svg'">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent opacity-40"></div>
                        </div>

                        <!-- Floating elements for 3D effect -->
                        <div class="absolute -top-4 -right-4 w-fit p-4 h-fit bg-slate-800 border border-slate-700 rounded-lg shadow-lg transform rotate-6 flex items-center gap-2 justify-center">
                            <div class="text-rose-500 font-bold text-xl">+20%</div>
                            <div class="text-xs text-slate-300">Efficiency</div>
                        </div>

                        <div class="absolute -bottom-4 -left-4 w-32 h-16 bg-slate-800 border border-slate-700 rounded-lg shadow-lg transform -rotate-3 flex items-center justify-center p-2">
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
        <div class="absolute bottom-0 left-0 right-0 bg-slate-800/80 backdrop-blur-md border-t border-slate-700/50 py-4 hidden lg:block">
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
