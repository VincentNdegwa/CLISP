<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useMyBusiness } from "@/Store/MyBusiness";
import { Head, usePage } from "@inertiajs/vue3";
import DataTable from "primevue/datatable";
import Column from "primevue/column";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        DataTable,
        Column,
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
    methods: {
        formatRole(data) {
            return `<span class="inline-block px-3 py-1 text-sm font-semibold bg-rose-100 text-rose-600 rounded-full">${data.role}</span>`;
        },
        formatStatus(data) {
            return data.business.status === "active"
                ? '<span class="text-green-600">Active</span>'
                : '<span class="text-red-600">Inactive</span>';
        },
    },
};
</script>

<template>
    <Head title="My Businesses" />
    <AuthenticatedLayout>
        <h1 class="text-3xl font-extrabold mb-6 text-gray-900">
            My Businesses
        </h1>

        <!-- Loading Skeleton -->
        <div v-if="myBusiness.loading">
            <!-- Skeleton Loader can be here or a loading spinner -->
        </div>

        <!-- Display Businesses in DataTable -->
        <div v-else-if="myBusiness.data && myBusiness.data.length > 0">
            <DataTable
                :value="myBusiness.data"
                paginator
                rows="10"
                :rowsPerPageOptions="[5, 10, 20]"
                class="p-datatable-gridlines"
            >
                <Column field="business.business_name" header="Business Name" />
                <Column field="business.location" header="Location" />
                <Column
                    field="role"
                    header="Role"
                    :body="(data) => formatRole(data)"
                />
                <Column field="business.email" header="Email" />
                <Column field="business.phone_number" header="Phone" />
                <Column
                    field="business.status"
                    header="Status"
                    :body="(data) => formatStatus(data)"
                />
                <Column field="business.trust_score" header="Trust Score" />
                <Column
                    field="business.subscription_plan"
                    header="Subscription"
                />
                <Column
                    field="business.date_registered"
                    header="Registered Date"
                />
            </DataTable>
        </div>

        <!-- No businesses message -->
        <div v-else class="text-center">
            <p class="text-gray-700">
                You are not associated with any businesses.
            </p>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Custom styles for the DataTable */
.p-datatable-gridlines {
    --primary-color: #1d4ed8; /* Example custom color */
}
</style>
