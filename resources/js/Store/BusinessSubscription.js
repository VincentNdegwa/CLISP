import { defineStore } from "pinia";
import axios from "axios";
import { useUserStore } from "./UserStore";

export const useBusinessSubscriptionStore = defineStore(
    "businessSubscriptionStore",
    {
        state: () => ({
            subscription: null,
            loading: false,
            error: null,
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
        },
    }
);
