<script setup>
import { ref } from 'vue';

const isAnnual = ref(false);

const plans = ref([
  {
    id: 1,
    name: 'Starter',
    description: 'Perfect for small businesses just getting started with inventory sharing.',
    monthlyPrice: 29,
    annualPrice: 290, // Save ~$58 annually
    features: [
      'Basic inventory management',
      'Up to 100 inventory items',
      'Connect with 5 local businesses',
      'Email support',
      'Mobile app access'
    ],
    popular: false,
    color: 'slate'
  },
  {
    id: 2,
    name: 'Professional',
    description: 'Ideal for growing businesses with expanding inventory needs.',
    monthlyPrice: 79,
    annualPrice: 790, // Save ~$158 annually
    features: [
      'Advanced inventory management',
      'Unlimited inventory items',
      'Connect with 20 local businesses',
      'Priority email & chat support',
      'Mobile app access',
      'Analytics dashboard',
      'API access (100 requests/day)'
    ],
    popular: true,
    color: 'rose'
  },
  {
    id: 3,
    name: 'Enterprise',
    description: 'Full-featured solution for established businesses with complex inventory needs.',
    monthlyPrice: 199,
    annualPrice: 1990, // Save ~$398 annually
    features: [
      'Enterprise inventory management',
      'Unlimited inventory items',
      'Unlimited business connections',
      '24/7 priority support',
      'Mobile app access',
      'Advanced analytics & reporting',
      'Unlimited API access',
      'Custom integrations',
      'Dedicated account manager'
    ],
    popular: false,
    color: 'blue'
  }
]);

const faqs = ref([
  {
    question: 'How does the billing cycle work?',
    answer: 'You can choose between monthly or annual billing. Annual billing saves you approximately 2 months of subscription costs. You can change your billing cycle at any time.'
  },
  {
    question: 'Can I upgrade or downgrade my plan?',
    answer: 'Yes, you can upgrade or downgrade your plan at any time. When upgrading, you will be charged the prorated difference. When downgrading, the new rate will apply at the start of your next billing cycle.'
  },
  {
    question: 'Is there a free trial available?',
    answer: 'Yes, we offer a 14-day free trial for all plans. No credit card required to start your trial. You can upgrade to a paid plan at any time during or after your trial.'
  },
  {
    question: 'What payment methods do you accept?',
    answer: 'We accept all major credit cards (Visa, Mastercard, American Express), PayPal, and bank transfers for annual enterprise plans.'
  }
]);
</script>

