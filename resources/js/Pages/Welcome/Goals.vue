<script setup>
import { ref, onMounted } from 'vue';
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";

const goals = ref([
  {
    id: 1,
    title: 'Resource Optimization',
    description: 'Help businesses maximize their resources, reduce waste, and build stronger local networks.',
    icon: 'pi pi-bolt',
    stat: '20%',
    statLabel: 'Increase in resource efficiency'
  },
  {
    id: 2,
    title: 'Local Collaboration',
    description: 'Foster a community of businesses that work together to share resources and knowledge.',
    icon: 'pi pi-users',
    stat: '2,000+',
    statLabel: 'Active business users'
  },
  {
    id: 3,
    title: 'Cost Reduction',
    description: 'Reduce operational costs through efficient inventory sharing and management.',
    icon: 'pi pi-dollar',
    stat: '30%',
    statLabel: 'Average cost savings'
  }
]);

// For the animated counter
const animateValue = (obj, start, end, duration) => {
  let startTimestamp = null;
  const step = (timestamp) => {
    if (!startTimestamp) startTimestamp = timestamp;
    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
    obj.value = Math.floor(progress * (end - start) + start);
    if (progress < 1) {
      window.requestAnimationFrame(step);
    }
  };
  window.requestAnimationFrame(step);
};

const satisfactionRate = ref(0);
const businessCount = ref(0);

onMounted(() => {
  // Animate the counters when component is mounted
  animateValue(satisfactionRate, 0, 96, 2000);
  animateValue(businessCount, 0, 2000, 2000);
});
</script>

<template>
  <section id="goals" class="py-24 relative overflow-hidden">
    <!-- Background elements -->
    <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
    <div class="absolute top-0 left-0 w-1/3 h-1/3 bg-gradient-to-br from-blue-500/10 to-purple-500/10 rounded-br-full blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-1/3 h-1/3 bg-gradient-to-tl from-rose-500/10 to-amber-500/10 rounded-tl-full blur-3xl"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4 text-slate-900 dark:text-white">
          Our <span class="text-rose-500">Mission</span> & Goals
        </h2>
        <p class="text-slate-600 dark:text-slate-300 max-w-3xl mx-auto text-lg">
          CLISP is transforming how local businesses manage and share inventory, creating a more efficient and collaborative business ecosystem.
        </p>
      </div>

      <!-- Main goals section -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-16">
        <div
          v-for="goal in goals"
          :key="goal.id"
          class="rounded-2xl shadow-lg p-8 border border-slate-200 dark:border-slate-700 transform transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
        >
          <div class="flex items-center mb-6">
            <div :class="`w-12 h-12 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mr-4`">
              <i :class="`${goal.icon} text-rose-500 text-xl`"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ goal.title }}</h3>
          </div>

          <p class="text-slate-600 dark:text-slate-300 mb-6">{{ goal.description }}</p>

          <div class="flex items-end justify-between">
            <div>
              <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ goal.stat }}</div>
              <div class="text-sm text-slate-500 dark:text-slate-400">{{ goal.statLabel }}</div>
            </div>

            <div class="h-16 w-24">
              <div class="h-full w-full bg-slate-100 dark:bg-slate-700 rounded-lg relative overflow-hidden">
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-rose-500 to-rose-400 h-3/4 animate-pulse-slow"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                  <i class="pi pi-arrow-up text-white"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistics section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">
        <!-- Satisfaction rate card -->
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl shadow-xl p-8 relative overflow-hidden">
          <div class="absolute top-0 right-0 w-40 h-40 bg-rose-500/20 rounded-full filter blur-3xl"></div>

          <div class="relative z-10 flex flex-col md:flex-row items-center justify-between">
            <div class="mb-6 md:mb-0">
              <h3 class="text-2xl font-bold text-white mb-2">Customer Satisfaction</h3>
              <p class="text-slate-300 max-w-md">Our users love CLISP! With a high customer satisfaction rate, businesses appreciate the platform's ease of use, security, and the opportunities it creates for local collaboration.</p>
            </div>

            <div class="flex flex-col items-center">
              <div class="text-6xl font-bold text-white mb-2">{{ satisfactionRate }}%</div>
              <div class="text-sm text-slate-300">Satisfaction Rate</div>
            </div>
          </div>
        </div>

        <!-- Business network card -->
        <div class="rounded-2xl shadow-lg p-8 border border-slate-200 dark:border-slate-700 relative overflow-hidden">
          <div class="absolute top-0 left-0 w-40 h-40 bg-blue-500/10 rounded-full filter blur-3xl"></div>

          <div class="relative z-10 flex flex-col md:flex-row items-center justify-between">
            <div class="mb-6 md:mb-0">
              <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Growing Network</h3>
              <p class="text-slate-600 dark:text-slate-300 max-w-md">Join a growing network of local businesses that trust CLISP to manage and share their inventory. Whether you're a small shop or a medium-sized enterprise, CLISP helps you optimize resources.</p>
            </div>

            <div class="flex flex-col items-center">
              <div class="text-6xl font-bold text-rose-500 mb-2">{{ businessCount }}+</div>
              <div class="text-sm text-slate-500 dark:text-slate-400">Businesses</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Vision section -->
      <div class="bg-gradient-to-r from-rose-500 to-rose-600 rounded-2xl shadow-xl p-8 md:p-12 text-white relative overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div class="absolute -top-20 -right-20 w-60 h-60 bg-white/10 rounded-full"></div>
          <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white/10 rounded-full"></div>
          <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-40 h-40 bg-white/10 rounded-full"></div>
        </div>

        <div class="relative z-10 text-center max-w-3xl mx-auto">
          <h3 class="text-2xl md:text-3xl font-bold mb-6">Our Vision for the Future</h3>
          <p class="text-white/90 text-lg mb-8">
            We envision a future where businesses collaborate seamlessly, sharing resources and inventory to create a more sustainable and efficient economy. CLISP is leading this transformation by providing the technology and network to make it happen.
          </p>

          <PrimaryRoseButton class="bg-white text-rose-500 hover:bg-white/90 transition-colors px-8 py-3 rounded-lg font-medium shadow-lg">
            Start Your Journey!
          </PrimaryRoseButton>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.bg-grid-pattern {
  background-image:
      linear-gradient(to right, rgba(0,0,0,0.05) 1px, transparent 1px),
      linear-gradient(to bottom, rgba(0,0,0,0.05) 1px, transparent 1px);
  background-size: 40px 40px;
}

.animate-pulse-slow {
  animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 0.8;
  }
  50% {
    opacity: 1;
  }
}
</style>
