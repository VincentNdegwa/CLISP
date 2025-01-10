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
                    value="nextCycle"
                />
                <label for="cancelAfterSubscription" class="text-slate-900">
                    Cancel After End of Subscription
                </label>
            </div>
        </div>

        <div class="flex justify-end mt-10">
            <PrimaryRoseButton @click="openAction('Cancel Plan', 'Are you sure want to cancel plan',handleCancel  )" :disabled="!cancelOption">
                Cancel Plan
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
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import RadioButton from "primevue/radiobutton";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

export default {
    components: {
        RadioButton,
        PrimaryRoseButton,
        ConfirmationModal,

    },
    data() {
        return {
            cancelOption: null,
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
        handleCancel() {
            if (this.cancelOption === "now") {
                this.$emit("cancel", "now");
            } else if (this.cancelOption === "nextCycle") {
                this.$emit("cancel", "nextCycle");
            }
        }, openAction(title, message, confirm) {
            this.confirmation.isOpen = true;
            this.confirmation.title = title;
            this.confirmation.message = message;
            this.confirmation.confirm = confirm;
        },
        cancelAction() {
            this.confirmation.isOpen = false;
        },
    },
};
</script>
