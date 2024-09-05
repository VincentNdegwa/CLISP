<template>
    <div
        v-if="openConfirm"
        class="fixed inset-0 flex items-center justify-center z-50 bg-gray-900 bg-opacity-60"
    >
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <h2 class="text-lg font-semibold text-slate-900">
                {{ title.trim() ? title.trim() : "Confirm Action" }}
            </h2>
            <p class="text-slate-600 mt-2">
                {{
                    message.trim()
                        ? message.trim()
                        : "Are you sure you want to proceed?"
                }}
            </p>
            <div class="mt-4 flex justify-end">
                <button
                    class="bg-slate-200 text-slate-700 px-4 py-2 rounded mr-2"
                    @click="cancel"
                >
                    Cancel
                </button>
                <button
                    class="bg-rose-500 text-white px-4 py-2 rounded"
                    @click="confirm"
                >
                    Confirm
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        isOpen: {
            type: Boolean,
            default: false,
        },
        title: {
            type: String,
            default: "Confirm Action",
        },
        message: {
            type: String,
            default: "Are you sure you want to proceed?",
        },
    },
    data() {
        return {
            openConfirm: this.isOpen,
        };
    },
    watch: {
        isOpen: {
            handler(newValue) {
                this.openConfirm = newValue;
            },
            deep: true,
        },
    },
    methods: {
        cancel() {
            this.$emit("close");
        },
        confirm() {
            this.$emit("confirm");
            this.$emit("close");
        },
    },
};
</script>

<style scoped></style>
