import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const usePaymentMethods = defineStore("usePaymentMethods", {
    state: () => ({
        methods: [],
        paymentInformations: [],
        notification: {
            error: "",
            message: "",
        },
    }),
    actions: {
        async fetchPaymentMethods(queries) {
            try {
                let url = null;
                url = `/api/business/${
                    useUserStore().business
                }/payment-methods`;

                if (queries) {
                    const queryString = new URLSearchParams(queries).toString();

                    url = `/api/business/${
                        useUserStore().business
                    }/payment-methods?${queryString}`;
                }

                const response = await axios.get(url);
                this.methods = [];
                this.methods = response.data;
            } catch (error) {
                console.error(error);
            }
        },

        async fetchPaymentInformations() {
            try {
                const response = await axios.get(
                    `/api/business/${
                        useUserStore().business
                    }/payment-information`
                );
                this.paymentInformations = response.data;
            } catch (error) {
                console.error(error);
            }
        },

        async createOrUpdatePaymentInformation(params) {
            try {
                const response = await axios.post(
                    `/api/business/${
                        useUserStore().business
                    }/payment-information`,
                    params
                );

                const existingIndex = this.paymentInformations.findIndex(
                    (payment) =>
                        payment.payment_type === response.data.data.payment_type
                );

                if (existingIndex !== -1) {
                    this.paymentInformations[existingIndex] =
                        response.data.data;
                } else {
                    this.paymentInformations.push(response.data.data);
                }
                this.notification.message = response.data.message;
                this.notification.error = response.data.error;
            } catch (error) {
                console.error(error);
            }
        },

        async setDefault(payment_id) {
            try {
                const response = await axios.post(
                    `/api/business/${
                        useUserStore().business
                    }/payment-information/default/${payment_id}`
                );
                this.notification.message = response.data.message;
                this.notification.error = response.data.error;
                if (!response.data.error) {
                    let payment = response.data.data;
                    this.paymentInformations = this.paymentInformations.map(
                        (info) => {
                            if (info.id === payment.id) {
                                return { ...info, default: true };
                            }
                            return { ...info, default: false };
                        }
                    );
                }
            } catch (error) {
                console.error(error);
            }
        },
    },
});
