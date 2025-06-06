<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const currentYear = ref(new Date().getFullYear());

const companyInfo = {
  legalName: "Tech360 Systems.",
  address: "Westlands, Nairobi, Kenya",
  email: "support@tech360.com",
  phone: "+254769287724"
};

const companyLinks = [
  { name: 'About Us', url: '/about' },
  { name: 'Careers', url: '/careers' },
  { name: 'Blog', url: '/blog' },
  { name: 'Press', url: '/press' },
  { name: 'Contact', url: '/contact' }
];

const productLinks = [
  { name: 'Features', url: '/#features' },
  { name: 'Pricing', url: '/#pricing' },
  { name: 'Testimonials', url: '/#testimonials' },
  { name: 'API Documentation', url: '/docs' },
  { name: 'Release Notes', url: '/releases' }
];

const resourceLinks = [
  { name: 'Help Center', url: '/help' },
  { name: 'Community', url: '/community' },
  { name: 'Partners', url: '/partners' },
  { name: 'Resources', url: '/resources' },
  { name: 'Webinars', url: '/webinars' }
];

const legalLinks = [
  { name: 'Terms & Conditions', url: '/legal?tab=terms' },
  { name: 'Privacy Policy', url: '/legal?tab=privacy' },
  { name: 'Refund Policy', url: '/legal?tab=refund' },
  { name: 'Security', url: '/security' },
  { name: 'Acceptable Use Policy', url: '/acceptable-use' }
];

const socialLinks = [
  { name: 'Twitter', icon: 'pi pi-twitter', url: 'https://twitter.com' },
  { name: 'LinkedIn', icon: 'pi pi-linkedin', url: 'https://linkedin.com' },
  { name: 'Facebook', icon: 'pi pi-facebook', url: 'https://facebook.com' },
  { name: 'Instagram', icon: 'pi pi-instagram', url: 'https://instagram.com' },
  { name: 'GitHub', icon: 'pi pi-github', url: 'https://github.com' }
];

const newsletter = ref({
  email: '',
  submitted: false,
  error: false
});

const submitNewsletter = () => {
  if (!newsletter.value.email || !newsletter.value.email.includes('@')) {
    newsletter.value.error = true;
    return;
  }
  
  newsletter.value.submitted = true;
  newsletter.value.error = false;
  newsletter.value.email = '';
  
  // In a real application, you would send this to your backend
  console.log('Newsletter subscription submitted');
};
</script>

