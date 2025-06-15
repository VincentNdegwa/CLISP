import axios from "axios";
import { defineStore } from "pinia";

export const useBinLocationStore = defineStore("bin_location_store", {
    state: () => ({
        binLocations: {
            data: [],
            current_page: 1,
            last_page: 1,
            total: 0,
        },
        loading: false,
        error: null,
        success: null,
        singleBinLocation: {},
    }),
    actions: {
        async fetchBinLocations(queries = {}) {
            // Build query parameters
            const params = new URLSearchParams();

            if (queries.warehouse_id)
                params.append("warehouse_id", queries.warehouse_id);
            if (queries.search) params.append("search", queries.search);
            if (queries.page) params.append("page", queries.page);
            if (queries.rows) params.append("rows", queries.rows);

            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(
                    `/api/bin-locations?${params.toString()}`
                );
                this.binLocations = response.data;
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message || error.response.data.errors
                    : error.message;
            } finally {
                this.loading = false;
            }
        },

        async getBinLocationsByWarehouse(warehouseId, queries = {}) {
            if (!warehouseId) {
                this.error = "Warehouse ID is required.";
                return;
            }

            // Build query parameters
            const params = new URLSearchParams();

            if (queries.page) params.append("page", queries.page);
            if (queries.rows) params.append("rows", queries.rows);

            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(
                    `/api/bin-locations/by-warehouse/${warehouseId}?${params.toString()}`
                );
                this.binLocations = response.data;
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message || error.response.data.errors
                    : error.message;
            } finally {
                this.loading = false;
            }
        },

        async getBinLocation(id) {
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.get(`/api/bin-locations/${id}`);
                this.singleBinLocation = response.data;
                return response.data;
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message || error.response.data.errors
                    : error.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        async createBinLocation(binLocation) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.post(
                    "/api/bin-locations",
                    binLocation
                );
                this.success = response.data.message;

                // Update the binLocations list if needed
                if (this.binLocations.data) {
                    this.binLocations.data.unshift(response.data.bin_location);
                }

                return response.data.bin_location;
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message || error.response.data.errors
                    : error.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        async updateBinLocation(binLocation) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.put(
                    `/api/bin-locations/${binLocation.id}`,
                    binLocation
                );
                this.success = response.data.message;

                // Update in local state if it exists
                if (this.binLocations.data) {
                    this.binLocations.data = this.binLocations.data.map(
                        (item) =>
                            item.id === binLocation.id
                                ? response.data.bin_location
                                : item
                    );
                }

                // Update single item if it's the one being edited
                if (this.singleBinLocation.id === binLocation.id) {
                    this.singleBinLocation = response.data.bin_location;
                }

                return response.data.bin_location;
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message || error.response.data.errors
                    : error.message;
                return null;
            } finally {
                this.loading = false;
            }
        },

        async deleteBinLocation(id) {
            this.loading = true;
            this.error = null;
            this.success = null;

            try {
                const response = await axios.delete(`/api/bin-locations/${id}`);
                this.success = response.data.message;

                // Remove from local state
                if (this.binLocations.data) {
                    this.binLocations.data = this.binLocations.data.filter(
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
