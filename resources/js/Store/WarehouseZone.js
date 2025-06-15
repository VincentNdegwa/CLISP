import { defineStore } from "pinia";
import axios from "axios";

export const useWarehouseZoneStore = defineStore("warehouseZone", {
    state: () => ({
        zones: {
            data: [],
            total: 0,
            currentPage: 1,
            perPage: 10,
        },
        zoneTypes: [],
        loading: false,
        error: null,
        success: null,
        currentZone: null,
    }),

    getters: {
        getZoneById: (state) => (id) => {
            return state.zones.data.find((zone) => zone.id === id) || null;
        },

        getZonesByWarehouse: (state) => (warehouseId) => {
            return state.zones.data.filter(
                (zone) => zone.warehouse_id === warehouseId
            );
        },

        getActiveZonesByWarehouse: (state) => (warehouseId) => {
            return state.zones.data.filter(
                (zone) =>
                    zone.warehouse_id === warehouseId &&
                    zone.status === "active"
            );
        },
    },

    actions: {
        clearErrors() {
            this.error = null;
        },

        clearSuccess() {
            this.success = null;
        },

        async fetchZones(params = {}) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/warehouse-zones", {
                    params,
                });
                this.zones = response.data;
                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to fetch warehouse zones";
                console.error("Error fetching warehouse zones:", error);
                return false;
            } finally {
                this.loading = false;
            }
        },

        // Get a specific zone by ID
        async fetchZone(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/warehouse-zones/${id}`);
                this.currentZone = response.data;
                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    `Failed to fetch zone with ID ${id}`;
                console.error(`Error fetching zone with ID ${id}:`, error);
                return false;
            } finally {
                this.loading = false;
            }
        },

        // Get zones by warehouse ID
        async fetchZonesByWarehouse(warehouseId) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(
                    `/api/warehouses/${warehouseId}/zones`
                );

                // Update the zones in the store while keeping pagination info
                const existingZones = this.zones.data.filter(
                    (zone) => zone.warehouse_id !== warehouseId
                );
                const newZones = [...existingZones, ...response.data];

                // Update the store with the new zones
                this.zones.data = newZones;

                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    `Failed to fetch zones for warehouse ${warehouseId}`;
                console.error(
                    `Error fetching zones for warehouse ${warehouseId}:`,
                    error
                );
                return false;
            } finally {
                this.loading = false;
            }
        },

        // Get all zone types
        async fetchZoneTypes() {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get("/api/zones-types");
                this.zoneTypes = response.data;
                return response.data;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to fetch zone types";
                console.error("Error fetching zone types:", error);
                return false;
            } finally {
                this.loading = false;
            }
        },

        // Create a new warehouse zone
        async createZone(zoneData) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    "/api/warehouse-zones",
                    zoneData
                );

                // Add the new zone to the store
                this.zones.data.push(response.data.zone);
                this.success = "Warehouse zone created successfully";

                return response.data.zone;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to create warehouse zone";
                console.error("Error creating warehouse zone:", error);
                return false;
            } finally {
                this.loading = false;
            }
        },

        // Update an existing warehouse zone
        async updateZone(zone) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.put(
                    `/api/warehouse-zones/${zone.id}`,
                    zone
                );

                // Update the zone in the store
                const index = this.zones.data.findIndex(
                    (z) => z.id === zone.id
                );
                if (index !== -1) {
                    this.zones.data[index] = response.data.zone;
                }

                this.success = "Warehouse zone updated successfully";

                return response.data.zone;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to update warehouse zone";
                console.error("Error updating warehouse zone:", error);
                return false;
            } finally {
                this.loading = false;
            }
        },

        // Delete a warehouse zone
        async deleteZone(id) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                await axios.delete(`/api/warehouse-zones/${id}`);

                // Remove the zone from the store
                this.zones.data = this.zones.data.filter(
                    (zone) => zone.id !== id
                );

                this.success = "Warehouse zone deleted successfully";

                return true;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to delete warehouse zone";
                console.error("Error deleting warehouse zone:", error);
                return false;
            } finally {
                this.loading = false;
            }
        },
    },
});
