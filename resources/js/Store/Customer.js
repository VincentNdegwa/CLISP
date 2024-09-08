import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const useCustomerStore = defineStore("customer_store", {
    state: () => ({
        customers: [],
        loading: false,
        error: null,
        success: null,
    }),
    actions: {
        async createCustomer(customerData) {
            const store = useUserStore();
            const businessId = store.business;
            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }
            this.loading = true;
            try {
                const response = await axios.post(
                    "/api/customers/create-customer",
                    {
                        business_id: businessId,
                        ...customerData,
                    }
                );
                if (response.data.error) {
                    console.log(response);

                    if (response.data.errors) {
                        this.error = response.data.errors;
                    } else {
                        this.error = response.data.message;
                    }
                    return;
                }

                this.customers.push(response.data.data);
                this.success = "Customer created successfully!";
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message
                    : "Failed to create customer.";
            } finally {
                this.loading = false;
            }
        },

        async fetchBusinessCustomers() {
            const store = useUserStore();
            const businessId = store.business;
            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }
            this.loading = true;
            try {
                const response = await axios.get(
                    `/api/customers/business-customers/${businessId}`
                );
                this.customers = response.data.data;
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message
                    : "Failed to fetch customers.";
            } finally {
                this.loading = false;
            }
        },

        async updateCustomer(customerData) {
            this.loading = true;

            try {
                const store = useUserStore();
                const businessId = store.business;
                if (!businessId) {
                    this.error = "Business ID not found.";
                    return;
                }

                const response = await axios.patch(
                    "/api/customers/update-customer",
                    {
                        business_id: businessId,
                        ...customerData,
                    }
                );
                if (response.data.error) {
                    if (response.data.errors) {
                        this.error = response.data.errors;
                    } else {
                        this.error = response.data.message;
                    }
                    return;
                } else {
                    const index = this.customers.findIndex(
                        (c) => c.id === customerData.id
                    );
                    if (index !== -1) {
                        this.customers[index] = {
                            ...this.customers[index],
                            ...customerData,
                        };
                    }
                    this.success = "Customer updated successfully!";
                }
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message
                    : "Failed to update customer.";
            } finally {
                this.loading = false;
            }
        },

        async deleteCustomer(customerId) {
            this.loading = true;
            try {
                const response = await axios.delete(
                    `/api/customers/delete-customer/${customerId}`
                );
                if (response.data.error) {
                    this.error = response.data.message;
                    return;
                } else {
                    this.customers = this.customers.filter(
                        (c) => c.id !== customerId
                    );
                    this.success = "Customer deleted successfully!";
                }
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message
                    : "Failed to delete customer.";
            } finally {
                this.loading = false;
            }
        },
    },
});
