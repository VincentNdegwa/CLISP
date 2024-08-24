<script>
import { Head } from "@inertiajs/vue3";
import Hero from "./Hero.vue";
import Goals from "./Goals.vue";
export default {
    components: {
        Head,
        Hero,
        Goals,
    },
};
</script>

<template>
    <Head title="Welcome to CLISP" />
    <div class="text-slate-800 bg-white">
        <Hero />
        <Goals />
    </div>
</template>
