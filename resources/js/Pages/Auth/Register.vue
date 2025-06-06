<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const isLoading = ref(false);

const submit = () => {
    isLoading.value = true;
    form.post(route("register"), {
        onFinish: () => {
            form.reset("password", "password_confirmation");
            isLoading.value = false;
        },
    });
};
</script>

<template>
    <GuestLayout>
        <template #header>Create Your Account</template>
        
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div class="space-y-5">
                <!-- Name Field -->
                <div>
                    <InputLabel for="name" value="Full Name" class="text-white text-sm font-medium mb-2" />
                    <TextInput
                        id="name"
                        type="text"
                        class="block w-full bg-white/5 border-white/10 focus:border-rose-500 focus:ring focus:ring-rose-500/20 rounded-lg shadow-sm text-white"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="John Doe"
                    />
                    <InputError class="mt-2 text-rose-400" :message="form.errors.name" />
                </div>

                <!-- Email Field -->
                <div>
                    <InputLabel for="email" value="Email Address" class="text-white text-sm font-medium mb-2" />
                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full bg-white/5 border-white/10 focus:border-rose-500 focus:ring focus:ring-rose-500/20 rounded-lg shadow-sm text-white"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="your@email.com"
                    />
                    <InputError class="mt-2 text-rose-400" :message="form.errors.email" />
                </div>

                <!-- Password Field -->
                <div>
                    <InputLabel for="password" value="Password" class="text-white text-sm font-medium mb-2" />
                    <TextInput
                        id="password"
                        type="password"
                        class="block w-full bg-white/5 border-white/10 focus:border-rose-500 focus:ring focus:ring-rose-500/20 rounded-lg shadow-sm text-white"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError class="mt-2 text-rose-400" :message="form.errors.password" />
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <InputLabel
                        for="password_confirmation"
                        value="Confirm Password"
                        class="text-white text-sm font-medium mb-2"
                    />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="block w-full bg-white/5 border-white/10 focus:border-rose-500 focus:ring focus:ring-rose-500/20 rounded-lg shadow-sm text-white"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="••••••••"
                    />
                    <InputError
                        class="mt-2 text-rose-400"
                        :message="form.errors.password_confirmation"
                    />
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <PrimaryRoseButton
                        class="w-full py-3 justify-center shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all"
                        :class="{ 'opacity-75': form.processing }"
                        :disabled="form.processing || isLoading"
                    >
                        <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isLoading ? 'Creating Account...' : 'Create Account' }}
                    </PrimaryRoseButton>
                </div>

                <!-- Login Link -->
                <div class="text-center pt-2">
                    <p class="text-sm text-slate-400">
                        Already have an account?
                        <Link
                            :href="route('login')"
                            class="text-rose-400 hover:text-rose-300 font-medium transition-colors"
                        >
                            Sign in
                        </Link>
                    </p>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
