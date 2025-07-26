import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const useStockCountStore = defineStore("stock_count_store", {
    state: () => ({
        stockCounts: {
            data: [],
        },
        currentStockCount: null,
        loading: false,
        error: null,
        success: null,
        filters: {
            status: "all",
            warehouse_id: "all",
            search: "",
        },
    }),

    actions: {
        async fetchStockCounts(params = {}) {
            this.loading = true;
            this.error = null;

            // Merge default filters with provided params
            const queryParams = { ...this.filters, ...params };

            try {
                const response = await axios.get("/api/stock-counts", {
                    params: queryParams,
                });
                this.stockCounts = response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error fetching stock counts";
                console.error("Error fetching stock counts:", error);
            } finally {
                this.loading = false;
            }
        },

        async getStockCount(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/stock-counts/${id}`);
                this.currentStockCount = response.data;
                return this.currentStockCount;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error fetching stock count details";
                console.error("Error fetching stock count details:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async addStockCount(stockCount) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    "/api/stock-counts",
                    stockCount
                );
                this.success = "Stock count created successfully";

                // Refresh the stock counts list
                await this.fetchStockCounts();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error creating stock count";
                if (error.response?.data?.errors) {
                    this.error = Object.values(error.response.data.errors)
                        .flat()
                        .join(", ");
                }
                console.error("Error creating stock count:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async updateCounts(id, items) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    `/api/stock-counts/${id}/update-counts`,
                    { items }
                );
                this.success = "Stock count updated successfully";

                // Refresh the current stock count
                if (
                    this.currentStockCount &&
                    this.currentStockCount.id === id
                ) {
                    await this.getStockCount(id);
                }

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error updating stock counts";
                if (error.response?.data?.errors) {
                    this.error = Object.values(error.response.data.errors)
                        .flat()
                        .join(", ");
                }
                console.error("Error updating stock counts:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async verifyStockCount(id) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    `/api/stock-counts/${id}/verify`
                );
                this.success = "Stock count verified successfully";

                // Refresh the stock counts list and current stock count
                await this.fetchStockCounts();
                if (
                    this.currentStockCount &&
                    this.currentStockCount.id === id
                ) {
                    await this.getStockCount(id);
                }

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error verifying stock count";
                console.error("Error verifying stock count:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async createAdjustment(id) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    `/api/stock-counts/${id}/adjust`
                );
                this.success = "Inventory adjustments created successfully";

                // Refresh the current stock count
                if (
                    this.currentStockCount &&
                    this.currentStockCount.id === id
                ) {
                    await this.getStockCount(id);
                }

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error creating adjustments";
                console.error("Error creating adjustments:", error);
                return null;
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
                warehouse_id: "all",
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
