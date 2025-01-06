<template>
    <Head title="Billing" />
    <AuthenticatedLayout>
        <!-- Loader -->
        <div
            v-if="loading || !store.subscription?.business_id"
            class="flex justify-center items-center"
        >
            <LoadingUI />
        </div>
        <div v-else class="flex flex-col w-full p-5 space-y-6 h-[90vh]">
            <!-- Subscription Section -->
            <div
                class="flex flex-col gap-7 border-2 rounded-lg shadow p-6 h-[25vh]"
            >
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
                <div class="flex gap-10">
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
                <div class="flex gap-4">
                    <PrimaryButton
                        type="button"
                        class="bg-slate-900 text-white"
                        @click="upgradePlan"
                    >
                        Upgrade Plan
                    </PrimaryButton>
                    <PrimaryButton
                        v-if="store.subscription.next_payment"
                        type="button"
                        class="bg-rose-500 text-white"
                        @click="
                            openAction(
                                'Cancel Plan',
                                'Are you sure you want to cancel your plan?',
                                cancelBilling
                            )
                        "
                    >
                        Cancel Plan
                    </PrimaryButton>
                </div>
            </div>

            <!-- Transactions Section -->
            <div
                v-if="store.transactions?.data?.length > 0"
                class="border-2 rounded-lg shadow p-6 flex-1 h-[65vh] overflow-auto"
            >
                <h2 class="text-slate-900 text-xl font-bold mb-4">
                    Transaction History
                </h2>
                <DataTable
                    :value="store.transactions?.data"
                    class="p-datatable-striped"
                >
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
                    <Column field="billed_at" header="Date" :sortable="true">
                        <template #body="slotProps">
                            {{ formatDate(slotProps.data.billed_at) }}
                        </template>
                    </Column>
                </DataTable>
            </div>
            <Paginator
                :totalRecords="store.transactions?.total"
                :rows="paginationParams.rows"
                :first="(paginationParams.page - 1) * paginationParams.rows"
                @page="onPageOrRowChange"
                @update:rows="onPageOrRowChange"
                :rowsPerPageOptions="[10, 20, 50]"
            />
        </div>
    </AuthenticatedLayout>

    <ConfirmationModal
        :isOpen="confirmation.isOpen"
        :title="confirmation.title"
        :message="confirmation.message"
        @confirm="confirmation.confirm"
        @close="cancelAction"
    />
    <AlertNotification
        :status="notification.status"
        :message="notification.message"
        :open="notification.open"
    />
</template>

<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useBusinessSubscriptionStore } from "@/Store/BusinessSubscription";
import { Head } from "@inertiajs/vue3";
import { onMounted, ref, watch } from "vue";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import LoadingUI from "@/Components/LoadingUI.vue";
import Paginator from "primevue/paginator";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import AlertNotification from "@/Components/AlertNotification.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        DataTable,
        Column,
        LoadingUI,
        Paginator,
        ConfirmationModal,
        AlertNotification,
    },
    setup() {
        const store = useBusinessSubscriptionStore();
        let loading = store.loading;
        const notification = ref({
            open: true,
            message: "",
            status: "error",
        });
        const paginationParams = ref({ page: 1, rows: 20 });

        onMounted(() => {
            loading = true;
            store.getBilling();
            fetchTransactions();
        });

        watch(store, (newStore, oldStore) => {
            if (newStore.error) {
                notification.value = {
                    open: true,
                    message: newStore.error,
                    status: "error",
                };
            }

            if (newStore.success) {
                notification.value = {
                    open: true,
                    message: newStore.success,
                    status: "success",
                };
            }
        });

        const fetchTransactions = async () => {
            await store.getBillingTransactions(
                paginationParams.value.page,
                paginationParams.value.rows
            );
        };
        const cancelBilling = async () => {
            await store.cancelBilling();
        };

        const onPageOrRowChange = (event) => {
            const newPage = event.page + 1 || paginationParams.value.page;
            const newRows = event.rows || paginationParams.value.rows;

            if (
                newPage !== paginationParams.value.page ||
                newRows !== paginationParams.value.rows
            ) {
                paginationParams.value.page = newPage;
                paginationParams.value.rows = newRows;
                fetchTransactions();
            }
        };

        return {
            store,
            loading,
            paginationParams,
            onPageOrRowChange,
            cancelBilling,
            notification,
        };
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return "--";
            const date = new Date(dateString);
            return `${date.toLocaleDateString()} ${date.toLocaleTimeString()}`;
        },
        upgradePlan() {
            console.log("Upgrade Plan");
        },
        cancelPlan() {
            console.log("Cancel Plan");
        },
        openAction(title, message, confirm) {
            console.log("Open Action");

            this.confirmation.isOpen = true;
            this.confirmation.title = title;
            this.confirmation.message = message;
            this.confirmation.confirm = confirm;
        },
        cancelAction() {
            this.confirmation.isOpen = false;
        },
    },
    data() {
        return {
            confirmation: {
                isOpen: false,
                title: "Confirm Action",
                message: "Are you sure you want to proceed?",
                confirm: () => {
                    this.confirmation.isOpen = false;
                },
            },
        };
    },
};
</script>

<style scoped></style>
