<script>
import { Head } from '@inertiajs/vue3';
import Hero from './Welcome/Hero.vue';
export default {
  components: {
    Head,
    Hero,
  }
}
</script>

<template>

  <Head title="Welcome to CLISP" />
  <div class="bg-white text-slate-800">
    <Hero/>
  </div>
</template>
