<template>
    <TableDisplay
        :loading="transactionStore.loading"
        :keys="tableHeaders"
        :data_length="transactionStore?.transactions?.data?.length"
    >
        <template v-slot:row>
            <tr
                v-for="transaction in transactionStore.transactions.data"
                :key="transaction.id"
                class="hover:bg-gray-100 transition-colors"
            >
                <td class="py-2 px-4 border-b">
                    <span v-if="transaction.transaction_type == 'Outgoing'">
                        <i class="bi bi-arrow-up-right"></i>
                    </span>
                    <span
                        v-else-if="transaction.transaction_type == 'Incoming'"
                    >
                        <i class="bi bi-arrow-down-left"></i>
                    </span>
                    <span v-else>
                        <i class="bi bi-arrow-down-up"></i>
                    </span>
                </td>
                <td class="py-2 px-4 border-b">
                    {{ getBusinessName(transaction.initiator) }}
                </td>
                <td class="py-2 px-4 border-b" v-if="isB2B">
                    {{ getBusinessName(transaction.receiver_business) }}
                </td>
                <td class="py-2 px-4 border-b" v-else>
                    {{ getCustomerName(transaction.receiver_customer) }}
                </td>

                <td class="px-6 py-4 border-b">
                    <span
                        :class="{
                            'text-yellow-500': transaction.status === 'pending',
                            'text-green-500':
                                transaction.status === 'completed',
                            'text-red-500': transaction.status === 'canceled',
                        }"
                    >
                        {{ transaction.status }}
                    </span>
                </td>

                <td class="py-2 px-4 border-b">
                    {{ convertCurrency(transaction.totalPrice) }}
                </td>

                <td class="px-6 py-4 border-b">
                    {{ formatDate(transaction.created_at) }}
                </td>
                <td class="px-6 py-4 border-b">
                    <div class="dropdown dropdown-left">
                        <div
                            tabindex="0"
                            class="btn btn-xs bg-green-500 text-white"
                        >
                            Action
                        </div>
                        <ul
                            tabindex="0"
                            class="dropdown-content menu bg-white rounded-box z-[100] w-52 p-2 shadow"
                        >
                            <li>
                                <a href="/">View</a>
                            </li>
                            <li>
                                <a href="/">Edit</a>
                            </li>
                            <li>
                                <a href="/">Delete</a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        </template>
    </TableDisplay>
</template>

<script>
import TableDisplay from "@/Layouts/TableDisplay.vue";

export default {
    components: {
        TableDisplay,
    },
    props: {
        transactionStore: {
            type: Object,
            required: true,
        },
        tableHeaders: {
            type: Object,
            required: true,
        },
        isB2B: {
            required: true,
            boolead: true,
            default: true,
        },
    },
    methods: {
        getBusinessName(business) {
            return business ? business.business_name : "N/A";
        },
        formatDate(date) {
            const options = {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "numeric",
                minute: "numeric",
                second: "numeric",
                hour12: false,
            };
            return new Date(date).toLocaleDateString(undefined, options);
        },
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
        getCustomerName(customer) {
            return customer ? customer.full_names : "N/A";
        },
    },
};
</script>
