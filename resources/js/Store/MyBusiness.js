import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const useMyBusiness = defineStore("useMyBusiness", {
    state: () => ({
        data: [],
        loading: false,
        error: null,
        success: null,
    }),
    actions: {
        async fetchMyBusiness(userId) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post("/api/business/my-business", {
                    userId: userId,
                });

                if (response.error) {
                    this.error = response.data.error;
                } else {
                    this.data = response.data.data;
                }
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message
                    : error.message;
            } finally {
                this.loading = false;
            }
        },
        async fetchActiveConnection() {
            this.loading = true;
            this.error = null;
            this.success = null;
            try {
                const store = useUserStore();
                const business_id = store.business;
                if (!business_id) {
                    this.error = "Business ID not found.";
                    return;
                }
                const response = await axios.get(
                    `/api/business/get-active-connection/${business_id}`
                );
                if (response.data.error) {
                    this.error = response.data.error;
                    if (response.data.errors) {
                        this.error = response.data.errors;
                    }
                } else {
                    this.success = response.data.message;
                    this.data = response.data.data;
                }
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message
                    : error.message;
            } finally {
                this.loading = false;
            }
        },
    },
});
