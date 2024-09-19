<script>
import { Head } from "@inertiajs/vue3";
import Hero from "./Hero.vue";
import Goals from "./Goals.vue";
import Products from "./Products.vue";
import CustomerDetails from "./CustomerDetails.vue";
export default {
    components: {
        Head,
        Hero,
        Goals,
        Products,
        CustomerDetails,
    },
};
</script>

<template>
    <Head title="Welcome to CLISP" />
    <div class="text-slate-800 bg-white">
        <Hero />
        <Goals />
        <Products />
        <CustomerDetails />
    </div>
</template>
