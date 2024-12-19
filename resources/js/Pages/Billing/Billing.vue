<template>
    <Head title="Billing" />
    <AuthenticatedLayout>
        <div class="flex flex-col w-full p-5 space-y-6">
            <!-- Loader -->
            <div v-if="loading" class="flex justify-center items-center">
                <div class="loader"></div>
            </div>

            <!-- Subscription Section -->
            <div v-else class="border-2 rounded-lg shadow p-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="text-slate-900 text-2xl font-bold">
                            {{
                                store.subscription?.subscriptions[0]?.name ||
                                "Plan Name"
                            }}
                        </div>
                        <div class="text-rose-500 text-sm">
                            ({{
                                store.subscription?.subscriptions[0]?.items[0]
                                    ?.plan.billing_cycle || "Billing Cycle"
                            }})
                        </div>
                    </div>
                    <div class="text-slate-900 text-lg font-semibold">
                        Price:
                        {{
                            store.subscription?.subscriptions[0]?.items[0]?.plan
                                .price || "--"
                        }}
                        {{
                            store.subscription?.subscriptions[0]?.items[0]?.plan
                                .currency || ""
                        }}
                    </div>
                </div>
                <div class="mt-4 flex gap-10">
                    <div class="flex flex-col">
                        <div class="text-slate-500 text-sm">Start Date</div>
                        <div class="text-lg font-bold">
                            {{
                                formatDate(
                                    store.subscription?.last_payment?.date
                                )
                            }}
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <div class="text-slate-500 text-sm">
                            Next Billing Date
                        </div>
                        <div class="text-lg font-bold">
                            {{
                                formatDate(
                                    store.subscription?.next_payment?.date
                                )
                            }}
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <div class="text-slate-500 text-sm">
                            Next Billing Amount
                        </div>
                        <div class="text-lg font-bold">
                            {{ store.subscription?.last_payment?.amount }}
                        </div>
                    </div>
                </div>
                <div class="mt-4 flex gap-4">
                    <PrimaryButton
                        type="button"
                        class="bg-slate-900 text-white"
                    >
                        Upgrade Plan
                    </PrimaryButton>
                    <PrimaryButton type="button" class="bg-rose-500 text-white">
                        Cancel Plan
                    </PrimaryButton>
                </div>
            </div>

            <!-- Transactions Section -->
            <div v-else class="border-2 rounded-lg shadow p-6 flex-1">
                <h2 class="text-slate-900 text-xl font-bold mb-4">
                    Transaction History
                </h2>
                <!-- <DataTable
                    :value="store.subscription?.transactions.flat()"
                    class="p-datatable-striped"
                >
                    <Column
                        field="id"
                        header="Transaction ID"
                        :sortable="true"
                    ></Column>
                    <Column
                        field="invoice_number"
                        header="Invoice Number"
                        :sortable="true"
                    ></Column>
                    <Column
                        field="total"
                        header="Amount"
                        :sortable="true"
                    ></Column>
                    <Column
                        field="status"
                        header="Status"
                        :sortable="true"
                    ></Column>
                    <Column
                        field="billed_at"
                        header="Date"
                        :sortable="true"
                    ></Column>
                </DataTable> -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useBusinessSubscriptionStore } from "@/Store/BusinessSubscription";
import { Head } from "@inertiajs/vue3";
import { onMounted } from "vue";
import Column from "primevue/column";
import DataTable from "primevue/datatable";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        DataTable,
        Column,
    },
    setup() {
        const store = useBusinessSubscriptionStore();

        onMounted(() => {
            store.getBilling();
        });

        return {
            store,
        };
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return "--";
            const date = new Date(dateString);
            return `${date.toLocaleDateString()} ${date.toLocaleTimeString()}`;
        },
    },
    data() {
        return {
            loading: this.store.loading,
        };
    },
};
</script>

<style scoped>
.loader {
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-left-color: #000;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
