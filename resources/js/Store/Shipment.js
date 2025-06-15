import { defineStore } from 'pinia';
import axios from 'axios';

export const useShipmentStore = defineStore('shipment', {
    state: () => ({
        items: null,
        loading: false,
        error: null,
        success: null,
    }),
    
    actions: {
        async fetchShipments(params) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.get('/api/shipments', { params });
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
                this.error = error.response?.data?.message || 'Error fetching shipments';
                console.error('Error fetching shipments:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async getShipment(id) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await axios.get(`/api/shipments/${id}`);
                return {
                    ...response.data,
                    status_text: this.getStatusText(response.data.status),
                    status_class: this.getStatusClass(response.data.status)
                };
            } catch (error) {
                this.error = error.response?.data?.message || 'Error fetching shipment details';
                console.error('Error fetching shipment details:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },
        
        async addShipment(shipment) {
            this.loading = true;
            this.error = null;
            this.success = null;
            
            try {
                const response = await axios.post('/api/shipments', shipment);
                this.success = 'Shipment created successfully';
                
                // Refresh the shipments list
                await this.fetchShipments({
                    page: 1,
                    rows: 20
                });
                
                return response.data.shipment;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error creating shipment';
                console.error('Error creating shipment:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },
        
        async updateShipment(shipment) {
            this.loading = true;
            this.error = null;
            this.success = null;
            
            try {
                const response = await axios.put(`/api/shipments/${shipment.id}`, shipment);
                this.success = 'Shipment updated successfully';
                
                // Refresh the shipments list
                await this.fetchShipments({
                    page: this.items?.current_page || 1,
                    rows: this.items?.per_page || 20
                });
                
                return response.data.shipment;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error updating shipment';
                console.error('Error updating shipment:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },
        
        async deleteShipment(id) {
            this.loading = true;
            this.error = null;
            this.success = null;
            
            try {
                await axios.delete(`/api/shipments/${id}`);
                this.success = 'Shipment deleted successfully';
                
                // Refresh the shipments list
                await this.fetchShipments({
                    page: this.items?.current_page || 1,
                    rows: this.items?.per_page || 20
                });
                
                return true;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error deleting shipment';
                console.error('Error deleting shipment:', error);
                return false;
            } finally {
                this.loading = false;
            }
        },
        
        async markAsShipped(id) {
            this.loading = true;
            this.error = null;
            this.success = null;
            
            try {
                const response = await axios.post(`/api/shipments/${id}/mark-shipped`);
                this.success = 'Shipment marked as shipped';
                
                // Refresh the shipments list
                await this.fetchShipments({
                    page: this.items?.current_page || 1,
                    rows: this.items?.per_page || 20
                });
                
                return response.data.shipment;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error updating shipment status';
                console.error('Error updating shipment status:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },
        
        async markAsDelivered(id) {
            this.loading = true;
            this.error = null;
            this.success = null;
            
            try {
                const response = await axios.post(`/api/shipments/${id}/mark-delivered`);
                this.success = 'Shipment marked as delivered';
                
                // Refresh the shipments list
                await this.fetchShipments({
                    page: this.items?.current_page || 1,
                    rows: this.items?.per_page || 20
                });
                
                return response.data.shipment;
            } catch (error) {
                this.error = error.response?.data?.message || 'Error updating shipment status';
                console.error('Error updating shipment status:', error);
                return null;
            } finally {
                this.loading = false;
            }
        },
        
        // Helper methods for status text and class
        getStatusText(status) {
            const statusMap = {
                0: 'Pending',
                1: 'Processing',
                2: 'Shipped',
                3: 'Delivered',
                4: 'Returned'
            };
            return statusMap[status] || 'Unknown';
        },
        
        getStatusClass(status) {
            const classMap = {
                0: 'bg-yellow-100 text-yellow-800',
                1: 'bg-blue-100 text-blue-800',
                2: 'bg-purple-100 text-purple-800',
                3: 'bg-green-100 text-green-800',
                4: 'bg-red-100 text-red-800'
            };
            return classMap[status] || 'bg-gray-100 text-gray-800';
        },
        
        // Clear messages
        clearMessages() {
            this.error = null;
            this.success = null;
        }
    }
});
