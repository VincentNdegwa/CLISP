<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
});

const isLoading = ref(false);

const submit = () => {
    isLoading.value = true;
    form.post(route("password.email"), {
        onFinish: () => {
            isLoading.value = false;
        },
    });
};
</script>

<template>
    <GuestLayout>
        <template #header>Reset Your Password</template>
        
        <Head title="Forgot Password" />

        <div class="mb-6 text-slate-600 dark:text-slate-300 text-sm leading-relaxed">
            Enter your email address below and we'll send you a password reset link to get back into your account.
        </div>

        <div v-if="status" class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-lg">
            <p class="text-sm text-green-500 font-medium">{{ status }}</p>
        </div>

        <form @submit.prevent="submit">
            <div class="space-y-6">
                <!-- Email Field -->
                <div>
                    <InputLabel for="email" value="Email Address" />
                    <TextInput
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="your@email.com"
                    />
                    <InputError class="mt-2 text-rose-600 dark:text-rose-400" :message="form.errors.email" />
                </div>

                <!-- Submit Button -->
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
                        {{ isLoading ? 'Sending Reset Link...' : 'Send Reset Link' }}
                    </PrimaryRoseButton>
                </div>

                <!-- Back to Login Link -->
                <div class="text-center">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Remember your password?
                        <Link
                            :href="route('login')"
                            class="text-rose-600 dark:text-rose-400 hover:text-rose-500 dark:hover:text-rose-300 font-medium transition-colors"
                        >
                            Back to login
                        </Link>
                    </p>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
