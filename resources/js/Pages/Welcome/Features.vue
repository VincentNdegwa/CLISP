<script setup>
import { ref } from 'vue';

const features = ref([
  {
    id: 1,
    title: 'Inventory Sharing',
    description: 'Share excess inventory with nearby businesses, reducing storage costs and increasing resource utilization.',
    icon: 'pi pi-sync',
    color: 'bg-blue-500'
  },
  {
    id: 2,
    title: 'Secure Transactions',
    description: 'End-to-end encrypted transactions ensure your business data remains protected at all times.',
    icon: 'pi pi-shield',
    color: 'bg-green-500'
  },
  {
    id: 3,
    title: 'Real-time Analytics',
    description: 'Track inventory movement, usage patterns, and optimization opportunities with powerful analytics.',
    icon: 'pi pi-chart-line',
    color: 'bg-purple-500'
  },
  {
    id: 4,
    title: 'Local Network',
    description: 'Connect with local businesses to build a strong community network for resource sharing.',
    icon: 'pi pi-users',
    color: 'bg-amber-500'
  },
  {
    id: 5,
    title: 'Smart Recommendations',
    description: 'AI-powered recommendations help you make informed decisions about inventory management.',
    icon: 'pi pi-bolt',
    color: 'bg-rose-500'
  },
  {
    id: 6,
    title: 'Mobile Access',
    description: 'Manage your inventory on the go with our responsive mobile application.',
    icon: 'pi pi-mobile',
    color: 'bg-cyan-500'
  }
]);

const activeFeature = ref(1);

const setActiveFeature = (id) => {
  activeFeature.value = id;
};
</script>

<template>
  <section id="features" class="py-24  relative overflow-hidden">
    <!-- Background elements -->
    <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-gradient-to-br from-rose-500/10 to-blue-500/10 rounded-bl-full blur-3xl"></div>
    <div class="absolute bottom-0 left-0 w-1/4 h-1/4 bg-gradient-to-tr from-amber-500/10 to-purple-500/10 rounded-tr-full blur-3xl"></div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4 text-slate-900 dark:text-white">
          Powerful <span class="text-rose-500">Features</span> for Your Business
        </h2>
        <p class="text-slate-600 dark:text-slate-300 max-w-3xl mx-auto text-lg">
          CLISP provides all the tools you need to optimize your inventory management and connect with local businesses.
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <!-- Feature showcase -->
        <div class="relative">
          <div class="aspect-square max-w-lg mx-auto relative">
            <!-- Circular background -->
            <div class="absolute inset-0 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 rounded-full"></div>

            <!-- Orbital rings -->
            <div class="absolute inset-[5%] border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-full animate-spin-slow"></div>
            <div class="absolute inset-[15%] border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-full animate-spin-slower"></div>
            <div class="absolute inset-[25%] border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-full animate-spin-slowest"></div>

            <!-- Center icon -->
            <div class="absolute inset-0 flex items-center justify-center">
              <div class="w-24 h-24 bg-white dark:bg-slate-800 rounded-full shadow-xl flex items-center justify-center">
                <i class="pi pi-box text-4xl text-rose-500"></i>
              </div>
            </div>

            <!-- Feature icons -->
            <div
              v-for="feature in features"
              :key="feature.id"
              :class="[
                'absolute w-16 h-16 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 cursor-pointer',
                feature.id === activeFeature ? 'scale-125 ring-2 ring-white dark:ring-slate-700 ring-offset-2' : 'hover:scale-110',
                feature.color
              ]"
              :style="`transform: rotate(${(feature.id - 1) * 60}deg) translateX(150px) rotate(-${(feature.id - 1) * 60}deg)`"
              @click="setActiveFeature(feature.id)"
            >
              <i :class="[feature.icon, 'text-white text-xl']"></i>
            </div>
          </div>
        </div>

        <!-- Feature details -->
        <div class="rounded-2xl shadow-xl p-8 border border-slate-200 dark:border-slate-700">
          <div v-for="feature in features" :key="feature.id" v-show="feature.id === activeFeature" class="space-y-4">
            <div :class="`inline-flex items-center justify-center w-12 h-12 rounded-lg ${feature.color}`">
              <i :class="[feature.icon, 'text-white text-xl']"></i>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 dark:text-white">{{ feature.title }}</h3>
            <p class="text-slate-600 dark:text-slate-300">{{ feature.description }}</p>

            <div class="pt-6">
              <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                    <i class="pi pi-check text-green-500"></i>
                  </div>
                </div>
                <div>
                  <h4 class="font-medium text-slate-900 dark:text-white">Easy Integration</h4>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Seamlessly integrates with your existing systems</p>
                </div>
              </div>

              <div class="h-px bg-slate-200 dark:bg-slate-700 my-6"></div>

              <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                    <i class="pi pi-check text-green-500"></i>
                  </div>
                </div>
                <div>
                  <h4 class="font-medium text-slate-900 dark:text-white">24/7 Support</h4>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Our team is always available to help you</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Feature stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mt-24">
        <div class=" rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 text-center">
          <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="pi pi-clock text-blue-500 text-xl"></i>
          </div>
          <div class="text-3xl font-bold text-slate-900 dark:text-white mb-2">85%</div>
          <div class="text-slate-600 dark:text-slate-400">Time Saved</div>
        </div>

        <div class=" rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 text-center">
          <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="pi pi-dollar text-green-500 text-xl"></i>
          </div>
          <div class="text-3xl font-bold text-slate-900 dark:text-white mb-2">30%</div>
          <div class="text-slate-600 dark:text-slate-400">Cost Reduction</div>
        </div>

        <div class=" rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 text-center">
          <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="pi pi-users text-purple-500 text-xl"></i>
          </div>
          <div class="text-3xl font-bold text-slate-900 dark:text-white mb-2">2,000+</div>
          <div class="text-slate-600 dark:text-slate-400">Active Users</div>
        </div>

        <div class=" rounded-xl shadow-lg p-6 border border-slate-200 dark:border-slate-700 text-center">
          <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="pi pi-star text-amber-500 text-xl"></i>
          </div>
          <div class="text-3xl font-bold text-slate-900 dark:text-white mb-2">4.9/5</div>
          <div class="text-slate-600 dark:text-slate-400">User Rating</div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.animate-spin-slow {
  animation: spin 30s linear infinite;
}

.animate-spin-slower {
  animation: spin 40s linear infinite reverse;
}

.animate-spin-slowest {
  animation: spin 50s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
