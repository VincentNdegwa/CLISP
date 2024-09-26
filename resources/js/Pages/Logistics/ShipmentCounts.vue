<template>
    <div class="p-2">
        <h1 class="text-2xl font-extrabold border-b">
            Dispatch Items To Recipient
        </h1>
        <div class="content mt-5">
            <div class="flex flex-col sm:flex-row sm:justify-between">
                <div class="card flex flex-row gap-1 items-center">
                    <span class="font-extrabold text-l">Initiator:</span>
                    <span class="text-l">{{
                        transaction.initiator?.business_name
                    }}</span>
                </div>
                <div class="card flex flex-row gap-1 items-center">
                    <span class="font-extrabold text-l">Receiver:</span>
                    <span v-if="transaction.receiver_business" class="text-l">{{
                        transaction.receiver_business
                            ? transaction.receiver_business.business_name
                            : "---"
                    }}</span>
                    <span v-if="transaction.receiver_customer" class="text-l">{{
                        transaction.receiver_customer
                            ? transaction.receiver_customer.full_name
                            : "---"
                    }}</span>
                </div>
            </div>

            <div class="mt-5">
                <h4 class="font-extrabold">Items</h4>
                <DataTable :value="transaction.items" class="mt-3">
                    <Column header="Item Name">
                        <template #body="itemSlotProps">
                            {{ itemSlotProps.data.item.item_name }}
                        </template>
                    </Column>
                    <Column field="quantity" header="Quantity"></Column>
                    <Column header="Quantity to Ship">
                        <template #body="itemSlotProps">
                            <input
                                type="number"
                                readonly
                                class="border rounded-md border-slate-700 min-w-[100px]"
                                v-model="itemSlotProps.data.quantity_ship"
                                :min="1"
                                :max="itemSlotProps.data.quantity"
                            />
                        </template>
                    </Column>
                </DataTable>

                <div class="mt-4">
                    <div class="flex flex-col md:flex-row gap-2">
                        <Button
                            class="flex-1"
                            label="Cancel"
                            severity="danger"
                            @click="$emit('close')"
                            size="large"
                        />
                        <Button
                            class="flex-1"
                            label="Dispatch"
                            @click="dispatchItems"
                            severity="contrast"
                            size="large"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";

export default {
    props: {
        transaction: {
            type: Object,
            required: true,
        },
    },
    data() {
        return {
            dispatchParams: {
                transaction_id: "",
                transaction_type: "",
                items: [],
            },
        };
    },
    methods: {
        dispatchItems() {
            this.dispatchParams.transaction_id = this.transaction.id;
            this.dispatchParams.transaction_type = this.transaction.type;
            this.dispatchParams.items = this.transaction.items.map((item) => ({
                item_id: item.item.id,
                quantity: item.quantity,
                quantity_ship: item.quantity_ship,
            }));
            this.$emit("close");
            this.$emit("dispatchItems", this.dispatchParams);
        },
    },
    components: {
        DataTable,
        Column,
        Button,
    },
};
</script>

<style scoped></style>
