// stores/transactionStore.js

import { defineStore } from "pinia";
import axios from "axios";
import { useUserStore } from "./userStore"; // Assuming this is the store where business_id is stored

export const useTransactionStore = defineStore("transactionStore", {
    state: () => ({
        transactions: [],
        error: null,
        success: null, // Add success message state
        loading: false,
    }),

    actions: {
        async addTransaction(transactionData) {
            const userStore = useUserStore();
            const businessId = userStore.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }

            try {
                this.loading = true;
                this.error = null; // Clear any previous error
                this.success = null; // Clear any previous success message

                const response = await axios.post(
                    `/api/transactions/${businessId}/add-transaction`,
                    transactionData
                );
                this.transactions.push(response.data.data); // Assuming the API returns the created transaction

                this.success = "Transaction added successfully.";
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "An error occurred while adding the transaction.";
            } finally {
                this.loading = false;
            }
        },

        async getTransaction(filters) {
            const userStore = useUserStore();
            const businessId = userStore.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }

            try {
                this.loading = true;
                this.error = null; // Clear any previous error
                this.success = null; // Clear any previous success message

                const response = await axios.post(
                    `/api/transactions/${businessId}/get-transaction`,
                    filters
                );
                this.transactions = response.data.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "An error occurred while fetching transactions.";
            } finally {
                this.loading = false;
            }
        },

        async updateTransaction(transactionId, transactionData) {
            const userStore = useUserStore();
            const businessId = userStore.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }

            try {
                this.loading = true;
                this.error = null; // Clear any previous error
                this.success = null; // Clear any previous success message

                const response = await axios.patch(
                    `/api/transactions/${businessId}/update-transaction/${transactionId}`,
                    transactionData
                );
                const index = this.transactions.findIndex(
                    (transaction) => transaction.id === transactionId
                );
                if (index !== -1) {
                    this.transactions[index] = response.data.data; // Assuming the API returns the updated transaction
                }

                this.success = "Transaction updated successfully.";
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "An error occurred while updating the transaction.";
            } finally {
                this.loading = false;
            }
        },

        async deleteTransaction(transactionId) {
            const userStore = useUserStore();
            const businessId = userStore.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }

            try {
                this.loading = true;
                this.error = null; // Clear any previous error
                this.success = null; // Clear any previous success message

                await axios.patch(
                    `/api/transactions/${businessId}/delete-transaction/${transactionId}`
                );
                this.transactions = this.transactions.filter(
                    (transaction) => transaction.id !== transactionId
                );

                this.success = "Transaction deleted successfully.";
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "An error occurred while deleting the transaction.";
            } finally {
                this.loading = false;
            }
        },
    },
});
