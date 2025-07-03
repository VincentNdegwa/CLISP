import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const useSupplierStore = defineStore("supplier_store", {
    state: () => ({
        suppliers: {
            data: [],
        },
        currentSupplier: null,
        loading: false,
        error: null,
        success: null,
        filters: {
            status: "all",
            search: "",
        },
    }),

    actions: {
        async fetchSuppliers(params = {}) {
            this.loading = true;
            this.error = null;

            // Merge default filters with provided params
            const queryParams = { ...this.filters, ...params };

            try {
                const response = await axios.get("/api/suppliers", {
                    params: queryParams,
                });
                this.suppliers = response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Error fetching suppliers";
                console.error("Error fetching suppliers:", error);
            } finally {
                this.loading = false;
            }
        },

        async getSupplier(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/suppliers/${id}`);
                this.currentSupplier = response.data;
                return this.currentSupplier;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error fetching supplier details";
                console.error("Error fetching supplier details:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async addSupplier(supplier) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post("/api/suppliers", supplier);
                this.success = "Supplier created successfully";

                // Refresh the suppliers list
                await this.fetchSuppliers();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Error creating supplier";
                if (error.response?.data?.errors) {
                    this.error = Object.values(error.response.data.errors)
                        .flat()
                        .join(", ");
                }
                console.error("Error creating supplier:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async updateSupplier(supplier) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.put(
                    `/api/suppliers/${supplier.id}`,
                    supplier
                );
                this.success = "Supplier updated successfully";

                // Refresh the suppliers list and current supplier
                await this.fetchSuppliers();
                if (
                    this.currentSupplier &&
                    this.currentSupplier.id === supplier.id
                ) {
                    this.currentSupplier = response.data;
                }

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Error updating supplier";
                if (error.response?.data?.errors) {
                    this.error = Object.values(error.response.data.errors)
                        .flat()
                        .join(", ");
                }
                console.error("Error updating supplier:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async deleteSupplier(id) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                await axios.delete(`/api/suppliers/${id}`);
                this.success = "Supplier deleted successfully";

                // Refresh the suppliers list
                await this.fetchSuppliers();

                // Clear current supplier if it's the one that was deleted
                if (this.currentSupplier && this.currentSupplier.id === id) {
                    this.currentSupplier = null;
                }

                return true;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Error deleting supplier";
                console.error("Error deleting supplier:", error);
                return false;
            } finally {
                this.loading = false;
            }
        },

        setFilters(filters) {
            this.filters = { ...this.filters, ...filters };
        },

        clearFilters() {
            this.filters = {
                status: "all",
                search: "",
            };
        },

        // Clear messages
        clearMessages() {
            this.error = null;
            this.success = null;
        },
        clearErrors() {
            this.error = null;
            this.success = null;
        },
    },
});
