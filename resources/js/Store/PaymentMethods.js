import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const usePaymentMethods = defineStore("usePaymentMethods", {
    state: () => ({
        methods: [],
    }),
    actions: {
        async fetchPaymentMethods() {
            try {
                const response = await axios.get(
                    `/api/business/${useUserStore().business}/payment-methods`
                );
                this.methods = response.data;
            } catch (error) {
                console.error(error);
            }
        },
    },
});
