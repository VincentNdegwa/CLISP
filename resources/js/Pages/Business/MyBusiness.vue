<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

export default {
    props: ["UserBusiness"],
    components: {
        AuthenticatedLayout,
        Head,
    },
};
</script>

<template>
    <Head title="My Businesses" />
    <AuthenticatedLayout>
        <h1 class="text-3xl font-extrabold mb-6 text-gray-900">
            My Businesses
        </h1>
        <div
            v-if="UserBusiness.length"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            <div
                v-for="business in UserBusiness"
                :key="business.id"
                class="bg-gray-100 text-slate-900 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-all scale-95 hover:scale-100 duration-300 ease-in-out"
            >
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        {{ business.business.business_name }}
                    </h2>
                    <p class="text-sm font-medium text-gray-500 mb-4">
                        {{ business.business.location }}
                    </p>
                    <div class="mb-4">
                        <span
                            class="inline-block px-3 py-1 text-sm font-semibold bg-rose-100 text-rose-600 rounded-full"
                        >
                            {{ business.role }}
                        </span>
                    </div>
                    <div class="text-gray-700 space-y-1">
                        <p>
                            <strong>Email:</strong>
                            {{ business.business.email }}
                        </p>
                        <p>
                            <strong>Phone:</strong>
                            {{ business.business.phone_number }}
                        </p>
                        <p>
                            <strong>Status:</strong>
                            <span
                                :class="
                                    business.business.status === 'active'
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                "
                            >
                                {{ business.business.status }}
                            </span>
                        </p>
                        <p>
                            <strong>Trust Score:</strong>
                            {{ business.business.trust_score }}
                        </p>
                        <p>
                            <strong>Subscription:</strong>
                            {{ business.business.subscription_plan }}
                        </p>
                        <p>
                            <strong>Registered:</strong>
                            {{ business.business.date_registered }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center">
            <p class="text-gray-700">
                You are not associated with any businesses.
            </p>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.container {
    max-width: 1200px;
    margin: 0 auto;
}
</style>
