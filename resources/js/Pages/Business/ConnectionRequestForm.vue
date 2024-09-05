<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import { useUserStore } from "@/Store/UserStore";
import axios from "axios";

export default {
    data() {
        return {
            searchTerm: "",
            searchResults: [],
            selectedBusiness: null,
            debounceTimeout: null,
            my_business_id: null,
            notification: {
                open: false,
                message: "",
                status: "error",
            },
            loading: false,
        };
    },
    methods: {
        searchBusiness() {
            clearTimeout(this.debounceTimeout);

            if (this.searchTerm.trim()) {
                this.debounceTimeout = setTimeout(() => {
                    this.loading = true;
                    axios
                        .get(
                            `/api/business/search-business?term=${this.searchTerm}`
                        )
                        .then((response) => {
                            this.searchResults =
                                response.data.businesses.filter(
                                    (item) =>
                                        item.business_id !== this.my_business_id
                                );
                        })
                        .catch((error) => {
                            console.error(error);
                            this.notification.open = true;
                            this.notification.message =
                                "An error occurred while searching for businesses.";
                            this.notification.status = "error";
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                }, 2000);
            }
        },
        selectBusiness(business) {
            this.selectedBusiness = business;
            this.searchTerm = business.business_name;
            this.searchResults = [];
        },
        sendConnectionRequest() {
            if (this.selectedBusiness && this.my_business_id) {
                this.loading = true;
                axios
                    .post("/api/business/send-connection-request", {
                        receiving_business_id:
                            this.selectedBusiness.business_id,
                        requesting_business_id: this.my_business_id,
                        requesting_user_id: this.$page.props.auth.user.id,
                    })
                    .then((res) => {
                        if (res.data.error) {
                            this.notification.open = true;
                            this.notification.message = res.data.message;
                            this.notification.status = "error";
                        } else {
                            this.$emit("addRequest", res.data);
                        }
                    })
                    .catch((error) => {
                        const error_msg = error.response
                            ? error.response.data.message
                            : error.message;
                        console.error(error);
                        this.notification.open = true;
                        this.notification.message = error_msg;
                        this.notification.status = "error";
                    })
                    .finally(() => {
                        this.loading = false;
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
    async mounted() {
        const userStore = useUserStore();

        const businessId = userStore.business;
        if (businessId) {
            this.my_business_id = businessId;
        }
    },
};
</script>

<template>
    <AlertNotification
        :open="notification.open"
        :message="notification.message"
        :status="notification.status"
        position="top"
    />
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
                    :disabled="loading"
                >
                    Send Connection Request
                </PrimaryButton>
                <PrimaryRoseButton
                    type="button"
                    @click="$emit('close')"
                    :disabled="loading"
                    class="flex-1"
                >
                    Cancel
                </PrimaryRoseButton>
            </div>
        </form>
    </div>
</template>