<template>
  <footer class="bg-slate-900 text-white pt-20 pb-10 relative overflow-hidden">
    <!-- Background elements -->
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-rose-500 via-purple-500 to-blue-500"></div>
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-rose-500/10 rounded-full filter blur-3xl"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-500/10 rounded-full filter blur-3xl"></div>
    </div>
    
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <!-- Company information banner - Required for domain review -->
      <div class="bg-slate-800/50 rounded-lg p-6 mb-12 border border-slate-700/50">
        <div class="flex flex-col md:flex-row justify-between">
          <div>
            <h2 class="text-xl font-bold text-white mb-2">CLISP - Inventory Sharing Platform</h2>
            <p class="text-slate-300 mb-4 max-w-2xl">
              CLISP is a comprehensive SaaS platform that empowers local businesses to manage, share, and optimize their inventory resources, creating a more efficient and collaborative business ecosystem.
            </p>
            <div class="text-sm text-slate-400">
              <p><strong>Legal Business Name:</strong> {{ companyInfo.legalName }}</p>
              <p><strong>Contact:</strong> {{ companyInfo.email }} | {{ companyInfo.phone }}</p>
            </div>
          </div>
          <div class="mt-6 md:mt-0 flex flex-col justify-center">
            <Link 
              href="/#pricing" 
              class="px-6 py-3 bg-rose-500 hover:bg-rose-600 text-white rounded-lg font-medium transition-colors text-center"
            >
              View Pricing Plans
            </Link>
            <div class="mt-2 text-center text-sm text-slate-400">
              <Link href="/legal?tab=refund" class="underline hover:text-rose-400">14-day money-back guarantee</Link>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Footer top section -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12 mb-16">
        <!-- Company info -->
        <div class="lg:col-span-2">
          <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-rose-500 rounded-lg flex items-center justify-center mr-3">
              <span class="font-bold text-white text-xl">C</span>
            </div>
            <h2 class="text-2xl font-bold text-white">CLISP</h2>
          </div>
          
          <p class="text-slate-300 mb-6 max-w-md">
            CLISP is transforming how local businesses manage and share inventory, creating a more efficient and collaborative business ecosystem.
          </p>
          
          <!-- Social links -->
          <div class="flex space-x-4">
            <a 
              v-for="social in socialLinks" 
              :key="social.name"
              :href="social.url"
              target="_blank"
              rel="noopener noreferrer"
              class="w-10 h-10 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center transition-colors"
              :title="social.name"
            >
              <i :class="[social.icon, 'text-slate-300']"></i>
            </a>
          </div>
        </div>
        
        <!-- Quick links -->
        <div>
          <h3 class="text-lg font-semibold mb-6 text-white">Company</h3>
          <ul class="space-y-3">
            <li v-for="link in companyLinks" :key="link.name">
              <Link 
                :href="link.url"
                class="text-slate-300 hover:text-rose-400 transition-colors"
              >
                {{ link.name }}
              </Link>
            </li>
          </ul>
        </div>
        
        <div>
          <h3 class="text-lg font-semibold mb-6 text-white">Product</h3>
          <ul class="space-y-3">
            <li v-for="link in productLinks" :key="link.name">
              <Link 
                :href="link.url"
                class="text-slate-300 hover:text-rose-400 transition-colors"
              >
                {{ link.name }}
              </Link>
            </li>
          </ul>
        </div>
        
        <div>
          <h3 class="text-lg font-semibold mb-6 text-white">Resources</h3>
          <ul class="space-y-3">
            <li v-for="link in resourceLinks" :key="link.name">
              <Link 
                :href="link.url"
                class="text-slate-300 hover:text-rose-400 transition-colors"
              >
                {{ link.name }}
              </Link>
            </li>
          </ul>
        </div>
      </div>
      
      <!-- Newsletter -->
      <div class="border-t border-slate-800 pt-12 pb-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
          <div>
            <h3 class="text-xl font-bold mb-4 text-white">Stay up to date</h3>
            <p class="text-slate-300 mb-6">
              Subscribe to our newsletter to receive updates on new features, case studies, and inventory management tips.
            </p>
            
            <div v-if="!newsletter.submitted" class="flex flex-col sm:flex-row gap-3">
              <div class="flex-grow">
                <input 
                  type="email" 
                  v-model="newsletter.email"
                  placeholder="Enter your email"
                  class="w-full px-4 py-3 bg-slate-800 border border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500 text-white"
                  :class="{ 'border-red-500': newsletter.error }"
                />
                <p v-if="newsletter.error" class="text-red-500 text-sm mt-1">Please enter a valid email address</p>
              </div>
              <button 
                @click="submitNewsletter"
                class="px-6 py-3 bg-rose-500 hover:bg-rose-600 text-white rounded-lg font-medium transition-colors whitespace-nowrap"
              >
                Subscribe
              </button>
            </div>
            
            <div v-else class="bg-slate-800 border border-green-500 rounded-lg p-4 text-green-400">
              <div class="flex items-center">
                <i class="pi pi-check-circle mr-2"></i>
                <span>Thank you for subscribing! We'll be in touch soon.</span>
              </div>
            </div>
          </div>
          
          <div class="flex flex-col sm:flex-row sm:justify-end items-start sm:items-center gap-6">
            <img src="/images/app-store.svg" alt="Download on App Store" class="h-12" />
            <img src="/images/google-play.svg" alt="Get it on Google Play" class="h-12" />
          </div>
        </div>
      </div>
      
      <!-- Footer bottom -->
      <div class="border-t border-slate-800 pt-8 mt-8">
        <!-- Legal links - Important for domain review -->
        <div class="flex flex-wrap gap-x-6 gap-y-3 justify-center mb-6">
          <Link 
            v-for="link in legalLinks" 
            :key="link.name"
            :href="link.url"
            class="text-slate-400 hover:text-slate-300 text-sm transition-colors font-medium"
          >
            {{ link.name }}
          </Link>
        </div>
        
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="text-slate-400 text-sm mb-4 md:mb-0">
            {{ currentYear }} {{ companyInfo.legalName }}. All rights reserved.
          </div>
          
          <div class="flex items-center">
            <span class="text-slate-400 text-sm mr-2">Secure payments by</span>
            <img src="/images/paddle-logo.svg" alt="Paddle" class="h-5" />
            <div class="ml-3 bg-green-600/20 text-green-400 text-xs px-2 py-1 rounded flex items-center">
              <i class="pi pi-lock mr-1 text-xs"></i>
              <span>SSL Secured</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
/* Additional styling can be added here if needed */
</style>
