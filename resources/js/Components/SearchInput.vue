<template>
    <div class="rounded-md flex items-center gap-3 p-1">
        <input
            type="text"
            v-model="searchText"
            @input="onInput"
            @keydown.enter="emitSearch"
            class="bg-gray-100 flex-1 outline-none border-0 rounded-md focus:outline-none focus:ring-0"
            placeholder="Search"
        />

        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 16 16"
            fill="currentColor"
            class="h-4 w-4 opacity-70"
        >
            <path
                fill-rule="evenodd"
                d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z"
                clip-rule="evenodd"
            />
        </svg>
    </div>
</template>

<script>
export default {
    data() {
        return {
            searchText: "",
            typingTimeout: null,
        };
    },
    methods: {
        onInput() {
            clearTimeout(this.typingTimeout);
            this.typingTimeout = setTimeout(() => {
                this.emitSearch();
            }, 1000);
        },
        emitSearch() {
            this.$emit("search", this.searchText);
        },
    },
};
</script>
