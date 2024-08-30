<template>
    <div class="skeleton-table w-full">
        <div class="skeleton-header flex gap-4">
            <div
                v-for="i in columnCount"
                :key="i"
                class="skeleton h-6 w-24 rounded flex-1"
            ></div>
        </div>

        <div
            v-for="i in rowCount"
            :key="i"
            class="skeleton-row flex gap-4 mt-6"
        >
            <div
                v-for="j in columnCount"
                :key="j"
                class="skeleton h-4 w-24 rounded flex-1"
            ></div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        rowCount: {
            type: Number,
            default: 6,
        },
        columnCount: {
            type: Number,
            default: 5,
        },
    },
};
</script>

<style scoped>
.skeleton-table {
    padding: 1rem;
}

.skeleton-header {
    margin-bottom: 1rem;
}

.skeleton {
    background-color: #bcc1c7;
    animation: pulse 1.5s infinite ease-in-out;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.4;
    }
}
</style>
