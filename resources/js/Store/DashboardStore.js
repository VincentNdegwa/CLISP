import axios from "axios";
import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const useDashboardStore = defineStore("dashboard_store", {
    state: () => ({
        dashboardData: null,
        loading: false,
        error: null,
    }),
    actions: {
        async fetchDashboardDetails(bId) {
            const userStore = useUserStore();
            const businessId = userStore.business;

            if (!businessId) {
                this.error = "Business ID not found.";
                return;
            }
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post(
                    `/api/dashboard/${businessId}/details`
                );
                this.dashboardData = response.data;
            } catch (error) {
                this.error = error.response
                    ? error.response.data.message
                    : error.message;
                console.error("Error fetching dashboard data:", this.error);
            } finally {
                this.loading = false;
            }
        },
    },
});
