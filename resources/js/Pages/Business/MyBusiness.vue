<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useMyBusiness } from "@/Store/MyBusiness";
import { Head, usePage } from "@inertiajs/vue3";

export default {
    components: {
        AuthenticatedLayout,
        Head,
    },
    setup() {
        const { props } = usePage();
        const myBusiness = useMyBusiness();

        // Fetch the user's businesses
        myBusiness.fetchMyBusiness(props.auth.user.id);

        return {
            myBusiness,
        };
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
            v-if="myBusiness.loading"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            <!-- Skeleton Loader -->
            <div
                v-for="n in 3"
                :key="n"
                class="bg-gray-200 animate-pulse rounded-lg shadow-lg p-6"
            >
                <div class="h-6 bg-gray-300 mb-2 rounded"></div>
                <div class="h-4 bg-gray-300 mb-4 rounded"></div>
                <div class="h-4 bg-gray-300 mb-2 rounded"></div>
                <div class="h-4 bg-gray-300 mb-2 rounded"></div>
                <div class="h-4 bg-gray-300 rounded"></div>
            </div>
        </div>

        <div
            v-else-if="myBusiness.data && myBusiness.data.length > 0"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
            <!-- Business Cards -->
            <div
                v-for="business in myBusiness.data"
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
