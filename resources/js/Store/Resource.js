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
        async fetchResources(queries) {
            console.log('loadibg the queries');

            const store = useUserStore();
            const businessId = store.business;

            let link = `/api/item/${businessId}/list`;
            if (queries) {
                if (queries.search) {
                    link += `?search=${queries.search}`;
                }
                if (queries.category) {
                    link += `?category=${queries.category}`;
                }
            }
            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(link);
                if (response.data.error) {
                    this.error = response.data.error;
                } else {
                    this.items = response.data.data;
                    this.success = response.data.success;
                }
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message
                    : error.message;
            } finally {
                this.loading = false;
            }
        },
        async addResource(item) {
            const store = useUserStore();
            const businessId = store.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    `/api/item/${businessId}/create`,
                    item
                );
                if (response.data.error) {
                    this.error = response.data.error;
                    if (response.data.errors) {
                        this.error = response.data.errors;
                    }
                } else {
                    this.items.data.push(response.data.data);
                    this.success = response.data.message;
                }
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message
                    : error.message;
            } finally {
                this.loading = false;
            }
        },
        async updateResource(data) {
            const store = useUserStore();
            const businessId = store.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }
            console.log('in the store');
            this.loading = true;
            this.error = null;

            try {
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