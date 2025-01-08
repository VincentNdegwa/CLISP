import { defineStore } from "pinia";

export const useUserStore = defineStore("userStore", {
    state: () => ({
        businessId: null,
        actualBusiness: null,
    }),
    getters: {
        business(state) {
            const business = window.localStorage.getItem("default_business");
            if (business) {
                state.actualBusiness = JSON.parse(business);
                state.businessId = JSON.parse(business).business_id;
                return state.businessId;
            }
            return null;
        },
    }, actions: {
        setDefaultBusiness() {
            
        }
    }
});
