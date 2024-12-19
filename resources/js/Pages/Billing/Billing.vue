<template>
    <Head title="Billing" />
    <AuthenticatedLayout>
        <div class="flex flex-col w-full p-5 space-y-6">
            <!-- Subscription Section -->
            <div class="border-2 rounded-lg shadow p-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="text-slate-900 text-2xl font-bold">
                            {{
                                store.subscription?.subscriptions[0]?.name +
                                    " Plan" || "Plan Name"
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
            <div class="border-2 rounded-lg shadow p-6 flex-1">
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
};
</script>

<style scoped></style>
