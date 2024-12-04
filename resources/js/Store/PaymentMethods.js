import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const usePaymentMethods = defineStore("usePaymentMethods", {
    state: () => ({
        methods: [],
    }),
    actions: {
        async fetchPaymentMethods(queries) {
            try {
                let url = null;
                url = `/api/business/${
                    useUserStore().business
                }/payment-methods`;

                if (queries) {
                    const queryString = new URLSearchParams(
                        queries
                    ).toString();

                    url = `/api/business/${
                        useUserStore().business
                    }/payment-methods?${queryString}`;
                }

                const response = await axios.get(url);
                this.methods = response.data;
            } catch (error) {
                console.error(error);
            }
        },
    },
});
