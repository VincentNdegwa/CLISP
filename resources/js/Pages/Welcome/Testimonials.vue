<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const testimonials = ref([
  {
    id: 1,
    name: 'Sarah Johnson',
    position: 'Operations Manager',
    company: 'Metro Hardware Supplies',
    image: 'images/default-profile.png',
    content: 'CLISP has transformed how we manage our inventory. We have reduced waste by 30% and built valuable relationships with other local businesses. The platform is intuitive and the support team is always responsive.',
    rating: 5
  },
  {
    id: 2,
    name: 'Michael Chen',
    position: 'Owner',
    company: 'Chen Manufacturing',
    image: 'images/default-profile.png',
    content: "As a small manufacturing business, we often had surplus materials that went unused. With CLISP, we can now share these resources with other businesses in our area. It has been a game-changer for our bottom line.",
    rating: 5
  },
  {
    id: 3,
    name: 'Rebecca Martinez',
    position: 'Supply Chain Director',
    company: 'Coastal Distributors',
    image: 'images/default-profile.png',
    content: 'The analytics dashboard provides invaluable insights into our inventory usage patterns. We have optimized our purchasing and sharing strategies, resulting in significant cost savings.',
    rating: 4
  },
  {
    id: 4,
    name: 'David Wilson',
    position: 'CEO',
    company: 'Wilson Retail Group',
    image: 'images/default-profile.png',
    content: 'CLISP has helped us connect with complementary businesses in our area. We now share seasonal inventory items, reducing storage costs and improving our cash flow throughout the year.',
    rating: 5
  },
  {
    id: 5,
    name: 'Jennifer Lee',
    position: 'Inventory Manager',
    company: 'Sunshine Foods',
    image: 'images/default-profile.png',
    content: 'The mobile app makes it incredibly easy to scan and manage inventory on the go. We can quickly list items for sharing and respond to requests from other businesses in real-time.',
    rating: 4
  },
  {
    id: 6,
    name: 'Robert Taylor',
    position: 'Operations Director',
    company: 'Taylor Construction',
    image: 'images/default-profile.png',
    content: "We have been able to access specialized equipment through CLISP that would have been too expensive to purchase outright. The platform verification system ensures we are dealing with trustworthy partners.",
    rating: 5
  }
]);

const currentIndex = ref(0);
const autoplayInterval = ref(null);
const isMobile = ref(window.innerWidth < 768);

const nextTestimonial = () => {
  currentIndex.value = (currentIndex.value + 1) % testimonials.value.length;
};

const prevTestimonial = () => {
  currentIndex.value = (currentIndex.value - 1 + testimonials.value.length) % testimonials.value.length;
};

const startAutoplay = () => {
  stopAutoplay();
  // autoplayInterval.value = setInterval(() => {
  //   nextTestimonial();
  // }, 5000);
};

const stopAutoplay = () => {
  if (autoplayInterval.value) {
    clearInterval(autoplayInterval.value);
  }
};

const handleResize = () => {
  isMobile.value = window.innerWidth < 768;
};

onMounted(() => {
  // startAutoplay();
  window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
  stopAutoplay();
  window.removeEventListener('resize', handleResize);
});
</script>

