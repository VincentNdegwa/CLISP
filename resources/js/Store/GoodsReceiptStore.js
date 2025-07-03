import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const useGoodsReceiptStore = defineStore("goods_receipt_store", {
    state: () => ({
        receipts: {
            data: [],
        },
        currentReceipt: null,
        loading: false,
        error: null,
        success: null,
        filters: {
            status: "all",
            supplier_id: "all",
            search: "",
        },
    }),

    actions: {
        async fetchReceipts(params = {}) {
            this.loading = true;
            this.error = null;

            // Merge default filters with provided params
            const queryParams = { ...this.filters, ...params };

            try {
                const response = await axios.get("/api/goods-receipts", {
                    params: queryParams,
                });
                this.receipts = response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error fetching goods receipts";
                console.error("Error fetching goods receipts:", error);
            } finally {
                this.loading = false;
            }
        },

        async getReceipt(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/goods-receipts/${id}`);
                this.currentReceipt = response.data;
                return this.currentReceipt;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error fetching goods receipt details";
                console.error("Error fetching goods receipt details:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async addReceipt(receipt) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    "/api/goods-receipts",
                    receipt
                );
                this.success = "Goods receipt created successfully";

                // Refresh the receipts list
                await this.fetchReceipts();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error creating goods receipt";
                if (error.response?.data?.errors) {
                    this.error = Object.values(error.response.data.errors)
                        .flat()
                        .join(", ");
                }
                console.error("Error creating goods receipt:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async inspectItems(id, items) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    `/api/goods-receipts/${id}/inspect`,
                    { items }
                );
                this.success = "Items inspected successfully";

                // Refresh the current receipt
                if (this.currentReceipt && this.currentReceipt.id === id) {
                    await this.getReceipt(id);
                }

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Error inspecting items";
                if (error.response?.data?.errors) {
                    this.error = Object.values(error.response.data.errors)
                        .flat()
                        .join(", ");
                }
                console.error("Error inspecting items:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async completeReceipt(id) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    `/api/goods-receipts/${id}/complete`
                );
                this.success = "Goods receipt completed successfully";

                // Refresh the receipts list and current receipt
                await this.fetchReceipts();
                if (this.currentReceipt && this.currentReceipt.id === id) {
                    await this.getReceipt(id);
                }

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error completing goods receipt";
                console.error("Error completing goods receipt:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async getPurchaseOrderItems(purchaseOrderId) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(
                    "/api/goods-receipts/purchase-order-items",
                    {
                        params: { purchase_order_id: purchaseOrderId },
                    }
                );
                return response.data.items;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error fetching purchase order items";
                console.error("Error fetching purchase order items:", error);
                return [];
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
                supplier_id: "all",
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
