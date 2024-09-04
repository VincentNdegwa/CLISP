<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";

export default {
    data() {
        return {
            searchTerm: "",
            searchResults: [],
            selectedBusiness: null,
        };
    },
    methods: {
        searchBusiness() {
            axios
                .get(`/api/search-business?term=${this.searchTerm}`)
                .then((response) => {
                    this.searchResults = response.data;
                });
        },
        selectBusiness(business) {
            this.selectedBusiness = business;
            this.searchTerm = business.business_name;
            this.searchResults = [];
        },
        sendConnectionRequest() {
            if (this.selectedBusiness) {
                axios
                    .post("/api/send-connection-request", {
                        business_id: this.selectedBusiness.id,
                    })
                    .then((response) => {
                        alert("Connection request sent successfully!");
                    });
            } else {
                alert("Please select a business to connect with.");
            }
        },
    },
    components: {
        PrimaryButton,
        PrimaryRoseButton,
    },
};
</script>

<template>
    <div class="max-w-lg mx-auto mt-10 p-0">
        <h1 class="text-2xl font-bold mb-6">Connect with Another Business</h1>
        <form @submit.prevent="sendConnectionRequest">
            <div class="mb-4">
                <label for="business" class="block text-gray-700"
                    >Search Business</label
                >
                <input
                    type="text"
                    v-model="searchTerm"
                    @input="searchBusiness"
                    class="w-full mt-2 p-2 border border-gray-300 rounded"
                    placeholder="Type business name..."
                />
                <div
                    v-if="searchResults.length"
                    class="mt-2 border border-gray-300 rounded"
                >
                    <ul>
                        <li
                            v-for="result in searchResults"
                            :key="result.id"
                            @click="selectBusiness(result)"
                            class="p-2 hover:bg-gray-100 cursor-pointer"
                        >
                            {{ result.business_name }} ({{ result.location }})
                        </li>
                    </ul>
                </div>
            </div>
            <div class="flex gap-1 p-2">
                <PrimaryButton
                    type="submit"
                    class="text-white p-2 rounded flex-1"
                >
                    Send Connection Request
                </PrimaryButton>
                <PrimaryRoseButton
                    type="button"
                    @click="$emit('close')"
                    class="text-white bg-rose-500 hover:bg-rose-700 p-2 rounded flex-1"
                >
                    Cancel
                </PrimaryRoseButton>
            </div>
        </form>
    </div>
</template>
