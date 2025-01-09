import { defineStore } from "pinia";
import axios from "axios";
import { useUserStore } from "./UserStore";
import TransactionDisplay from "@/Pages/Trade/TransactionDisplay.vue";

export const useBusinessSubscriptionStore = defineStore("businessSubscriptionStore", {
    state: () => ({
        subscription: null,
        transactions: [],
        loading: false,
        error: null,
        success: null,
    }),
    actions: {
        async getBilling() {
            this.loading = true;
            this.error = null;
            try {
                const url = `/api/${useUserStore().business}/billing`;
                const response = await axios.get(url);
                this.subscription = response.data;
            } catch (err) {
                this.error = err.message;
            } finally {
                this.loading = false;
            }
        },
        async getBillingTransactions(page = 1, rows = 20) {
            this.loading = true;
            this.error = null;
            try {
                const url = `/api/${
                    useUserStore().business
                }/billing/transaction?page=${page}&rows=${rows}`;

                const response = await axios.get(url);
                this.transactions = response.data;
            } catch (err) {
                this.error = err.message;
            } finally {
                this.loading = false;
            }
        },
        async cancelBilling(cancelType) {
            this.loading = true;
            this.error = null;
            try {
                let url = `/api/${
                    useUserStore().business
                }/billing/cancel-subscription`;
                if (cancelType) {
                    url += `?cancelType=${cancelType}`;
                }
                const response = await axios.post(url);
                if (response.data.error) {
                    this.error = response.data.message;
                } else {
                    this.success = response.data.message;
                }
            } catch (err) {
                this.error = err.message;
            } finally {
                this.loading = false;
            }
        },
        async ChangePlan(params) {
            this.loading = true;
            this.error = null;
            try {
                const url = `/api/business/${
                    useUserStore().business
                }/change-plan`;
                const response = await axios.post(url, params);
                if (response.data.error) {
                    this.error = response.data.message;
                } else {
                    this.success = response.data.message;
                }
            } catch (err) {
                this.error = err.message;
            } finally {
                this.loading = false;
            }
        },
    },
});
