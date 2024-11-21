import { defineStore } from "pinia";
import { useUserStore } from "./UserStore";

export const currencyConvertor = defineStore("currencyConvertorStore", {
    state: () => ({}),
    actions: {
        convertMyCurrency(currency) {
            const currencyCode = useUserStore().actualBusiness?.currency_code;

            const numericAmount =
                typeof currency === "string" ? parseFloat(currency) : currency;
            if (isNaN(numericAmount)) {
                return "0.0";
            }
            return new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: currencyCode,
                minimumFractionDigits: 2,
                maximumFractionDigits: 4,
            }).format(numericAmount);
        },
        convertOtherCurrency(currency, code) {
            const numericAmount =
                typeof currency === "string" ? parseFloat(currency) : currency;
            if (isNaN(numericAmount)) {
                return "0.0";
            }
            return new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: code,
            }).format(numericAmount);
        },
    },
});
