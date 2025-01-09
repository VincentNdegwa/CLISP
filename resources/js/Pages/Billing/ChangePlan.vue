<template>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold text-slate-900 mb-4">Change Plan</h2>

        <!-- Billing Cycle Selector -->
        <div class="flex justify-end mb-6">
            <SelectButton
                v-model="billing_cycle"
                :options="cycles"
                :optionLabel="
                    (option) => option.charAt(0).toUpperCase() + option.slice(1)
                "
                class="p-button"
            />
        </div>

        <!-- Plans Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div
                v-for="plan in billing_plans"
                :key="plan.name"
                class="card w-full bg-white shadow-md border border-gray-300 rounded-lg hover:shadow-lg transition-all duration-300"
                :class="{ 'border-red-500': selectedPlan === plan.name }"
                @click="selectPlan(plan)"
            >
                <div class="p-5">
                    <!-- Plan Header -->
                    <div class="flex items-center justify-between">
                        <h2 class="text-md font-semibold text-gray-800">
                            {{ plan.name }} Plan
                        </h2>
                        <div
                            v-if="plan.isPopular"
                            class="bg-red-500 text-white text-xs px-3 py-1 rounded-full uppercase tracking-wider"
                        >
                            Popular
                        </div>
                    </div>

                    <!-- Price Section -->
                    <div class="flex gap-1 items-center w-full">
                        <div class="text-xl font-extrabold text-gray-900">
                            {{ currency(plan.price) }}
                        </div>
                        <div class="text-sm p-0 text-gray-500">
                            / {{ plan.billing_cycle }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col flex-wrap gap-4 mt-10">
            <div class="flex items-center gap-2 mt-4">
                <RadioButton
                    v-model="changeOption"
                    inputId="changeNow"
                    name="changeOption"
                    value="now"
                />
                <label for="changeNow" class="text-slate-900">Change Now</label>
            </div>
            <div class="flex items-center gap-2">
                <RadioButton
                    v-model="changeOption"
                    inputId="changeNextCycle"
                    name="changeOption"
                    value="nextCycle"
                />
                <label for="changeNextCycle" class="text-slate-900">
                    Change Next Billing Cycle
                </label>
            </div>
        </div>

        <!-- Change Plan Button -->
        <div class="flex justify-end mt-10">
            <PrimaryRoseButton
                @click="handleChange"
                :disabled="!selectedPlan || !changeOption"
            >
                Change Plan
            </PrimaryRoseButton>
        </div>
    </div>
</template>

<script>
import SelectButton from "primevue/selectbutton";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import { currencyConvertor } from "@/Store/CurrencyConvertStore";
import RadioButton from "primevue/radiobutton";

export default {
    components: {
        SelectButton,
        PrimaryRoseButton,
        RadioButton,
    },
    props: {
        plan: { type: Array, required: true },
    },
    data() {
        return {
            billing_cycle: "monthly",
            cycles: ["monthly", "annually"],
            billing_plans: [],
            selectedPlan: null,
            changeOption: null,
        };
    },
    methods: {
        currency(amount) {
            const converter = currencyConvertor();
            return converter.convertOtherCurrency(amount, "USD");
        },
        setPlans() {
            this.billing_plans = this.plan.map((plan) => {
                return plan.find(
                    (x_plan) => x_plan.billing_cycle === this.billing_cycle
                );
            });
        },
        selectPlan(plan) {
            this.selectedPlan = plan.name;
        },
        handleChange() {
            if (this.changeOption === "now") {
                this.$emit("changePlan", {
                    plan: this.selectedPlan,
                    when: "now",
                    billingCycle: this.billing_cycle,
                });
            } else if (this.changeOption === "nextCycle") {
                this.$emit("changePlan", {
                    plan: this.selectedPlan,
                    when: "nextCycle",
                    billingCycle: this.billing_cycle,
                });
            }
        },
    },
    mounted() {
        this.setPlans();
    },
    watch: {
        billing_cycle: {
            deep: true,
            handler() {
                this.setPlans();
            },
        },
    },
};
</script>

<style scoped>
.card {
    cursor: pointer;
    transition: transform 0.2s ease-in-out;
}
.card:hover {
    transform: scale(1.02);
}
</style>
