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
                    {{ getBusinessName(transaction.initiator) }}
                </td>
                <td class="py-2 px-4 border-b">
                    {{ getBusinessName(transaction.receiver_business) }}
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
                <td class="px-6 py-4 border-b">
                    {{ formatDate(transaction.created_at) }}
                </td>
                <td>
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
    },
    methods: {
        getBusinessName(business) {
            return business ? business.business_name : "N/A";
        },
        formatDate(date) {
            const options = { year: "numeric", month: "short", day: "numeric" };
            return new Date(date).toLocaleDateString(undefined, options);
        },
    },
};
</script>
