<template>
    <p>outgoing</p>
    <TableDisplay
        :loading="transactionStore.loading"
        :key="tableHeaders"
        :data_length="transactionStore?.transactions?.length"
    >
        <template v-slot:row>
            <tr
                v-for="transaction in transactionStore.transactions"
                :key="transaction.id"
                class="hover:bg-gray-100 transition-colors"
            >
                <td class="py-2 px-4 border-b">
                    {{ transaction.item }}
                </td>
                <td class="px-6 py-4 border-b">
                    {{ transaction.quantity }}
                </td>
                <td class="px-6 py-4 border-b">
                    ${{ transaction.price || 0 }}
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
                    {{ transaction.date }}
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
    data() {
        return {};
    },
};
</script>
