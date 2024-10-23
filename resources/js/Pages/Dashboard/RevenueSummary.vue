<template>
    <div class="flex flex-col gap-2">
        <div class="font-bold text-xl">Revenue Summary</div>
        <div
            class="flex md:flex-row flex-col justify-stretch gap-3 w-full md:p-4 overflow-x-scroll"
        >
            <Card
                class="flex-1"
                v-for="(item, index) in revenueSummary"
                :key="index"
            >
                <template #title>
                    <small>
                        {{ item.title }}
                    </small>
                </template>

                <template #content>
                    <div
                        class="md:text-3xl text-xl font-bold text-ellipsis mt-3"
                    >
                        {{ formatNumber(item.revenue) }}
                    </div>
                    <div>
                        <div
                            class="flex items-center mt-3 text-red-600"
                            v-if="isNegative(item.difference)"
                        >
                            <i class="pi pi-sort-down-fill"></i>
                            <small>{{ item.difference }}%</small>
                        </div>
                        <div
                            class="flex items-center mt-3 text-green-600"
                            v-else
                        >
                            <i class="pi pi-sort-up-fill"></i>
                            <small>{{ item.difference }}%</small>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>

<script>
import { currencyConvertor } from "@/Store/CurrencyConvertStore";
import Card from "primevue/card";

export default {
    props: ["revenueSummary"],
    components: {
        Card,
    },
    methods: {
        formatNumber(amount) {
            return currencyConvertor().convertMyCurrency(amount);
        },
        isNegative(difference) {
            let sign = Math.sign(difference);
            if (sign === -1) return true;
            if (sign === 1) return false;
            return false;
        },
    },
};
</script>