<template>
  <section id="testimonials" class="py-24 bg-slate-50 dark:bg-slate-900 relative overflow-hidden">
    <!-- Background elements -->
    <div class="absolute inset-0">
      <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-gradient-to-bl from-rose-500/10 to-transparent rounded-bl-full blur-3xl"></div>
      <div class="absolute bottom-0 left-0 w-1/3 h-1/3 bg-gradient-to-tr from-blue-500/10 to-transparent rounded-tr-full blur-3xl"></div>
      <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-1/2 h-1/2 bg-gradient-to-br from-purple-500/5 to-transparent rounded-full blur-3xl"></div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4 text-slate-900 dark:text-white">
          What Our <span class="text-rose-500">Customers</span> Say
        </h2>
        <p class="text-slate-600 dark:text-slate-300 max-w-3xl mx-auto text-lg">
          Businesses across various industries are transforming their inventory management with CLISP.
        </p>
      </div>

      <!-- Desktop Testimonials (3 at a time) -->
      <div v-if="!isMobile" class="hidden md:block">
        <div class="grid grid-cols-3 gap-8">
          <div
            v-for="i in 3"
            :key="i"
            class="bg-slate-800/70 dark:bg-slate-800 rounded-2xl shadow-lg p-8 border border-slate-200 dark:border-slate-700 transform transition-all duration-300 hover:-translate-y-1 hover:shadow-xl relative"
          >
            <!-- Quote icon -->
            <div class="absolute -top-4 -left-4 w-8 h-8 bg-rose-500 rounded-full flex items-center justify-center shadow-lg">
              <i class="pi pi-comment text-white text-sm"></i>
            </div>

            <!-- Rating stars -->
            <div class="flex mb-6">
              <i
                v-for="star in 5"
                :key="star"
                class="pi pi-star-fill mr-1 text-sm"
                :class="star <= testimonials[(currentIndex + i - 1) % testimonials.length].rating ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600'"
              ></i>
            </div>

            <!-- Testimonial content -->
            <p class="text-slate-600 dark:text-slate-300 mb-6 italic">
              "{{ testimonials[(currentIndex + i - 1) % testimonials.length].content }}"
            </p>

            <!-- Author info -->
            <div class="flex items-center">
              <div class="w-12 h-12 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden mr-4">
                <img
                  :src="testimonials[(currentIndex + i - 1) % testimonials.length].image"
                  :alt="testimonials[(currentIndex + i - 1) % testimonials.length].name"
                  class="w-full h-full object-cover"
                  onerror="this.src='/images/no_data.svg'"
                />
              </div>
              <div>
                <h4 class="font-bold text-slate-900 dark:text-white">{{ testimonials[(currentIndex + i - 1) % testimonials.length].name }}</h4>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                  {{ testimonials[(currentIndex + i - 1) % testimonials.length].position }},
                  {{ testimonials[(currentIndex + i - 1) % testimonials.length].company }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation arrows -->
        <div class="flex justify-center mt-10 space-x-4">
          <button
            @click="prevTestimonial"
            @mouseenter="stopAutoplay"
            class="w-12 h-12 rounded-full bg-slate-800/70 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
          >
            <i class="pi pi-chevron-left text-slate-600 dark:text-slate-300"></i>
          </button>
          <button
            @click="nextTestimonial"
            @mouseenter="stopAutoplay"
            class="w-12 h-12 rounded-full bg-slate-800/70 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
          >
            <i class="pi pi-chevron-right text-slate-600 dark:text-slate-300"></i>
          </button>
        </div>
      </div>

      <!-- Mobile Testimonial (single) -->
      <div v-else class="md:hidden">
        <div class="bg-slate-800/70 dark:bg-slate-800 rounded-2xl shadow-lg p-8 border border-slate-200 dark:border-slate-700 max-w-md mx-auto relative">
          <!-- Quote icon -->
          <div class="absolute -top-4 -left-4 w-8 h-8 bg-rose-500 rounded-full flex items-center justify-center shadow-lg">
            <i class="pi pi-comment text-white text-sm"></i>
          </div>

          <!-- Rating stars -->
          <div class="flex mb-6">
            <i
              v-for="star in 5"
              :key="star"
              class="pi pi-star-fill mr-1 text-sm"
              :class="star <= testimonials[currentIndex].rating ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600'"
            ></i>
          </div>

          <!-- Testimonial content -->
          <p class="text-slate-600 dark:text-slate-300 mb-6 italic">
            "{{ testimonials[currentIndex].content }}"
          </p>

          <!-- Author info -->
          <div class="flex items-center">
            <div class="w-12 h-12 rounded-full bg-slate-200 dark:bg-slate-700 overflow-hidden mr-4">
              <img
                :src="testimonials[currentIndex].image"
                :alt="testimonials[currentIndex].name"
                class="w-full h-full object-cover"
                onerror="this.src='/images/no_data.svg'"
              />
            </div>
            <div>
              <h4 class="font-bold text-slate-900 dark:text-white">{{ testimonials[currentIndex].name }}</h4>
              <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ testimonials[currentIndex].position }},
                {{ testimonials[currentIndex].company }}
              </p>
            </div>
          </div>

          <!-- Navigation dots -->
          <div class="flex justify-center mt-8 space-x-2">
            <button
              v-for="(testimonial, index) in testimonials"
              :key="testimonial.id"
              @click="currentIndex = index"
              class="w-2.5 h-2.5 rounded-full transition-colors"
              :class="index === currentIndex ? 'bg-rose-500' : 'bg-slate-300 dark:bg-slate-600 hover:bg-slate-400 dark:hover:bg-slate-500'"
            ></button>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-8">
        <div class="text-center">
          <div class="text-4xl font-bold text-rose-500 mb-2">96%</div>
          <p class="text-slate-600 dark:text-slate-300">Customer Satisfaction</p>
        </div>
        <div class="text-center">
          <div class="text-4xl font-bold text-rose-500 mb-2">30%</div>
          <p class="text-slate-600 dark:text-slate-300">Average Cost Savings</p>
        </div>
        <div class="text-center">
          <div class="text-4xl font-bold text-rose-500 mb-2">2,000+</div>
          <p class="text-slate-600 dark:text-slate-300">Active Businesses</p>
        </div>
        <div class="text-center">
          <div class="text-4xl font-bold text-rose-500 mb-2">15+</div>
          <p class="text-slate-600 dark:text-slate-300">Industries Served</p>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Additional styling can be added here if needed */
</style>
