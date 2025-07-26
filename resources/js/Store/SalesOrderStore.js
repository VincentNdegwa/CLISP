import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const useSalesOrderStore = defineStore("sales_order_store", {
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
            payment_status: "all",
            fulfillment_status: "all",
            customer_id: "all",
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
                const response = await axios.get("/api/sales-orders", {
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
                            payment_status_text: this.getPaymentStatusText(
                                order.payment_status
                            ),
                            payment_status_class: this.getPaymentStatusClass(
                                order.payment_status
                            ),
                            fulfillment_status_text:
                                this.getFulfillmentStatusText(
                                    order.fulfillment_status
                                ),
                            fulfillment_status_class:
                                this.getFulfillmentStatusClass(
                                    order.fulfillment_status
                                ),
                        };
                    });
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error fetching sales orders";
                console.error("Error fetching sales orders:", error);
            } finally {
                this.loading = false;
            }
        },

        async getOrder(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/sales-orders/${id}`);
                this.currentOrder = {
                    ...response.data,
                    status_text: this.getStatusText(response.data.status),
                    status_class: this.getStatusClass(response.data.status),
                    payment_status_text: this.getPaymentStatusText(
                        response.data.payment_status
                    ),
                    payment_status_class: this.getPaymentStatusClass(
                        response.data.payment_status
                    ),
                    fulfillment_status_text: this.getFulfillmentStatusText(
                        response.data.fulfillment_status
                    ),
                    fulfillment_status_class: this.getFulfillmentStatusClass(
                        response.data.fulfillment_status
                    ),
                };
                return this.currentOrder;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error fetching sales order details";
                console.error("Error fetching sales order details:", error);
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
                const response = await axios.post("/api/sales-orders", order);
                this.success = "Sales order created successfully";

                // Refresh the orders list
                await this.fetchOrders();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error creating sales order";
                if (error.response?.data?.errors) {
                    this.error = Object.values(error.response.data.errors)
                        .flat()
                        .join(", ");
                }
                console.error("Error creating sales order:", error);
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
                    `/api/sales-orders/${order.id}`,
                    order
                );
                this.success = "Sales order updated successfully";

                // Refresh the orders list
                await this.fetchOrders();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error updating sales order";
                if (error.response?.data?.errors) {
                    this.error = Object.values(error.response.data.errors)
                        .flat()
                        .join(", ");
                }
                console.error("Error updating sales order:", error);
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
                await axios.delete(`/api/sales-orders/${id}`);
                this.success = "Sales order deleted successfully";

                // Refresh the orders list
                await this.fetchOrders();

                return true;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error deleting sales order";
                console.error("Error deleting sales order:", error);
                return false;
            } finally {
                this.loading = false;
            }
        },

        async confirmOrder(id) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    `/api/sales-orders/${id}/confirm`
                );
                this.success = "Sales order confirmed successfully";

                // Refresh the current order and orders list
                if (this.currentOrder && this.currentOrder.id === id) {
                    await this.getOrder(id);
                }
                await this.fetchOrders();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error confirming sales order";
                console.error("Error confirming sales order:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async processOrder(id) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    `/api/sales-orders/${id}/process`
                );
                this.success = "Sales order processing started successfully";

                // Refresh the current order and orders list
                if (this.currentOrder && this.currentOrder.id === id) {
                    await this.getOrder(id);
                }
                await this.fetchOrders();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error processing sales order";
                console.error("Error processing sales order:", error);
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
                    `/api/sales-orders/${id}/cancel`
                );
                this.success = "Sales order cancelled successfully";

                // Refresh the current order and orders list
                if (this.currentOrder && this.currentOrder.id === id) {
                    await this.getOrder(id);
                }
                await this.fetchOrders();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error cancelling sales order";
                console.error("Error cancelling sales order:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async allocateInventory(id, allocations = []) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    `/api/sales-orders/${id}/allocate`,
                    { allocations }
                );
                this.success = "Inventory allocated successfully";

                // Refresh the current order
                if (this.currentOrder && this.currentOrder.id === id) {
                    await this.getOrder(id);
                }

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error allocating inventory";
                console.error("Error allocating inventory:", error);
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
                payment_status: "all",
                fulfillment_status: "all",
                customer_id: "all",
                search: "",
            };
        },

        // Helper methods for status text and class
        getStatusText(status) {
            const statusMap = {
                0: "Draft",
                1: "Confirmed",
                2: "Processing",
                3: "Picking",
                4: "Packing",
                5: "Shipped",
                6: "Delivered",
                7: "Cancelled",
                8: "On Hold",
            };
            return statusMap[status] || "Unknown";
        },

        getStatusClass(status) {
            const classMap = {
                0: "bg-gray-100 text-gray-800",
                1: "bg-blue-100 text-blue-800",
                2: "bg-indigo-100 text-indigo-800",
                3: "bg-purple-100 text-purple-800",
                4: "bg-pink-100 text-pink-800",
                5: "bg-orange-100 text-orange-800",
                6: "bg-green-100 text-green-800",
                7: "bg-red-100 text-red-800",
                8: "bg-yellow-100 text-yellow-800",
            };
            return classMap[status] || "bg-gray-100 text-gray-800";
        },

        getPaymentStatusText(status) {
            const statusMap = {
                0: "Pending",
                1: "Partial",
                2: "Paid",
                3: "Refunded",
            };
            return statusMap[status] || "Unknown";
        },

        getPaymentStatusClass(status) {
            const classMap = {
                0: "bg-yellow-100 text-yellow-800",
                1: "bg-blue-100 text-blue-800",
                2: "bg-green-100 text-green-800",
                3: "bg-red-100 text-red-800",
            };
            return classMap[status] || "bg-gray-100 text-gray-800";
        },

        getFulfillmentStatusText(status) {
            const statusMap = {
                0: "Pending",
                1: "Partial",
                2: "Fulfilled",
                3: "Backordered",
            };
            return statusMap[status] || "Unknown";
        },

        getFulfillmentStatusClass(status) {
            const classMap = {
                0: "bg-yellow-100 text-yellow-800",
                1: "bg-blue-100 text-blue-800",
                2: "bg-green-100 text-green-800",
                3: "bg-red-100 text-red-800",
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
