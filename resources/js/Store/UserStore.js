import { defineStore } from "pinia";

export const useUserStore = defineStore('userStore', {
    state: () => ({
        businessId: null,
    }),
    getters: {
        business(state) {
            const business = window.localStorage.getItem('default_business');
            if (business) {
                const business_id = JSON.parse(business).business_id;
                state.businessId = business_id;
                return state.businessId;
            }
            return null;
        }
    }
});
