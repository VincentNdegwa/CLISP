<script>
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import ConnectionRequestForm from "./ConnectionRequestForm.vue";
import { useUserStore } from "@/Store/UserStore";
export default {
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        Modal,
        ConnectionRequestForm,
    },
    data() {
        return {
            activeTab: "sent",
            sentRequests: [],
            incomingRequests: [],
            modal: {
                open: false,
                component: "",
            },
            loading: false,
            notification: {
                open: false,
                message: "",
                status: "error",
            },
        };
    },
    async mounted() {
        const userStore = useUserStore();

        const businessId = userStore.business;
        if (businessId) {
            this.loading = true;
            await this.fetchRequests(businessId);
        }
    },
    methods: {
        async fetchRequests(businessId) {
            try {
                const response = await axios.get(
                    `/api/business/connection-requests/${businessId}`
                );
                this.loading = false;
                this.sentRequests = response.data.sent;
                this.incomingRequests = response.data.incoming;
            } catch (error) {
                this.loading = false;
                this.notification.open = true;
                this.notification.message = "Failed to fetch requests";
                this.notification.status = "error";
            }
        },
        acceptRequest(request) {
            axios
                .post(`/api/business/accept-connection-request/${request.id}`)
                .then((response) => {
                    request.connection_status = "accepted";
                });
        },
        rejectRequest(request) {
            axios
                .post(`/api/business/reject-connection-request/${request.id}`)
                .then((response) => {
                    request.connection_status = "rejected";
                });
        },
        closeModal() {
            this.modal.open = false;
            this.modal.component = "";
        },
        openConnectionForm() {
            this.modal.open = true;
            this.modal.component = "ConnectionRequestForm";
        },
        addRequest(data) {
            if (!data.error) {
                this.notification.open = true;
                this.notification.message = data.message;
                this.notification.status = "success";
                this.sentRequests.push(data.business_request);
            }
        },
    },
};
</script>

<template>
    <Head title="Business Connections" />
    <Modal :show="modal.open" @close="closeModal">
        <ConnectionRequestForm
            @close="closeModal"
            @addRequest="addRequest"
            v-if="modal.component == 'ConnectionRequestForm'"
        />
    </Modal>
    <AlertNotification
        :open="notification.open"
        :message="notification.message"
        :status="notification.status"
    />
    <AuthenticatedLayout>
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold mb-6">Business Connections</h1>
            <PrimaryButton @click="openConnectionForm">
                Make Request
            </PrimaryButton>
        </div>

        <!-- Tabs -->
        <div class="flex border-b mb-6">
            <button
                @click="activeTab = 'sent'"
                :class="[
                    'py-2 px-4 rounded-t-lg transition-all ease-linear duration-150',
                    activeTab === 'sent'
                        ? 'border-b-2 border-blue-600 bg-slate-700 text-white'
                        : '',
                ]"
            >
                My Requests
            </button>
            <button
                @click="activeTab = 'incoming'"
                :class="[
                    'py-2 px-4 rounded-t-lg transition-all ease-linear duration-150',
                    activeTab === 'incoming'
                        ? 'border-b-2 border-blue-600 bg-slate-700 text-white'
                        : '',
                ]"
            >
                Incoming Requests
            </button>
        </div>

        <!-- Sent Requests -->
        <div v-if="activeTab === 'sent'" class="mb-10">
            <h2 class="text-xl font-semibold mb-4">Sent Requests</h2>
            <div v-if="sentRequests.length">
                <ul>
                    <li
                        v-for="request in sentRequests"
                        :key="request.id"
                        class="p-4 border-b border-gray-300"
                    >
                        <p>
                            <strong>To:</strong>
                            {{ request.business_receiver.business_name }} ({{
                                request.connection_status
                            }})
                        </p>
                        <p>
                            <strong>Requested By:</strong>
                            {{ request.user_requester.name }} ({{
                                request.user_requester.email
                            }})
                        </p>
                        <p>
                            <strong>Request Date:</strong>
                            {{ new Date(request.created_at).toLocaleString() }}
                        </p>
                        <p>
                            <strong>Receiver Email:</strong>
                            {{ request.business_receiver.email }}
                        </p>
                        <p>
                            <strong>Receiver Phone:</strong>
                            {{ request.business_receiver.phone_number }}
                        </p>
                    </li>
                </ul>
            </div>
            <div v-else class="text-gray-700">No sent requests.</div>
        </div>

        <!-- Incoming Requests -->
        <div v-if="activeTab === 'incoming'">
            <h2 class="text-xl font-semibold mb-4">Incoming Requests</h2>
            <div v-if="incomingRequests.length">
                <ul>
                    <li
                        v-for="request in incomingRequests"
                        :key="request.id"
                        class="p-4 border-b border-gray-300"
                    >
                        <p>
                            <strong>From:</strong>
                            {{ request.business_requester.business_name }} ({{
                                request.connection_status
                            }})
                        </p>
                        <p>
                            <strong>Requested By:</strong>
                            {{ request.user_requester.name }} ({{
                                request.user_requester.email
                            }})
                        </p>
                        <p>
                            <strong>Request Date:</strong>
                            {{ new Date(request.created_at).toLocaleString() }}
                        </p>
                        <p>
                            <strong>Requester Email:</strong>
                            {{ request.business_requester.email }}
                        </p>
                        <p>
                            <strong>Requester Phone:</strong>
                            {{ request.business_requester.phone_number }}
                        </p>
                        <div class="mt-2">
                            <button
                                @click="acceptRequest(request)"
                                class="bg-green-600 text-white px-4 py-2 rounded mr-2"
                            >
                                Accept
                            </button>
                            <button
                                @click="rejectRequest(request)"
                                class="bg-red-600 text-white px-4 py-2 rounded"
                            >
                                Reject
                            </button>
                        </div>
                    </li>
                </ul>
            </div>
            <div v-else class="text-gray-700">No incoming requests.</div>
        </div>
    </AuthenticatedLayout>
</template>
