import { defineStore } from 'pinia';
import axios from 'axios';
import { useUserStore } from './UserStore';

export const useInventoryStore = defineStore('inventory', {
    state: () => ({
        items: null,
        loading: false,
        error: null,
        success: null,
        lowStockItems: null,
        inventorySummary: null,
        adjustmentReasons: [], 
        loadingReasons: false,
    }),
    
    actions: {
        async fetchInventory(params) {
            const userStore = useUserStore();
            const businessId = userStore.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }
            this.loading = true;
            this.error = null;
            params.business_id = businessId;
            
            try {
                const response = await axios.get('/api/inventory', { params });
                this.items = response.data;
                
                // Add status_text and status_class to each item
                if (this.items && this.items.data) {
                    this.items.data = this.items.data.map(item => {
                        return {
                            ...item,
                            status_text: this.getStatusText(item.status),
                            status_class: this.getStatusClass(item.status)
                        };
                    });
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching inventory';
                console.error('Error fetching inventory:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async getInventoryItem(id) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.get(`/api/inventory/${id}`);
                return {
                    ...response.data,
                    status_text: this.getStatusText(response.data.status),
                    status_class: this.getStatusClass(response.data.status)
                };
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching inventory item details';
                console.error('Error fetching inventory item details:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },
        
        async addInventoryItem(inventoryItem) {
            this.loading = true;
            this.error = null;
            this.success = null;
            
            try {
                const response = await axios.post('/api/inventory', inventoryItem);
                this.success = 'Inventory item created successfully';
                
                // // Refresh the inventory list
                // await this.fetchInventory({
                //     page: 1,
                //     rows: 20
                // });
                
                return response.data.inventory;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error creating inventory item';
                console.error('Error creating inventory item:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },
        
        async updateInventoryItem(inventoryItem) {
            this.loading = true;
            this.error = null;
            this.success = null;
            
            try {
                const response = await axios.put(`/api/inventory/${inventoryItem.id}`, inventoryItem);
                this.success = 'Inventory item updated successfully';
                
                // Refresh the inventory list
                // await this.fetchInventory({
                //     page: this.items?.current_page || 1,
                //     rows: this.items?.per_page || 20
                // });
                
                return response.data.inventory;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error updating inventory item';
                console.error('Error updating inventory item:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },
        
        async deleteInventoryItem(id) {
            this.loading = true;
            this.error = null;
            this.success = null;
            
            try {
                await axios.delete(`/api/inventory/${id}`);
                this.success = 'Inventory item deleted successfully';
                
                // Refresh the inventory list
                // await this.fetchInventory({
                //     page: this.items?.current_page || 1,
                //     rows: this.items?.per_page || 20
                // });
                
                return true;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error deleting inventory item';
                console.error('Error deleting inventory item:', error);
                return false;
            } finally {
                this.loading = false;
            }
        },
        
        async adjustQuantity(id, adjustment) {
            this.loading = true;
            this.error = null;
            this.success = null;
            
            try {
                const response = await axios.post(`/api/inventory/${id}/adjust-quantity`, adjustment);
                this.success = 'Inventory quantity adjusted successfully';
                
                // Refresh the inventory list
                // await this.fetchInventory({
                //     page: this.items?.current_page || 1,
                //     rows: this.items?.per_page || 20
                // });
                
                return response.data.inventory;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error adjusting inventory quantity';
                console.error('Error adjusting inventory quantity:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },
        
        async moveInventory(id, moveData) {
            this.loading = true;
            this.error = null;
            this.success = null;
            
            try {
                const response = await axios.post(`/api/inventory/${id}/move`, moveData);
                this.success = 'Inventory moved successfully';
                
                // Refresh the inventory list
                // await this.fetchInventory({
                //     page: this.items?.current_page || 1,
                //     rows: this.items?.per_page || 20
                // });
                
                return response.data.inventory;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error moving inventory';
                console.error('Error moving inventory:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },
        
        async fetchLowStockItems() {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.get('/api/inventory/low-stock');
                this.lowStockItems = response.data;
                
                // Add status_text and status_class to each item
                if (this.lowStockItems && this.lowStockItems.data) {
                    this.lowStockItems.data = this.lowStockItems.data.map(item => {
                        return {
                            ...item,
                            status_text: this.getStatusText(item.status),
                            status_class: this.getStatusClass(item.status)
                        };
                    });
                }
                
                return this.lowStockItems;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching low stock items';
                console.error('Error fetching low stock items:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },
        
        async fetchInventorySummary() {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.get('/api/inventory/summary');
                this.inventorySummary = response.data;
                return this.inventorySummary;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching inventory summary';
                console.error('Error fetching inventory summary:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },
        
        // New action to fetch adjustment reasons
        async fetchAdjustmentReasons() {
            const userStore = useUserStore();
            const businessId = userStore.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }
            this.loadingReasons = true;
            this.error = null;
            
            try {
                const response = await axios.get(
                    "/api/stock-adjustment-reasons",
                    {
                        params: {
                            business_id: businessId,
                            rows: 100,
                        },
                    }
                );
                
                let reasons = response.data.data || [];
                this.adjustmentReasons = reasons;
                return reasons;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching adjustment reasons';
                console.error('Error fetching adjustment reasons:', error);
                return [];
            } finally {
                this.loadingReasons = false;
            }
        },
        
        getStatusText(status) {
            const statusMap = {
                0: 'In Stock',
                1: 'Low Stock',
                2: 'Out of Stock',
                3: 'Reserved',
                4: 'Damaged',
                5: 'Expired'
            };
            return statusMap[status] || 'Unknown';
        },
        
        getStatusClass(status) {
            const classMap = {
                0: 'bg-green-100 text-green-800',
                1: 'bg-yellow-100 text-yellow-800',
                2: 'bg-red-100 text-red-800',
                3: 'bg-blue-100 text-blue-800',
                4: 'bg-orange-100 text-orange-800',
                5: 'bg-purple-100 text-purple-800'
            };
            return classMap[status] || 'bg-gray-100 text-gray-800';
        },
        
        async processBatch(inventoryId, batchData) {
                    this.loading = true;
                    this.error = null;
                    this.success = null;
            
            try {
                const response = await axios.post(`/api/inventory/${inventoryId}/process-batch`, batchData);
                this.success = 'Batch operation completed successfully';
                
                // await this.fetchInventory({
                //     page: this.items?.current_page || 1,
                //     rows: this.items?.per_page || 20
                // });
                
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error processing batch operation';
                console.error('Error processing batch operation:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },

        async fetchInventoryBatches(inventoryId) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.get(`/api/inventory/${inventoryId}`);
                return response.data.batches || [];
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching inventory batches';
                console.error('Error fetching inventory batches:', error);
                return [];
            } finally {
                this.loading = false;
            }
        },
        async getInventoryBatches(inventoryId) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.get(`/api/inventory/${inventoryId}/batches`);
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching inventory batches';
                console.error('Error fetching inventory batches:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },
        
        // Clear messages
        clearMessages() {
            this.error = null;
            this.success = null;
        }
    },
    
    getters: {
        // You can add getters here if needed
        getAdjustmentReasons: (state) => state.adjustmentReasons,
    }
});
