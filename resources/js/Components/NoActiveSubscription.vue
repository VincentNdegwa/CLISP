<template>
    <div class="p-6 bg-white">
        <h2 class="text-slate-900 text-xl font-semibold mb-4">
            No Active Subscription
        </h2>
        <p class="text-slate-900 mb-6">
            The business, {{ business.business_name }}, has no active
            subscription.
        </p>
        <button
            @click="goToBilling"
            class="bg-rose-500 text-white px-4 py-2 rounded hover:bg-rose-600"
        >
            Go to Billing
        </button>
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
    methods: {
        goToBilling() {
            this.$router.push({ name: "billing.view" });
        },
    },
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
