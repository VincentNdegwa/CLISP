import { defineStore } from "pinia";
import axios from "axios";

export const usePaymentStore = defineStore("paymentStore", {
    state: () => ({
        isLoading: false,
        successMessage: null,
        errorMessage: null,
        data: null,
    }),

    actions: {
        async createPayment(paymentData) {
            this.isLoading = true;
            this.resetMessages();

            try {
                const response = await axios.post(
                    "/api/payments/record-payment",
                    paymentData
                );
                this.data = response;
                this.handleResponse(response);
            } catch (error) {
                this.handleError(error);
            } finally {
                this.isLoading = false;
            }
        },

        resetMessages() {
            this.successMessage = null;
            this.errorMessage = null;
            this.data = null;
        },

        handleResponse(response) {
            if (!response.data.error) {
                this.successMessage =
                    response.data.message || "Payment created successfully!";
            } else {
                this.errorMessage =
                    response.data.message || "Failed to create payment.";
            }
        },

        handleError(error) {
            if (
                error.response &&
                error.response.data &&
                error.response.data.message
            ) {
                this.errorMessage = error.response.data.message;
            } else {
                this.errorMessage = "An unexpected error occurred.";
            }
        },
    },
});
