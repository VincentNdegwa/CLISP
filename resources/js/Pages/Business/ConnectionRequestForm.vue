<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import { useUserStore } from "@/Store/UserStore";
import axios from "axios";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import LoadingIcon from "@/Components/LoadingIcon.vue";

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
            searching: false,
            searchPerformed: false,
            requestSent: false,
        };
    },
    methods: {
        searchBusiness() {
            clearTimeout(this.debounceTimeout);
            this.searchPerformed = false;

            if (this.searchTerm.trim()) {
                this.searching = true;
                this.debounceTimeout = setTimeout(() => {
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
                            this.searchPerformed = true;
                        })
                        .catch((error) => {
                            console.error(error);
                            this.notification.open = true;
                            this.notification.message =
                                "An error occurred while searching for businesses.";
                            this.notification.status = "error";
                        })
                        .finally(() => {
                            this.searching = false;
                        });
                }, 1000);
            } else {
                this.searchResults = [];
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
                this.requestSent = false;
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
                            this.requestSent = true;
                            this.notification.open = true;
                            this.notification.message = "Connection request sent successfully!";
                            this.notification.status = "success";
                            this.$emit("addRequest", res.data);
                            setTimeout(() => {
                                this.$emit("close");
                            }, 2000);
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
                this.notification.open = true;
                this.notification.message = "Please select a business to connect with.";
                this.notification.status = "warning";
            }
        },
    },
    components: {
        PrimaryButton,
        PrimaryRoseButton,
        TextInput,
        InputLabel,
        LoadingIcon,
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
    <div class="mx-auto p-5 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-50">
        <h1 class="text-2xl font-bold mb-6 text-slate-900 dark:text-slate-100">Connect with Another Business</h1>
        <form @submit.prevent="sendConnectionRequest">
            <div class="mb-4">
                <InputLabel for="business" value="Search Business" />
                <div class="relative">
                    <TextInput
                        id="business"
                        type="text"
                        v-model="searchTerm"
                        @input="searchBusiness"
                        class="w-full mt-2"
                        placeholder="Type business name..."
                        :disabled="loading"
                    />
                </div>
                <div v-if="searching" class="flex items-center justify-center mt-2">
                    <LoadingIcon class="h-5 w-5" />
                </div>
                
                <!-- Search Results -->
                <div
                    v-if="searchResults.length > 0"
                    class="mt-2 border border-slate-300 dark:border-slate-600 rounded bg-slate-50 dark:bg-slate-800"
                >
                    <ul>
                        <li
                            v-for="result in searchResults"
                            :key="result.id"
                            @click="selectBusiness(result)"
                            class="p-2 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer text-slate-900 dark:text-slate-100"
                        >
                            {{ result.business_name }} ({{ result.location }})
                        </li>
                    </ul>
                </div>
                
                <!-- No Results Message -->
                <div 
                    v-if="searchPerformed && searchResults.length === 0 && !searching" 
                    class="mt-2 p-3 text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded"
                >
                    No businesses found matching "{{ searchTerm }}". Please try a different search term.
                </div>
                
                <!-- Selected Business -->
                <div 
                    v-if="selectedBusiness" 
                    class="mt-4 p-3 border border-slate-300 dark:border-slate-600 rounded bg-slate-100 dark:bg-slate-700"
                >
                    <div class="font-medium text-slate-900 dark:text-slate-100">Selected Business:</div>
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-slate-800 dark:text-slate-200">{{ selectedBusiness.business_name }}</div>
                            <div class="text-sm text-slate-600 dark:text-slate-400">{{ selectedBusiness.location }}</div>
                        </div>
                        <button 
                            type="button" 
                            @click="selectedBusiness = null; searchTerm = ''" 
                            class="text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-300"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Success Message -->
            <div 
                v-if="requestSent" 
                class="mb-4 p-3 bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-100 rounded"
            >
                Connection request sent successfully! Closing this dialog...
            </div>
            
            <div class="flex gap-3">
                <PrimaryButton
                    type="submit"
                    class="text-white p-2 rounded flex-1"
                    :disabled="loading || !selectedBusiness"
                >
                   {{ loading ? "Sending..." : "Send Connection Request" }} 
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
