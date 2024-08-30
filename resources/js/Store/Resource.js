import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const useResourceStore = defineStore("resource_store", {
    state: () => ({
        items: {},
        loading: false,
        error: null,
        success: null,
    }),
    actions: {
        async fetchResources() {
            const store = useUserStore();
            const businessId = store.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }
            this.loading = true
            this.error = null
            try {
                const response = await axios.get(`/api/item/${businessId}/list`)
                if (response.data.error) {
                    this.error = response.data.error
                } else {
                    this.items = response.data.data
                    this.success = response.data.success
                }
            } catch (error) {
                this.error = error.response ? error.response.data.message : error.message;

            } finally {
                this.loading =false
            }
         },
        async addResource(item) {
            const store = useUserStore();
            const businessId = store.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }
            this.loading = true
            this.error = null

            try {
                const response = await axios.post(`/api/item/${businessId}/create`, item)
                if (response.data.error) {
                    this.error = response.data.error
                    if (response.data.errors) {
                        this.error = response.data.errors
                    }
                } else {
                    this.items.data.push(response.data.data)
                    this.success = response.data.message

                }
            } catch (error) {
                this.error = error.response ? error.response.data.message : error.message;
            }finally {
                this.loading = false;
            }
        }
    },
});
