import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const useWarehouseStore = defineStore("warehouse_store", {
    state: () => ({
        warehouses: {
            data: [],
            current_page: 1,
            last_page: 1,
            total: 0,
        },
        loading: false,
        error: null,
        success: null,
        singleWarehouse: {},
    }),
    actions: {
        async fetchWarehouses(queries = {}) {
            const userStore = useUserStore();
            const businessId = userStore.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }

            // Build the query parameters
            const params = new URLSearchParams();
            params.append("business_id", businessId);

            if (queries.search) params.append("search", queries.search);
            if (queries.page) params.append("page", queries.page);
            if (queries.rows) params.append("rows", queries.rows);

            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(
                    `/api/warehouses?${params.toString()}`
                );
                this.warehouses = response.data;
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message || error.response.data.errors
                    : error.message;
            } finally {
                this.loading = false;
            }
        },

        async getWarehouse(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/warehouses/${id}`);
                this.singleWarehouse = response.data;
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message || error.response.data.errors
                    : error.message;
            } finally {
                this.loading = false;
            }
        },

        async createWarehouse(warehouse) {
            const userStore = useUserStore();
            const businessId = userStore.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }

            // Add business_id to the warehouse data
            warehouse.business_id = businessId;

            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post("/api/warehouses", warehouse);
                this.success = response.data.message;

                // Update the warehouses list if needed
                if (this.warehouses.data) {
                    this.warehouses.data.unshift(response.data.warehouse);
                }

                return response.data.warehouse;
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message || error.response.data.errors
                    : error.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        async updateWarehouse(warehouse) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.put(
                    `/api/warehouses/${warehouse.id}`,
                    warehouse
                );
                this.success = response.data.message;

                // Update in local state if it exists
                if (this.warehouses.data) {
                    this.warehouses.data = this.warehouses.data.map((item) =>
                        item.id === warehouse.id
                            ? response.data.warehouse
                            : item
                    );
                }

                // Update single item if it's the one being edited
                if (this.singleWarehouse.id === warehouse.id) {
                    this.singleWarehouse = response.data.warehouse;
                }

                return response.data.warehouse;
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message || error.response.data.errors
                    : error.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        async deleteWarehouse(id) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.delete(`/api/warehouses/${id}`);
                this.success = response.data.message;

                // Remove from local state
                if (this.warehouses.data) {
                    this.warehouses.data = this.warehouses.data.filter(
                        (item) => item.id !== id
                    );
                }

                return true;
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message || error.response.data.errors
                    : error.message;
                return false;
            } finally {
                this.loading = false;
            }
        },

        clearErrors() {
            this.error = null;
        },

        clearSuccess() {
            this.success = null;
        },
    },
});
