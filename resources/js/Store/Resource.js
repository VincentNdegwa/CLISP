import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const useResourceStore = defineStore("resource_store", {
    state: () => ({
        items: {},
        loading: false,
        error: null,
        success: null,
        singleItem: {},
    }),
    actions: {
        async fetchResources(queries) {
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
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    `/api/item/${businessId}/update`,
                    data
                );
                if (response.data.error) {
                    this.error = response.data.error;
                    if (response.data.errors) {
                        this.error = response.data.errors;
                    }
                } else {
                    this.success = response.data.message;
                    this.items.data = this.items.data.map((resource) => {
                        if (resource.id === data.id) {
                            return data;
                        }
                        return resource;
                    });
                }
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message
                    : error.message;
            } finally {
                this.loading = false;
            }
        },
        async getSingleResource(itemId) {
            this.loading = true;
            this.error = null;
            const store = useUserStore();
            const businessId = store.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }
            try {
                const url = `/api/item/${businessId}/resources/${itemId}`;
                const response = await axios.get(url);
                if (response.data.error) {
                    this.error = response.data.error;
                } else {
                    this.singleItem = response.data.data;
                }
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message
                    : error.message;
            } finally {
                this.loading = false;
            }
        },
        async deleteResource(id) {
            try {
                const response = await axios.delete(`/api/item/delete/${id}`);
                if (response.data.error) {
                    this.error = response.data.error;
                    if (response.data.errors) {
                        this.error = response.data.errors;
                    }
                } else {
                    this.success = response.data.message;
                    this.items.data = this.items.data.filter(
                        (item) => item.id !== id
                    );
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
