<template>
    <Card class="h-fit lg:h-[35rem] max-h-[35rem]">
        <template #title>Low Stock Items</template>
        <template #content>
            <DataTable
                :value="lowStockItems"
                class="mt-4 overflow-scroll h-full lg:h-[30rem]"
            >
                <Column field="item_id" header="Item ID"></Column>
                <Column field="quantity" header="Quantity"></Column>
                <Column field="source" header="Source"></Column>
                <Column
                    field="created_at"
                    header="Created At"
                    :body="
                        (data) => new Date(data.created_at).toLocaleDateString()
                    "
                ></Column>
            </DataTable>
        </template>
    </Card>
</template>

<script>
import Card from "primevue/card";
import Column from "primevue/column";
import DataTable from "primevue/datatable";

export default {
    props: ["lowStockItems"],
    components: {
        Card,
        Column,
        DataTable,
    },
};
</script>
