<template>
    <Toast />
</template>

<script>
import Toast from "primevue/toast";

export default {
    components: {
        Toast,
    },
    props: {
        status: {
            type: String,
            default: "success",
        },
        message: {
            type: String,
            required: true,
        },
        open: {
            type: Boolean,
            default: false,
        },
        position: {
            type: String,
            default: "top",
        },
    },
    data() {
        return {
            isVisible: this.props,
            statusClass: "",
            iconPath: "",
            timeoutId: null,
        };
    },
    watch: {
        status: {
            handler(newVal) {
                if (newVal) {
                    this.statusClass = newVal;
                }
            },
            deep: true,
        },
        message: {
            handler(newValue) {
                this.showAlert();
            },
            deep: true,
        },
    },
    methods: {
        showAlert() {
            this.$toast.add({
                severity: this.status,
                summary:
                    this.status == "success"
                        ? "Sucess Message"
                        : this.status == "error"
                        ? "Error Message"
                        : this.status == "info"
                        ? "Info Message"
                        : "",
                detail: this.message,
                life: 3000,
            });
        },
    },
};
</script>

<style scoped></style>
