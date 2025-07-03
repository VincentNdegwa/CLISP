import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const usePurchaseOrderStore = defineStore("purchase_order_store", {
    state: () => ({
        orders: {
            data: [],
        },
        currentOrder: null,
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
        async fetchOrders(params = {}) {
            this.loading = true;
            this.error = null;

            // Merge default filters with provided params
            const queryParams = { ...this.filters, ...params };

            try {
                const response = await axios.get("/api/purchase-orders", {
                    params: queryParams,
                });
                this.orders = response.data;

                // Add status_text and status_class to each order
                if (this.orders && this.orders.data) {
                    this.orders.data = this.orders.data.map((order) => {
                        return {
                            ...order,
                            status_text: this.getStatusText(order.status),
                            status_class: this.getStatusClass(order.status),
                        };
                    });
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error fetching purchase orders";
                console.error("Error fetching purchase orders:", error);
            } finally {
                this.loading = false;
            }
        },

        async getOrder(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/purchase-orders/${id}`);
                this.currentOrder = {
                    ...response.data,
                    status_text: this.getStatusText(response.data.status),
                    status_class: this.getStatusClass(response.data.status),
                };
                return this.currentOrder;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error fetching purchase order details";
                console.error("Error fetching purchase order details:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async addOrder(order) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    "/api/purchase-orders",
                    order
                );
                this.success = "Purchase order created successfully";

                // Refresh the orders list
                await this.fetchOrders();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error creating purchase order";
                if (error.response?.data?.errors) {
                    this.error = Object.values(error.response.data.errors)
                        .flat()
                        .join(", ");
                }
                console.error("Error creating purchase order:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async updateOrder(order) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.put(
                    `/api/purchase-orders/${order.id}`,
                    order
                );
                this.success = "Purchase order updated successfully";

                // Refresh the orders list
                await this.fetchOrders();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error updating purchase order";
                if (error.response?.data?.errors) {
                    this.error = Object.values(error.response.data.errors)
                        .flat()
                        .join(", ");
                }
                console.error("Error updating purchase order:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async deleteOrder(id) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                await axios.delete(`/api/purchase-orders/${id}`);
                this.success = "Purchase order deleted successfully";

                // Refresh the orders list
                await this.fetchOrders();

                return true;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error deleting purchase order";
                console.error("Error deleting purchase order:", error);
                return false;
            } finally {
                this.loading = false;
            }
        },

        async approveOrder(id) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    `/api/purchase-orders/${id}/approve`
                );
                this.success = "Purchase order approved successfully";

                // Refresh the current order and orders list
                if (this.currentOrder && this.currentOrder.id === id) {
                    await this.getOrder(id);
                }
                await this.fetchOrders();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error approving purchase order";
                console.error("Error approving purchase order:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async sendOrder(id) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    `/api/purchase-orders/${id}/send`
                );
                this.success = "Purchase order sent successfully";

                // Refresh the current order and orders list
                if (this.currentOrder && this.currentOrder.id === id) {
                    await this.getOrder(id);
                }
                await this.fetchOrders();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error sending purchase order";
                console.error("Error sending purchase order:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async cancelOrder(id) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    `/api/purchase-orders/${id}/cancel`
                );
                this.success = "Purchase order cancelled successfully";

                // Refresh the current order and orders list
                if (this.currentOrder && this.currentOrder.id === id) {
                    await this.getOrder(id);
                }
                await this.fetchOrders();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error cancelling purchase order";
                console.error("Error cancelling purchase order:", error);
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
                supplier_id: "all",
                search: "",
            };
        },

        // Helper methods for status text and class
        getStatusText(status) {
            const statusMap = {
                0: "Draft",
                1: "Submitted",
                2: "Approved",
                3: "Sent",
                4: "Partially Received",
                5: "Received",
                6: "Cancelled",
                7: "On Hold",
            };
            return statusMap[status] || "Unknown";
        },

        getStatusClass(status) {
            const classMap = {
                0: "bg-gray-100 text-gray-800",
                1: "bg-blue-100 text-blue-800",
                2: "bg-indigo-100 text-indigo-800",
                3: "bg-purple-100 text-purple-800",
                4: "bg-amber-100 text-amber-800",
                5: "bg-green-100 text-green-800",
                6: "bg-red-100 text-red-800",
                7: "bg-yellow-100 text-yellow-800",
            };
            return classMap[status] || "bg-gray-100 text-gray-800";
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
