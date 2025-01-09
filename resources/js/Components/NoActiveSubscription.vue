<template>
    <div class="p-6 bg-white">
        <h2 class="text-slate-900 text-xl font-semibold mb-4">
            No Active Subscription
        </h2>
        <p class="text-slate-900 mb-6">
            The business, {{ business.business_name }}, has no active
            subscription.
        </p>
        <a
            :href="route('billing.view')"
            class="bg-rose-500 text-white px-4 py-4 mt-5 rounded hover:bg-rose-600"
        >
            Go to Billing
        </a>
    </div>
</template>

<script>
import Modal from "./Modal.vue";

export default {
    components: {},
    props: {
        business: {
            type: Object,
            required: true,
        },
    },
    methods: {},
};
</script>

<style scoped>
.bg-white {
    background-color: #ffffff;
}
.text-slate-900 {
    color: #1e293b;
}
.bg-rose-500 {
    background-color: #f43f5e;
}
.hover\:bg-rose-600:hover {
    background-color: #e11d48;
}
</style>
