<template>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold text-slate-900 mb-4">
            Cancel Billing
        </h2>
        <div class="flex flex-col flex-wrap gap-4 mt-10">
            <div class="flex items-center gap-2">
                <RadioButton
                    v-model="cancelOption"
                    inputId="cancelNow"
                    name="cancelOption"
                    value="now"
                />
                <label for="cancelNow" class="text-slate-900">Cancel Now</label>
            </div>
            <div class="flex items-center gap-2">
                <RadioButton
                    v-model="cancelOption"
                    inputId="cancelAfterSubscription"
                    name="cancelOption"
                    value="afterSubscription"
                />
                <label for="cancelAfterSubscription" class="text-slate-900">
                    Cancel After End of Subscription
                </label>
            </div>
        </div>

        <div class="flex justify-end mt-10">
            <PrimaryRoseButton @click="handleCancel" :disabled="!cancelOption">
                Cancel Plan
            </PrimaryRoseButton>
        </div>
    </div>
</template>

<script>
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import RadioButton from "primevue/radiobutton";

export default {
    components: {
        RadioButton,
        PrimaryRoseButton,
    },
    data() {
        return {
            cancelOption: null,
        };
    },
    methods: {
        handleCancel() {
            if (this.cancelOption === "now") {
                this.$emit("cancel", "now");
            } else if (this.cancelOption === "afterSubscription") {
                this.$emit("cancel", "afterSubscription");
            }
        },
    },
};
</script>
