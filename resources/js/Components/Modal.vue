<script setup>
import { computed, onMounted, onUnmounted, watch, ref } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: "2xl",
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    theme: {
        type: String,
        default: "primary", // primary, secondary, info, success, warning, danger
    },
});

const emit = defineEmits(["close"]);
const isDarkMode = ref(false);

// Check for dark mode preference
onMounted(() => {
    isDarkMode.value = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Listen for changes in color scheme preference
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
        isDarkMode.value = event.matches;
    });
    
    document.addEventListener("keydown", closeOnEscape);
});

onUnmounted(() => {
    document.removeEventListener("keydown", closeOnEscape);
    document.body.style.overflow = null;
    
    // Clean up event listener
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', () => {});
    }
});

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = "hidden";
        } else {
            document.body.style.overflow = null;
        }
    }
);

const close = () => {
    if (props.closeable) {
        emit("close");
    }
};

const closeOnEscape = (e) => {
    if (e.key === "Escape" && props.show) {
        close();
    }
};

const maxWidthClass = computed(() => {
    return {
        sm: "sm:max-w-sm",
        md: "sm:max-w-md",
        lg: "sm:max-w-lg",
        xl: "sm:max-w-xl",
        "2xl": "sm:max-w-2xl",
        "3xl": "sm:max-w-3xl",
        "4xl": "sm:max-w-4xl",
        "5xl": "sm:max-w-5xl",
        "6xl": "sm:max-w-6xl",
        "7xl": "sm:max-w-7xl",
        "8xl": "sm:max-w-8xl",
        "9xl": "sm:max-w-9xl",
    }[props.maxWidth];
});

const modalThemeClass = computed(() => {
    if (isDarkMode.value) {
        return 'bg-gray-800 text-gray-100 border border-gray-700 shadow-xl';
    }
    
    // Light mode themes
    return 'bg-gray-50 text-gray-800 shadow-xl';
});

const overlayClass = computed(() => {
    return isDarkMode.value
        ? 'bg-black opacity-80'
        : 'bg-gray-600 opacity-75';
});
</script>

<template>
    <Teleport to="body">
        <Transition leave-active-class="duration-200">
            <div
                v-show="show"
                class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
                scroll-region
            >
                <Transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div
                        v-show="show"
                        class="fixed inset-0 transform transition-all"
                        @click="close"
                    >
                        <div 
                            class="absolute inset-0" 
                            :class="overlayClass" 
                        />
                    </div>
                </Transition>

                <Transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-show="show"
                        class="mb-6 rounded-lg overflow-hidden transform transition-all sm:w-full sm:mx-auto"
                        :class="[maxWidthClass, modalThemeClass]"
                    >
                        <slot v-if="show" />
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* Optional: Add some additional styling for the modal */
.dark-mode-transition {
    transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}
</style>
