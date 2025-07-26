import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const useStockMovementStore = defineStore("stock_movement_store", {
    state: () => ({
        movements: {
            data: [],
        },
        currentMovement: null,
        loading: false,
        error: null,
        success: null,
        filters: {
            type: "all",
            warehouse_id: "all",
            resource_id: "all",
            inventory_id: "all",
            search: "",
        },
    }),

    actions: {
        async fetchMovements(params = {}) {
            this.loading = true;
            this.error = null;

            // Merge default filters with provided params
            const queryParams = { ...this.filters, ...params };

            try {
                const response = await axios.get("/api/stock-movements", {
                    params: queryParams,
                });
                this.movements = response.data;

                // Add type_text to each movement
                if (this.movements && this.movements.data) {
                    this.movements.data = this.movements.data.map(
                        (movement) => {
                            return {
                                ...movement,
                                type_text: this.getTypeText(movement.type),
                                type_class: this.getTypeClass(movement.type),
                            };
                        }
                    );
                }
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error fetching stock movements";
                console.error("Error fetching stock movements:", error);
            } finally {
                this.loading = false;
            }
        },

        async getMovement(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/stock-movements/${id}`);
                this.currentMovement = {
                    ...response.data,
                    type_text: this.getTypeText(response.data.type),
                    type_class: this.getTypeClass(response.data.type),
                };
                return this.currentMovement;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error fetching stock movement details";
                console.error("Error fetching stock movement details:", error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async createMovement(movement) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    "/api/stock-movements",
                    movement
                );
                this.success = "Stock movement created successfully";

                // Refresh the movements list
                await this.fetchMovements();

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Error creating stock movement";
                if (error.response?.data?.errors) {
                    this.error = Object.values(error.response.data.errors)
                        .flat()
                        .join(", ");
                }
                console.error("Error creating stock movement:", error);
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
                type: "all",
                warehouse_id: "all",
                resource_id: "all",
                inventory_id: "all",
                search: "",
            };
        },

        // Helper methods for type text and class
        getTypeText(type) {
            const typeMap = {
                in: "Incoming",
                out: "Outgoing",
                transfer: "Transfer",
                adjustment: "Adjustment",
                returned: "Returned",
                damaged: "Damaged",
                lost: "Lost",
                counted: "Counted",
            };
            return typeMap[type] || "Unknown";
        },

        getTypeClass(type) {
            const classMap = {
                in: "bg-green-100 text-green-800",
                out: "bg-red-100 text-red-800",
                transfer: "bg-blue-100 text-blue-800",
                adjustment: "bg-yellow-100 text-yellow-800",
                returned: "bg-purple-100 text-purple-800",
                damaged: "bg-orange-100 text-orange-800",
                lost: "bg-gray-100 text-gray-800",
                counted: "bg-indigo-100 text-indigo-800",
            };
            return classMap[type] || "bg-gray-100 text-gray-800";
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
