<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useTransactionStore } from "@/Store/TransactionStore";
import { Head } from "@inertiajs/vue3";

export default {
    components: {
        AuthenticatedLayout,
        PrimaryButton,
        Head,
    },
    props: {
        transactionId: {
            required: true,
            type: String,
        },
    },
    setup(props) {
        const transactionStore = useTransactionStore();

        onMounted: {
            transactionStore.getSingleTransaction(props.transactionId);
        }

        return {
            transactionStore,
        };
    },
    methods: {
        convertCurrency(currency) {
            if (currency) {
                return Intl.NumberFormat({
                    style: "currency",
                    currency: "KES",
                }).format(currency);
            } else {
                ("0.0");
            }
        },
    },
};
</script>

<template>
    <Head title="Transaction" />
    <AuthenticatedLayout>
        <!-- Minimalist Transaction Overview -->
        <div class="bg-white px-10 rounded-lg shadow-lg">
            <h1 class="text-2xl font-semibold mb-1">
                Transaction #{{ transactionStore.singleTransaction.id }}
            </h1>
            <div class="flex justify-between">
                <div>
                    <p>
                        <strong>Type:</strong>
                        {{ transactionStore.singleTransaction.type }}
                    </p>
                    <p>
                        <strong>Status:</strong>
                        <span :class="statusClass">{{
                            transactionStore.singleTransaction.status
                        }}</span>
                    </p>
                </div>
                <div class="text-right">
                    <p>
                        <strong>Total Price:</strong>
                        {{
                            convertCurrency(
                                transactionStore.singleTransaction.totalPrice
                            )
                        }}
                    </p>
                    <p>
                        <strong>Transaction Type:</strong>
                        {{
                            transactionStore.singleTransaction.transaction_type
                        }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Initiator & Receiver Section -->
        <div class="flex flex-col md:flex-row gap-6 mb-8">
            <!-- Initiator Card -->
            <div class="bg-gray-50 p-4 rounded-lg shadow-sm flex-grow">
                <h2 class="text-lg font-medium mb-2">Initiator</h2>
                <p>
                    <strong>Business Name:</strong>
                    {{
                        transactionStore.singleTransaction.initiator
                            .business_name
                    }}
                </p>
                <p>
                    <strong>Business ID:</strong>
                    {{
                        transactionStore.singleTransaction.initiator.business_id
                    }}
                </p>
            </div>

            <!-- Receiver Card -->
            <div class="bg-gray-50 p-4 rounded-lg shadow-sm flex-grow">
                <h2 class="text-lg font-medium mb-2">Receiver</h2>
                <p v-if="transactionStore.singleTransaction.receiver_business">
                    <strong>Business Name:</strong>
                    {{
                        transactionStore.singleTransaction.receiver_business
                            .business_name
                    }}
                </p>
                <p v-if="transactionStore.singleTransaction.receiver_customer">
                    <strong>Customer ID:</strong>
                    {{
                        transactionStore.singleTransaction.receiver_customer.id
                    }}
                </p>
            </div>
        </div>

        <!-- Details Section -->
        <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
            <h2 class="text-lg font-medium mb-4">Details</h2>
            <p>
                <strong>Shipping Fees:</strong> ${{
                    transactionStore.singleTransaction.details.shipping_fees
                }}
            </p>
        </div>

        <!-- Items Section -->
        <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
            <h2 class="text-lg font-medium mb-4">Items</h2>
            <div class="overflow-x-auto">
                <table class="w-full table-auto text-left">
                    <thead class="border-b">
                        <tr>
                            <th class="px-4 py-2">Item ID</th>
                            <th class="px-4 py-2">Quantity</th>
                            <th class="px-4 py-2">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in transactionStore.singleTransaction
                                .items"
                            :key="item.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-2">{{ item.item_id }}</td>
                            <td class="px-4 py-2">{{ item.quantity }}</td>
                            <td class="px-4 py-2">${{ item.price }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-4">
            <PrimaryButton
                class="bg-gray-800 text-white px-6 py-2 rounded hover:bg-gray-700"
                >Approve</PrimaryButton
            >
            <PrimaryButton
                class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-400"
                >Cancel</PrimaryButton
            >
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Minimalist status styles */
.status-pending {
    color: #f97316;
}

.status-completed {
    color: #22c55e;
}

.bg-white {
    background-color: #ffffff;
}

.bg-gray-50 {
    background-color: #f9fafb;
}

.shadow-sm {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
</style>
