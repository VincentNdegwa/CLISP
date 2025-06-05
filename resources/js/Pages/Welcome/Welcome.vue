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
</script>

<template>
    <Head title="CLISP | Inventory Sharing Platform" />
    <div class="text-slate-800 bg-gradient-to-b from-white to-slate-50 dark:from-slate-900 dark:to-slate-800">
        <Hero />
        <Features />
        <Products />
        <Goals />
        <Pricing />
        <Testimonials />
        <!-- <CustomerDetails /> -->
        <Footer />
    </div>
</template>
