<script>
import { Head } from '@inertiajs/vue3';
import Hero from './Welcome/Hero.vue';
import Goals from './Welcome/Goals.vue';
export default {
  components: {
    Head,
    Hero,
    Goals
  }
}
</script>

<template>

  <Head title="Welcome to CLISP" />
  <div class="bg-white text-slate-800">
    <Hero />
    <Goals />
  </div>
</template>
