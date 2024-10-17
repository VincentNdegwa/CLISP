<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useMyBusiness } from "@/Store/MyBusiness";
import { Head, usePage } from "@inertiajs/vue3";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import Menu from "primevue/menu";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import Badge from "primevue/badge";
import Avatar from "primevue/avatar";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        DataTable,
        Column,
        Button,
        Menu,
        TableSkeleton,
        Badge,
        Avatar,
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
    data() {
        return {
            selectedBusiness: null,
        };
    },
    methods: {
        formatRole(data) {
            return `<span class="inline-block px-3 py-1 text-sm font-semibold bg-rose-100 text-rose-600 rounded-full">${data.role}</span>`;
        },
        getStatuSeverity(status) {
            if (status === "active") {
                return "success";
            } else if (status === "inactive") {
                return "danger";
            } else {
                return "text-gray-500";
            }
        },
        toggleActionMenu(data, event) {
            console.log(data);

            this.selectedBusiness = data;
            this.$refs.menu.toggle(event);
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
            <TableSkeleton />
        </div>

        <!-- Display Businesses in DataTable -->
        <div v-else-if="myBusiness.data && myBusiness.data.length > 0">
            <DataTable :value="myBusiness.data">
                <Column>
                    <template #body="slotProps">
                        <Avatar
                            :image="
                                slotProps.data.business.logo ||
                                '/images/default-business-logo.png'
                            "
                            shape="circle"
                        />
                    </template>
                </Column>
                <Column field="business.business_name" header="Business Name" />
                <Column field="business.location" header="Location" />
                <Column
                    field="role"
                    header="Role"
                    :body="(data) => formatRole(data)"
                />
                <Column field="business.email" header="Email" />
                <Column field="business.phone_number" header="Phone" />
                <Column header="Status">
                    <template #body="slotProps">
                        <div>
                            <Badge
                                style="text-transform: capitalize"
                                :severity="
                                    getStatuSeverity(
                                        slotProps.data.business.status
                                    )
                                "
                            >
                                {{ slotProps.data.business.status }}
                            </Badge>
                        </div>
                    </template>
                </Column>

                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="card flex justify-center">
                            <Button
                                type="button"
                                icon="pi pi-ellipsis-v"
                                @click="
                                    toggleActionMenu(slotProps.data, $event)
                                "
                                aria-haspopup="true"
                                severity="contrast"
                                size="small"
                                aria-controls="action_menu"
                            />
                            <Menu
                                ref="menu"
                                :id="'action_menu_' + slotProps.data.id"
                                :model="[
                                    {
                                        label: 'Edit',
                                        icon: 'pi pi-pencil',

                                        command: () =>
                                            startEditingBusiness(
                                                selectedBusiness
                                            ),
                                    },
                                ]"
                                :popup="true"
                            />
                        </div>
                    </template>
                </Column>
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
