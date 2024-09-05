import axios from "axios";
import { defineStore } from "pinia";

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
    },
});
