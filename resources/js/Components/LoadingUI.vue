<template>
    <div :class="['flex w-full place-items-center',customClass]">
        <ProgressSpinner />
    </div>
</template>

<script>
import ProgressSpinner from "primevue/progressspinner";

export default {
    components: {
        ProgressSpinner,
    },
    props: {
        customClass: {
            type: String,
            default: "h-full",
        },
    },
};
</script>
