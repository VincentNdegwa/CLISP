import { defineStore } from "pinia";
import axios from "axios";
import { useUserStore } from "./UserStore";

export const useResourceCategoryStore = defineStore("resource_category", {
    state: () => ({
        items: {},
        loading: false,
        error: null,
        success: null
    }),
    actions: {
        async fetchResourceCategory() {
            const store = useUserStore();
            const businessId = store.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }

            this.loading = true;
            this.error = null;
            try {
                const response = await axios.get(`/api/category/${businessId}/list`);
                if (response.data.error) {
                    this.error = response.data.message;
                    if (response.data.errors) {
                        this.error = response.data.errors
                    }
                } else {
                    this.items = response.data.data;
                }
            } catch (error) {
                this.error = error.response ? error.response.data.message : error.message;
            } finally {
                this.loading = false;
            }
        },
        async addItem(item) {
            const store = useUserStore();
            const businessId = store.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }

            this.loading = true;
            this.error = null;
            try {
                const response = await axios.post(`/api/category/${businessId}/create`, item);

                if (response.data.error) {
                    this.error = response.data.message;
                    if (response.data.errors) {
                        this.error = response.data.errors
                    }
                } else {
                    this.success = response.data.message
                    this.items.data.push(response.data.data);
                }
            } catch (error) {
                this.error = error.response ? error.response.data.message : error.message;
            } finally {
                this.loading = false;
            }
        },
        removeItem(itemId) {
            this.items = this.items.data.filter((item) => item.id !== itemId);
        },

        async updateCategory(category) {
            
        }
    },
});
