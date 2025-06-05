<script setup>
import { ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';

const companyInfo = {
  legalName: "Tech360 Systems.",
  address: "Westlands, Nairobi, Kenya",
  email: "support@tech360.com",
  phone: "+254769287724",
//   registrationNumber: "C12345678"
};

const props = defineProps({
  activeTab: {
    type: String,
    required: true,
  },
});

const activeTab = ref(props.activeTab);


watch(() => props.activeTab, (newTab) => {
    console.log(newTab);
    
    activeTab.value = newTab;
}, { deep: true });



</script>

<template>
  <div>
    <Head :title="`CLISP | ${activeTab === 'terms' ? 'Terms & Conditions' : activeTab === 'privacy' ? 'Privacy Policy' : 'Refund Policy'}`" />
    
    <div class="bg-white dark:bg-slate-900 min-h-screen">
      <!-- Header with navigation tabs -->
      <div class="border-b border-slate-200 dark:border-slate-700">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex overflow-x-auto py-4 gap-8">
            <button 
              @click="activeTab = 'terms'" 
              class="px-1 py-2 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
              :class="activeTab === 'terms' ? 'border-rose-500 text-rose-600 dark:text-rose-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'"
            >
              Terms & Conditions
            </button>
            <button 
              @click="activeTab = 'privacy'" 
              class="px-1 py-2 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
              :class="activeTab === 'privacy' ? 'border-rose-500 text-rose-600 dark:text-rose-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'"
            >
              Privacy Policy
            </button>
            <button 
              @click="activeTab = 'refund'" 
              class="px-1 py-2 border-b-2 font-medium text-sm whitespace-nowrap transition-colors"
              :class="activeTab === 'refund' ? 'border-rose-500 text-rose-600 dark:text-rose-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'"
            >
              Refund Policy
            </button>
          </div>
        </div>
      </div>
      
      <!-- Content area -->
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Company information banner -->
        <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-6 mb-8 border border-slate-200 dark:border-slate-700">
          <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">Company Information</h3>
          <div class="text-sm text-slate-600 dark:text-slate-300 space-y-1">
            <p><strong>Legal Name:</strong> {{ companyInfo.legalName }}</p>
            <p><strong>Address:</strong> {{ companyInfo.address }}</p>
            <p><strong>Contact:</strong> {{ companyInfo.email }} | {{ companyInfo.phone }}</p>
            <!-- <p><strong>Registration Number:</strong> {{ companyInfo.registrationNumber }}</p> -->
          </div>
        </div>
        
        <!-- Terms & Conditions -->
        <div v-if="activeTab === 'terms'" class="prose prose-slate dark:prose-invert max-w-none">
          <h1>Terms & Conditions</h1>
          <p class="text-lg">Last updated: June 5, 2025</p>
          
          <h2>1. Introduction</h2>
          <p>Welcome to CLISP ("we," "our," or "us"). By accessing or using our inventory sharing platform and services, you agree to be bound by these Terms & Conditions. Please read them carefully.</p>
          
          <h2>2. Services Description</h2>
          <p>CLISP provides a platform for businesses to manage, share, and optimize their inventory resources. Our services include inventory management tools, resource sharing capabilities, analytics, marketplace features, mobile applications, and API integrations.</p>
          
          <h2>3. Account Registration</h2>
          <p>To use our services, you must register for an account. You agree to provide accurate, current, and complete information and to update this information to keep it accurate, current, and complete.</p>
          
          <h2>4. Subscription Plans and Payments</h2>
          <p>We offer various subscription plans with different features and pricing. Payment terms are based on the plan you select. All payments are processed securely through our payment processor, Paddle.</p>
          
          <h2>5. Billing and Renewals</h2>
          <p>Subscriptions automatically renew at the end of each billing period unless canceled. You can choose between monthly or annual billing cycles. Annual subscriptions offer a discount compared to monthly billing.</p>
          
          <h2>6. User Responsibilities</h2>
          <p>You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You agree not to share your account with any third party.</p>
          
          <h2>7. Acceptable Use Policy</h2>
          <p>You agree not to use our services for any illegal or unauthorized purpose. You will comply with all applicable laws and regulations in your use of the services.</p>
          
          <h2>8. Intellectual Property</h2>
          <p>All content, features, and functionality of our services are owned by CLISP and are protected by copyright, trademark, and other intellectual property laws.</p>
          
          <h2>9. Termination</h2>
          <p>We reserve the right to terminate or suspend your account and access to our services at our sole discretion, without notice, for conduct that we believe violates these Terms & Conditions or is harmful to other users, us, or third parties.</p>
          
          <h2>10. Changes to Terms</h2>
          <p>We may modify these Terms & Conditions at any time. We will notify you of significant changes. Your continued use of our services after such modifications constitutes your acceptance of the updated terms.</p>
          
          <h2>11. Contact Information</h2>
          <p>If you have any questions about these Terms & Conditions, please contact us at legal@tech360.com.</p>
        </div>
        
        <!-- Privacy Policy -->
        <div v-if="activeTab === 'privacy'" class="prose prose-slate dark:prose-invert max-w-none">
          <h1>Privacy Policy</h1>
          <p class="text-lg">Last updated: June 5, 2025</p>
          
          <h2>1. Introduction</h2>
          <p>This Privacy Policy describes how CLISP ("we," "our," or "us") collects, uses, and shares your personal information when you use our inventory sharing platform and services.</p>
          
          <h2>2. Information We Collect</h2>
          <p>We collect information you provide directly to us, such as your name, email address, company information, and payment details. We also collect information automatically when you use our services, including usage data, device information, and cookies.</p>
          
          <h2>3. How We Use Your Information</h2>
          <p>We use your information to provide, maintain, and improve our services; to process transactions; to communicate with you; to monitor and analyze usage trends; and to personalize your experience.</p>
          
          <h2>4. Information Sharing</h2>
          <p>We may share your information with service providers who perform services on our behalf, business partners, and as required by law. We do not sell your personal information to third parties.</p>
          
          <h2>5. Data Security</h2>
          <p>We implement appropriate security measures to protect your personal information from unauthorized access, alteration, disclosure, or destruction.</p>
          
          <h2>6. Your Rights and Choices</h2>
          <p>You have the right to access, correct, or delete your personal information. You can also opt out of receiving marketing communications from us.</p>
          
          <h2>7. International Data Transfers</h2>
          <p>Your information may be transferred to and processed in countries other than your country of residence, where data protection laws may differ.</p>
          
          <h2>8. Children's Privacy</h2>
          <p>Our services are not directed to children under 16, and we do not knowingly collect personal information from children under 16.</p>
          
          <h2>9. Changes to This Privacy Policy</h2>
          <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page.</p>
          
          <h2>10. Contact Us</h2>
          <p>If you have any questions about this Privacy Policy, please contact us at privacy@tech360.com.</p>
        </div>
        
        <!-- Refund Policy -->
        <div v-if="activeTab === 'refund'" class="prose prose-slate dark:prose-invert max-w-none">
          <h1>Refund Policy</h1>
          <p class="text-lg">Last updated: June 5, 2025</p>
          
          <h2>1. Subscription Refunds</h2>
          <p>We offer a 14-day money-back guarantee for all new subscriptions. If you're not satisfied with our services within the first 14 days of your subscription, you can request a full refund by contacting our support team at support@tech360.com.</p>
          
          <h2>2. Eligibility for Refunds</h2>
          <p>To be eligible for a refund, your request must be made within 14 days of the initial subscription purchase. Refunds are not available for subscription renewals or upgrades after the initial 14-day period.</p>
          
          <h2>3. Refund Process</h2>
          <p>Refunds will be processed using the same payment method used for the original purchase. Depending on your payment provider, it may take 5-10 business days for the refund to appear in your account.</p>
          
          <h2>4. Partial Refunds</h2>
          <p>In special circumstances, we may issue partial refunds for subscription services at our discretion. These cases will be evaluated individually by our customer support team.</p>
          
          <h2>5. Cancellation vs. Refund</h2>
          <p>Canceling your subscription will prevent future billing but does not automatically qualify you for a refund of previous payments. To request a refund, you must contact our support team directly.</p>
          
          <h2>6. Annual Subscription Refunds</h2>
          <p>For annual subscriptions, after the initial 14-day period, we may provide a prorated refund for the unused portion of your subscription at our discretion.</p>
          
          <h2>7. Non-Refundable Items</h2>
          <p>Custom development work, implementation services, and other one-time services are non-refundable once the work has begun.</p>
          
          <h2>8. Changes to Refund Policy</h2>
          <p>We reserve the right to modify this Refund Policy at any time. Changes will be effective immediately upon posting on our website.</p>
          
          <h2>9. Contact Information</h2>
          <p>If you have any questions about our Refund Policy or would like to request a refund, please contact our support team at support@tech360.com.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Additional styling can be added here if needed */
</style>
