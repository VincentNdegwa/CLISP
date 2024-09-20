// stores/transactionStore.js

import { defineStore } from "pinia";
import axios from "axios";
import { useUserStore } from "./UserStore";

export const useTransactionStore = defineStore("transactionStore", {
    state: () => ({
        transactions: {},
        error: null,
        success: null,
        loading: false,
        singleTransaction: {},
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
                this.error = null;
                this.success = null;

                const response = await axios.post(
                    `/api/transactions/${businessId}/add-transaction`,
                    transactionData
                );
                if (response.data.error) {
                    this.error = response.data.error;
                    if (response.data.errors) {
                        this.error = response.data.errors;
                    }
                } else {
                    this.transactions.data.unshift(response.data.data);
                    this.success = "Transaction added successfully.";
                }
            } catch (error) {
                console.log(error);
                this.error = error.response?.data?.message;
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
                // console.log(response.data.data.data);

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
                const index = this.transactions.data.findIndex(
                    (transaction) => transaction.id === transactionId
                );
                if (index !== -1) {
                    this.transactions.data[index] = response.data.data; // Assuming the API returns the updated transaction
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
                this.error = null;
                this.success = null;

                const response = await axios.delete(
                    `/api/transactions/${businessId}/delete-transaction/${transactionId}`
                );

                if (response.data.error) {
                    this.error = response.data.error;
                    if (response.data.errors) {
                        this.error = response.data.errors;
                    }
                } else {
                    this.transactions.data = this.transactions.data.filter(
                        (transaction) => transaction.id != transactionId
                    );
                    this.success = response.data.message;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "An error occurred while deleting the transaction.";
            } finally {
                this.loading = false;
            }
        },

        async getSingleTransaction(transactionId) {
            const userStore = useUserStore();
            const businessId = userStore.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }

            try {
                this.loading = true;
                this.error = null;

                const response = await axios.post(
                    `/api/transactions/${businessId}/view/${transactionId}`
                );
                if (response.data.error) {
                    this.error = response.data.error;
                    if (response.data.errors) {
                        this.error = response.data.errors;
                    }
                } else {
                    this.singleTransaction = response.data.data;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "An error occurred while deleting the transaction.";
            } finally {
                this.loading = false;
            }
        },

        async handleTransactionRequest(
            url,
            transactionId,
            payload = null,
            successMessage
        ) {
            const userStore = useUserStore();
            const businessId = userStore.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }

            try {
                this.loading = true;
                this.error = null;
                this.success = null;

                let response;
                if (payload) {
                    response = await axios.post(url, payload);
                } else {
                    console.log("making response");
                    response = await axios.post(url);
                }

                if (response.data.error) {
                    this.error = response.data.error;
                    if (response.data.errors) {
                        this.error = response.data.errors;
                    }
                } else {
                    this.updateTransactionState(
                        transactionId,
                        response.data.data
                    );

                    this.success = response.data.message;
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "An error occurred while processing the transaction.";
            } finally {
                this.loading = false;
            }
        },

        // Helper method to update transaction state
        updateTransactionState(transactionId, updatedTransaction) {
            const index = this.transactions.data.findIndex(
                (transaction) => transaction.id === transactionId
            );
            if (index !== -1) {
                this.transactions.data[index] = updatedTransaction;
            }
            this.singleTransaction = updatedTransaction;
            console.log(this.singleTransaction);
        },

        // Accept transaction
        async acceptTransaction(transactionId) {
            const url = `/api/transactions/${
                useUserStore().business
            }/accept-transaction/${transactionId}`;
            await this.handleTransactionRequest(
                url,
                transactionId,
                null,
                "Transaction accepted successfully."
            );
        },

        // Reject transaction with a reason
        async rejectTransaction(transactionId, reason) {
            const url = `/api/transactions/${
                useUserStore().business
            }/reject-transaction/${transactionId}`;
            await this.handleTransactionRequest(
                url,
                transactionId,
                { reason },
                "Transaction rejected successfully."
            );
        },

        // Accept and pay transaction
        async acceptAndPayTransaction(transactionId) {
            const url = `/api/transactions/${
                useUserStore().business
            }/accept-and-pay-transaction/${transactionId}`;
            await this.handleTransactionRequest(
                url,
                transactionId,
                null,
                "Transaction accepted and paid successfully."
            );
        },

        // Pay transaction
        async payTransaction(transactionId) {
            const url = `/api/transactions/${
                useUserStore().business
            }/pay-transaction/${transactionId}`;
            await this.handleTransactionRequest(
                url,
                transactionId,
                null,
                "Transaction paid successfully."
            );
        },

        // Close transaction
        async closeTransaction(transactionId) {
            const url = `/api/transactions/${
                useUserStore().business
            }/close-transaction/${transactionId}`;
            await this.handleTransactionRequest(
                url,
                transactionId,
                null,
                "Transaction closed successfully."
            );
        },
    },
});