<template>
  <section id="pricing" class="py-24 relative">
    <!-- Background elements -->
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute top-0 left-0 w-full h-64 bg-gradient-to-b from-slate-100 to-transparent dark:from-slate-900/50 dark:to-transparent"></div>
      <div class="absolute -top-40 -left-40 w-80 h-80 bg-rose-100 dark:bg-rose-900/20 rounded-full filter blur-3xl opacity-30"></div>
      <div class="absolute -bottom-20 -right-20 w-60 h-60 bg-blue-100 dark:bg-blue-900/20 rounded-full filter blur-3xl opacity-30"></div>
    </div>

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4 text-slate-900 dark:text-white">
          Simple, Transparent <span class="text-rose-500">Pricing</span>
        </h2>
        <p class="text-slate-600 dark:text-slate-300 max-w-3xl mx-auto text-lg">
          Choose the perfect plan for your business needs. All plans include core inventory sharing features.
        </p>

        <!-- Billing toggle -->
        <div class="flex items-center justify-center mt-8 mb-8">
          <span :class="`mr-3 text-sm font-medium ${!isAnnual ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400'}`">Monthly</span>
          <button
            @click="isAnnual = !isAnnual"
            type="button"
            class="relative inline-flex h-8 w-16 items-center rounded-full"
            :class="isAnnual ? 'bg-rose-500' : 'bg-slate-300 dark:bg-slate-700'"
          >
            <span class="sr-only">Toggle billing period</span>
            <span
              :class="`${isAnnual ? 'translate-x-9' : 'translate-x-1'} inline-block h-6 w-6 transform rounded-full bg-slate-50 transition-transform`"
            ></span>
          </button>
          <span :class="`ml-3 text-sm font-medium ${isAnnual ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400'}`">
            Annual <span class="text-rose-500 text-xs font-bold ml-1">Save 20%</span>
          </span>
        </div>
      </div>

      <!-- Pricing cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div
          v-for="plan in plans"
          :key="plan.id"
          class="relative rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
          :class="[
            plan.popular ?
              'border-2 border-rose-500 shadow-lg shadow-rose-500/10' :
              'border border-slate-200 dark:border-slate-700 shadow-md'
          ]"
        >
          <!-- Popular badge -->
          <div
            v-if="plan.popular"
            class="absolute top-0 right-0 bg-rose-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg"
          >
            MOST POPULAR
          </div>

          <!-- Card header -->
          <div
            class="p-6 border-b border-slate-200 dark:border-slate-700"
            :class="plan.popular ? 'bg-rose-50 dark:bg-rose-900/20' : ''"
          >
            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">{{ plan.name }}</h3>
            <p class="text-slate-600 dark:text-slate-300 text-sm h-12">{{ plan.description }}</p>

            <div class="mt-4">
              <div class="flex items-baseline">
                <span class="text-3xl font-bold text-slate-900 dark:text-white">$</span>
                <span class="text-5xl font-bold text-slate-900 dark:text-white">
                  {{ isAnnual ? plan.annualPrice / 12 : plan.monthlyPrice }}
                </span>
                <span class="ml-1 text-slate-500 dark:text-slate-400">/month</span>
              </div>

              <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ isAnnual ? 'Billed annually ($' + plan.annualPrice + '/year)' : 'Billed monthly' }}
              </p>
            </div>
          </div>

          <!-- Features list -->
          <div class="p-6">
            <ul class="space-y-4">
              <li
                v-for="(feature, index) in plan.features"
                :key="index"
                class="flex items-start"
              >
                <div :class="`w-5 h-5 rounded-full bg-${plan.color}-100 dark:bg-${plan.color}-900/30 flex items-center justify-center mr-3 mt-0.5`">
                  <i class="pi pi-check text-xs" :class="`text-${plan.color}-500`"></i>
                </div>
                <span class="text-slate-700 dark:text-slate-300 text-sm">{{ feature }}</span>
              </li>
            </ul>

            <!-- CTA button -->
            <button
              :class="[
                'w-full mt-8 py-3 px-4 rounded-lg font-medium transition-all',
                plan.popular ?
                  'bg-rose-500 hover:bg-rose-600 text-white shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40' :
                  'bg-slate-900 hover:bg-slate-800 text-white dark:bg-slate-700 dark:hover:bg-slate-600'
              ]"
            >
              Get Started
            </button>
          </div>
        </div>
      </div>

      <!-- Enterprise CTA -->
      <div class="mt-16 bg-gradient-to-r from-slate-900 to-slate-800 rounded-2xl p-8 shadow-xl relative overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div class="absolute top-0 right-0 w-1/2 h-1/2 bg-rose-500/10 rounded-full filter blur-3xl"></div>
          <div class="absolute bottom-0 left-0 w-1/2 h-1/2 bg-blue-500/10 rounded-full filter blur-3xl"></div>
        </div>

        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
          <div class="md:w-2/3">
            <h3 class="text-2xl md:text-3xl font-bold text-white mb-4">Need a custom solution?</h3>
            <p class="text-slate-300">Contact our sales team to discuss custom pricing and features tailored to your specific business requirements.</p>
          </div>

          <button class="px-6 py-3 bg-slate-50 hover:bg-slate-100 text-slate-900 rounded-lg shadow-lg transition-all font-medium">
            Contact Sales
          </button>
        </div>
      </div>

      <!-- FAQs -->
      <div class="mt-20">
        <h3 class="text-2xl font-bold text-slate-900 dark:text-white text-center mb-8">Frequently Asked Questions</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div
            v-for="(faq, index) in faqs"
            :key="index"
            class="bg-slate-50 dark:bg-slate-700/30 rounded-xl p-6"
          >
            <h4 class="text-lg font-medium text-slate-900 dark:text-white mb-3">{{ faq.question }}</h4>
            <p class="text-slate-600 dark:text-slate-300">{{ faq.answer }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Additional styling can be added here if needed */
</style>
