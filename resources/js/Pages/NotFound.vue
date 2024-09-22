<template>
    <Head title="NotFound" />
    <div class="min-h-screen flex items-center justify-center bg-white">
        <div class="text-center px-6 md:px-12">
            <!-- Error Code -->
            <h1 class="text-9xl font-bold text-rose-500 mb-4">404</h1>

            <!-- Error Message -->
            <h2 class="text-2xl md:text-3xl font-semibold text-slate-600 mb-2">
                Page Not Found
            </h2>
            <p class="text-lg text-slate-500 mb-8">
                Sorry, we can't find the page you're looking for.
            </p>

            <!-- Navigation Buttons -->
            <div class="flex justify-center space-x-4">
                <!-- Use Laravel's route helper for routing -->
                <a
                    :href="route('dashboard')"
                    class="btn bg-rose-500 text-white hover:bg-rose-600"
                >
                    Go to Dashboard
                </a>
            </div>
        </div>
    </div>
</template>

<script>
import { Head } from "@inertiajs/vue3";

export default {
    name: "NotFound",
    methods: {
        route(name) {
            return window.route(name);
        },
    },
    components: {
        Head,
    },
};
</script>

<style scoped></style>
