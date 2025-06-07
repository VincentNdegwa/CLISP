<template>
    <div class="bg-slate-50 dark:bg-slate-900 text-slate-950 dark:text-slate-50 p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold mb-4">Change Plan</h2>

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
                :key="plan.price_id"
                :class="[
                    'w-full cursor-pointer shadow-md border border-gray-300 rounded-lg hover:shadow-lg transition-all duration-300',
                    selectedPlan?.price_id === plan?.price_id
                        ? 'bg-red-200'
                        : 'bg-slate-50 dark:bg-slate-800 text-slate-950 dark:text-slate-50',
                ]"
                @click="selectPlan(plan)"
            >
                <div class="p-5">
                    <!-- Plan Header -->
                    <div class="flex items-center justify-between">
                        <h2 class="text-md font-semibold text-slate-900 dark:text-slate-50">
                            {{ plan.name }} Plan
                        </h2>
                        <div
                            v-if="plan.isPopular"
                            class="bg-red-500 text-slate-700 text-xs px-3 py-1 rounded-full uppercase tracking-wider"
                        >
                            Popular
                        </div>
                    </div>

                    <!-- Price Section -->
                    <div class="flex gap-1 items-center w-full mt-4">
                        <div class="text-xl font-extrabold text-gray-900 dark:text-slate-50">
                            {{ currency(plan.price) }}
                        </div>
                        <div class="text-sm text-gray-500">
                            / {{ plan.billing_cycle }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Option -->
        <div class="flex flex-col gap-4 mt-10" v-if="activeSubscriptions">
            <div class="flex items-center gap-2">
                <RadioButton
                    v-model="changeOption"
                    inputId="changeNow"
                    name="changeOption"
                    value="now"
                />
                <label for="changeNow" class="text-slate-900 dark:text-slate-50">Change Now</label>
            </div>
            <div class="flex items-center gap-2">
                <RadioButton
                    v-model="changeOption"
                    inputId="changeNextCycle"
                    name="changeOption"
                    value="nextCycle"
                />
                <label for="changeNextCycle" class="text-slate-900 dark:text-slate-50"
                    >Change Next Billing Cycle</label
                >
            </div>
        </div>

        <!-- Change Plan Button -->
        <div class="flex justify-end mt-10">
            <PrimaryRoseButton
                @click="handleChange"
                :disabled="
                    !selectedPlan ||
                    (activeSubscriptions ? !changeOption : false)
                "
            >
                Change Plan
            </PrimaryRoseButton>
        </div>

        <ConfirmationModal
            :isOpen="confirmation.isOpen"
            :title="confirmation.title"
            :message="confirmation.message"
            @confirm="confirmation.confirm"
            @close="cancelAction"
        />
    </div>
</template>

<script>
import SelectButton from "primevue/selectbutton";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import { currencyConvertor } from "@/Store/CurrencyConvertStore";
import RadioButton from "primevue/radiobutton";
import { useBusinessSubscriptionStore } from "@/Store/BusinessSubscription";
import { useUserStore } from "@/Store/UserStore";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

export default {
    components: {
        SelectButton,
        PrimaryRoseButton,
        RadioButton,
        ConfirmationModal,
    },
    props: {
        plan: { type: Array, required: true },
        activeSubscriptions: { type: Boolean, required: true },
    },
    data() {
        return {
            billing_cycle: "monthly",
            cycles: ["monthly", "annually"],
            billing_plans: [],
            selectedPlan: null,
            changeOption: null,
            confirmation: {
                isOpen: false,
                title: "Confirm Action",
                message: "Are you sure you want to proceed?",
                confirm: () => {
                    this.confirmation.open == false;
                },
                cancel: () => {
                    this.confirmation.open == false;
                },
            },
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
        openAction(title, message, confirm) {
            this.confirmation.isOpen = true;
            this.confirmation.title = title;
            this.confirmation.message = message;
            this.confirmation.confirm = confirm;
        },
        cancelAction() {
            this.confirmation.isOpen = false;
        },
        selectPlan(plan) {
            this.selectedPlan = plan;
        },
        async handleChange() {
            const params = {
                plan_id: this.selectedPlan.price_id,
                when: this.activeSubscriptions ? this.changeOption : "now",
                activeSubscriptions: this.activeSubscriptions,
            };
            if (!this.activeSubscriptions) {
                const businessId = useUserStore().business;
                const priceId = this.selectedPlan.price_id;
                window.location.href = `/checkout/subscription/${businessId}/${priceId}`;
            } else {
                this.openAction(
                    "Confirm Change Plan",
                    "Are you sure you want to change plan?",
                    async () => {
                        await this.changeMyPlan(params).then(() => {
                            this.$emit("close");
                        });
                    }
                );
            }
        },
    },
    mounted() {
        this.setPlans();
    },
    watch: {
        billing_cycle: {
            handler() {
                this.setPlans();
            },
        },
    },
    setup() {
        const businessSub = useBusinessSubscriptionStore();
        const changeMyPlan = async (params) => {
            await businessSub.ChangePlan(params);
        };
        return { changeMyPlan };
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
