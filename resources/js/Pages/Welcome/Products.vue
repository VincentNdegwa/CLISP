<template>
  <section id="products" class="py-24 relative">
    <!-- Background elements -->
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-rose-100 dark:bg-rose-900/20 rounded-full filter blur-3xl opacity-30"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-100 dark:bg-blue-900/20 rounded-full filter blur-3xl opacity-30"></div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4 text-slate-900 dark:text-white">
          One Platform, <span class="text-rose-500">Many Solutions</span>
        </h2>
        <p class="text-slate-600 dark:text-slate-300 max-w-3xl mx-auto text-lg">
          CLISP offers a comprehensive suite of products designed to optimize your inventory management and foster local business collaboration.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
        <div
          v-for="product in products"
          :key="product.id"
          class="group relative  rounded-2xl shadow-lg overflow-hidden border border-slate-200 dark:border-slate-700 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
        >
          <!-- Card header with gradient -->
          <div :class="`bg-gradient-to-r ${product.color} p-6 h-24 flex items-center`">
            <div class="bg-white/20 dark:bg-slate-800/30 w-12 h-12 rounded-lg flex items-center justify-center backdrop-blur-sm">
              <i :class="`${product.icon} text-white text-xl`"></i>
            </div>
            <h3 class="text-white text-xl font-bold ml-4">{{ product.title }}</h3>
          </div>

          <!-- Card content -->
          <div class="p-6">
            <p class="text-slate-600 dark:text-slate-300 mb-6">{{ product.description }}</p>

            <ul class="space-y-3">
              <li
                v-for="(feature, index) in product.features"
                :key="index"
                class="flex items-center"
              >
                <div class="w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mr-3">
                  <i class="pi pi-check text-green-500 text-xs"></i>
                </div>
                <span class="text-slate-700 dark:text-slate-300 text-sm">{{ feature }}</span>
              </li>
            </ul>

            <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
              <button class="text-rose-500 hover:text-rose-600 dark:hover:text-rose-400 font-medium text-sm flex items-center transition-colors">
                Learn more
                <i class="pi pi-arrow-right ml-2"></i>
              </button>

              <div class="bg-slate-100 dark:bg-slate-700 rounded-full px-3 py-1">
                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">CLISP</span>
              </div>
            </div>
          </div>

          <!-- Hover effect overlay -->
          <div class="absolute inset-0 bg-gradient-to-r from-rose-500/10 to-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
        </div>
      </div>

      <!-- CTA Section -->
      <div class="mt-16 bg-gradient-to-r from-slate-900 to-slate-800 rounded-2xl p-8 md:p-12 shadow-xl relative overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div class="absolute top-0 right-0 w-1/2 h-1/2 bg-rose-500/10 rounded-full filter blur-3xl"></div>
          <div class="absolute bottom-0 left-0 w-1/2 h-1/2 bg-blue-500/10 rounded-full filter blur-3xl"></div>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
          <div class="md:w-2/3">
            <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Ready to optimize your inventory?</h3>
            <p class="text-slate-300">Join thousands of businesses already using CLISP to manage and share their resources efficiently.</p>
          </div>

          <div class="md:w-1/3 flex flex-col sm:flex-row gap-4">
            <button class="px-6 py-3 bg-rose-500 hover:bg-rose-600 text-white rounded-lg shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all font-medium">
              Get Started
            </button>
            <button class="px-6 py-3 bg-transparent hover:bg-white/10 text-white border border-white/20 rounded-lg transition-colors font-medium">
              Contact Sales
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';

const products = ref([
  {
    id: 1,
    title: 'Inventory Management',
    description: 'A comprehensive solution for tracking, managing, and optimizing your business inventory with real-time updates and analytics.',
    icon: 'pi pi-box',
    features: ['Real-time tracking', 'Barcode scanning', 'Automated alerts', 'Inventory forecasting'],
    color: 'from-blue-500 to-cyan-400'
  },
  {
    id: 2,
    title: 'Resource Sharing',
    description: 'Connect with local businesses to share resources, reducing costs and environmental impact while maximizing efficiency.',
    icon: 'pi pi-share-alt',
    features: ['Local business network', 'Secure sharing', 'Rental agreements', 'Resource optimization'],
    color: 'from-green-500 to-emerald-400'
  },
  {
    id: 3,
    title: 'Analytics Dashboard',
    description: 'Gain valuable insights into your inventory usage, sharing patterns, and optimization opportunities with our powerful analytics.',
    icon: 'pi pi-chart-bar',
    features: ['Custom reports', 'Data visualization', 'Trend analysis', 'Export capabilities'],
    color: 'from-purple-500 to-violet-400'
  },
  {
    id: 5,
    title: 'Mobile Application',
    description: 'Access all CLISP features on the go with our intuitive mobile application for iOS and Android devices.',
    icon: 'pi pi-mobile',
    features: ['Barcode scanning', 'Push notifications', 'Offline mode', 'Location services'],
    color: 'from-rose-500 to-pink-400'
  },
  {
    id: 6,
    title: 'API Integration',
    description: 'Seamlessly integrate CLISP with your existing business systems through our comprehensive API.',
    icon: 'pi pi-code',
    features: ['REST API', 'Webhooks', 'Custom integrations', 'Developer support'],
    color: 'from-indigo-500 to-blue-400'
  }
]);
</script>

<style scoped>
/* Additional styling can be added here if needed */
</style>
