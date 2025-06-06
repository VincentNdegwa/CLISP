<script setup>
import { computed, ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PrimaryRoseButton from '@/Components/PrimaryRoseButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});
const isLoading = ref(false);

const submit = () => {
    isLoading.value = true;
    form.post(route('verification.send'), {
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <template #header>Verify Your Email</template>
        
        <Head title="Email Verification" />

        <div class="mb-6 text-slate-300 text-sm leading-relaxed">
            Thanks for signing up! Before getting started, please verify your email address by clicking on the link
            we just emailed to you. If you didn't receive the email, we can send you another one.
        </div>

        <div v-if="verificationLinkSent" class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-lg">
            <p class="text-sm text-green-500 font-medium">A new verification link has been sent to your email address.</p>
        </div>

        <form @submit.prevent="submit">
            <div class="space-y-5">
                <!-- Resend Button -->
                <div>
                    <PrimaryRoseButton
                        class="w-full py-3 justify-center shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all"
                        :class="{ 'opacity-75': form.processing }"
                        :disabled="form.processing || isLoading"
                    >
                        <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isLoading ? 'Sending...' : 'Resend Verification Email' }}
                    </PrimaryRoseButton>
                </div>

                <!-- Logout Link -->
                <div class="text-center">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-slate-400 hover:text-rose-400 text-sm transition-colors"
                    >
                        Log Out
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
