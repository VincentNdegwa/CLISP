<template>
    <div class="flex flex-col gap-2">
        <div class="font-bold text-xl">Business Summary</div>
        <div
            class="flex md:flex-row flex-col justify-stretch gap-3 w-full md:p-4 overflow-x-scroll"
        >
            <Card class="flex-1">
                <template #title>
                    <small> Active Business Connections </small>
                </template>
                <template #content>
                    <div class="flex items-center justify-between">
                        <div
                            class="md:text-3xl text-xl font-bold text-ellipsis mt-3"
                        >
                            {{ businessSummary.businessConnection }}
                        </div>
                        <i class="pi pi-briefcase mt-8 text-rose-500"></i>
                    </div>
                </template>
            </Card>

            <Card class="flex-1">
                <template #title>
                    <small> Total Items </small>
                </template>
                <template #content>
                    <div class="flex items-center justify-between">
                        <div
                            class="md:text-3xl text-xl font-bold text-ellipsis mt-3"
                        >
                            {{ businessSummary.totalItems }}
                        </div>
                        <i class="pi pi-box mt-8 text-rose-500"></i>
                    </div>
                </template>
            </Card>
            <Card class="flex-1">
                <template #title>
                    <small> Total Items Value </small>
                </template>
                <template #content>
                    <div
                        class="md:text-3xl text-xl font-bold text-ellipsis mt-3"
                    >
                        {{ formatNumber(businessSummary.totalItemsValue) }}
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
    props: ["businessSummary"],
    components: {
        Card,
    },
    setup() {},
    methods: {
        formatNumber(amount) {
            return currencyConvertor().convertMyCurrency(amount);
        },
    },
};
</script>
