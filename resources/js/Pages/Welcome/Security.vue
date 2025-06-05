<script setup>
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const companyInfo = {
  legalName: "CLISP Technologies Ltd.",
  email: "security@tech360.com"
};
</script>

<template>
  <div>
    <Head title="CLISP | Security" />
    
    <div class="bg-white dark:bg-slate-900 min-h-screen">
      <!-- Header with navigation -->
      <div class="border-b border-slate-200 dark:border-slate-700">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center py-4">
            <Link href="/" class="flex items-center">
              <div class="w-10 h-10 bg-rose-500 rounded-lg flex items-center justify-center mr-3">
                <span class="font-bold text-white text-xl">C</span>
              </div>
              <h2 class="text-2xl font-bold text-slate-900 dark:text-white">CLISP</h2>
            </Link>
            <Link href="/legal" class="text-slate-600 hover:text-rose-500 dark:text-slate-300 dark:hover:text-rose-400 font-medium">
              Back to Legal Documents
            </Link>
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
            <p><strong>Contact for Security Concerns:</strong> {{ companyInfo.email }}</p>
          </div>
        </div>
        
        <!-- Security Policy -->
        <div class="prose prose-slate dark:prose-invert max-w-none">
          <h1>Security Policy</h1>
          <p class="text-lg">Last updated: June 5, 2025</p>
          
          <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-8">
            <div class="flex items-center">
              <i class="pi pi-lock text-green-600 dark:text-green-400 mr-3 text-xl"></i>
              <p class="text-green-800 dark:text-green-400 font-medium">
                CLISP is secured with SSL encryption. All data transmitted between your browser and our servers is encrypted.
              </p>
            </div>
          </div>
          
          <h2>1. Our Commitment to Security</h2>
          <p>At CLISP, we take the security of your data and our platform seriously. We implement industry-standard security measures to protect your information and ensure the integrity of our services.</p>
          
          <h2>2. Data Protection</h2>
          <p>We protect your data through:</p>
          <ul>
            <li><strong>Encryption:</strong> All data transmitted between your browser and our servers is encrypted using TLS/SSL protocols.</li>
            <li><strong>Secure Storage:</strong> Data at rest is encrypted using AES-256 encryption.</li>
            <li><strong>Regular Backups:</strong> We perform regular backups of all customer data to prevent loss.</li>
            <li><strong>Access Controls:</strong> We implement strict access controls to ensure only authorized personnel can access sensitive data.</li>
          </ul>
          
          <h2>3. Infrastructure Security</h2>
          <p>Our infrastructure security measures include:</p>
          <ul>
            <li><strong>Firewalls:</strong> Advanced firewall protection to prevent unauthorized access.</li>
            <li><strong>DDoS Protection:</strong> Systems to mitigate distributed denial-of-service attacks.</li>
            <li><strong>Intrusion Detection:</strong> 24/7 monitoring for suspicious activities.</li>
            <li><strong>Vulnerability Scanning:</strong> Regular scanning for potential vulnerabilities.</li>
            <li><strong>Secure Data Centers:</strong> Our servers are hosted in SOC 2 compliant data centers with physical security measures.</li>
          </ul>
          
          <h2>4. Payment Security</h2>
          <p>All payment processing is handled securely through our payment processor, Paddle:</p>
          <ul>
            <li>We do not store your full credit card details on our servers.</li>
            <li>Payment processing complies with PCI DSS (Payment Card Industry Data Security Standard).</li>
            <li>All payment transactions are encrypted with industry-standard protocols.</li>
          </ul>
          
          <h2>5. Account Security</h2>
          <p>We provide several features to help you secure your account:</p>
          <ul>
            <li><strong>Strong Password Requirements:</strong> We enforce minimum password strength requirements.</li>
            <li><strong>Two-Factor Authentication (2FA):</strong> Additional security layer for account access.</li>
            <li><strong>Session Management:</strong> Ability to view and terminate active sessions.</li>
            <li><strong>Login Notifications:</strong> Alerts for suspicious login attempts.</li>
          </ul>
          
          <h2>6. Security Compliance</h2>
          <p>CLISP is committed to complying with relevant security standards and regulations:</p>
          <ul>
            <li>GDPR compliance for European users</li>
            <li>Regular security audits and assessments</li>
            <li>Industry best practices for web application security</li>
          </ul>
          
          <h2>7. Security Updates</h2>
          <p>We regularly update our systems and applications to address security vulnerabilities:</p>
          <ul>
            <li>Prompt application of security patches</li>
            <li>Regular updates to third-party dependencies</li>
            <li>Continuous monitoring of security advisories</li>
          </ul>
          
          <h2>8. Security Incident Response</h2>
          <p>In the event of a security incident:</p>
          <ul>
            <li>We have a dedicated team to respond to security incidents</li>
            <li>We will promptly notify affected users as required by applicable laws</li>
            <li>We will work diligently to address and mitigate any security issues</li>
          </ul>
          
          <h2>9. User Responsibilities</h2>
          <p>To maintain security, we recommend that users:</p>
          <ul>
            <li>Use strong, unique passwords for your CLISP account</li>
            <li>Enable two-factor authentication when available</li>
            <li>Keep your login credentials confidential</li>
            <li>Log out from shared devices after use</li>
            <li>Report any suspicious activities to our security team</li>
          </ul>
          
          <h2>10. Security Vulnerability Reporting</h2>
          <p>If you discover a security vulnerability in our platform, please report it to {{ companyInfo.email }}. We appreciate responsible disclosure and will work with you to address any valid concerns.</p>
          
          <h2>11. Contact Information</h2>
          <p>For any security-related questions or concerns, please contact our security team at {{ companyInfo.email }}.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Additional styling can be added here if needed */
</style>
