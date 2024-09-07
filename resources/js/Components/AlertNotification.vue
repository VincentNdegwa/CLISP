<template>
    <div
        v-if="isVisible"
        :class="`alert ${statusClass} absolute ${position}-1 right-1 min-w-[200px] max-w-fit z-[1000]`"
        role="alert"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            class="h-6 w-6 shrink-0 stroke-current"
        >
            <path
                :d="iconPath"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
            />
        </svg>
        <span>{{ message }}</span>
    </div>
</template>

<script>
export default {
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
        open: {
            immediate: true,
            handler(newVal) {
                if (newVal) {
                    this.showAlert();
                }
            },
            deep: true,
        },
        status: {
            handler(newVal) {
                if (newVal) {
                    this.statusClass = newVal;
                    this.setStatusClass();
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
            if (this.timeoutId) {
                clearTimeout(this.timeoutId);
            }

            this.isVisible = true;

            this.timeoutId = setTimeout(() => {
                this.isVisible = false;
            }, 5000);
        },
        setStatusClass() {
            switch (this.status) {
                case "error":
                    this.statusClass = "alert-error";
                    break;
                case "warning":
                    this.statusClass = "alert-warning";
                    break;
                case "success":
                    this.statusClass = "alert-success";
                    break;
                default:
                    this.statusClass = "alert-info";
            }
        },
        setIconPath() {
            switch (this.status) {
                case "error":
                    this.iconPath =
                        "M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z";
                    break;
                case "warning":
                    this.iconPath =
                        "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z";
                    break;
                case "success":
                    this.iconPath =
                        "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z";
                    break;
                default:
                    this.iconPath =
                        "M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z";
            }
        },
    },
    mounted() {
        this.setStatusClass();
        this.setIconPath();
    },
};
</script>

<style scoped>
.alert {
    display: flex;
    align-items: center;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 0.5rem;
    background-color: #ebf8ff;
    color: #2c5282;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
}

.alert-warning {
    background-color: #fff3cd;
    color: #856404;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
}

.alert-info {
    background-color: #ebf8ff;
    color: #2c5282;
}
</style>
