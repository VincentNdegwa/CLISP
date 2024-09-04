<script>
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import ConnectionRequestForm from "./ConnectionRequestForm.vue";

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
        };
    },
    mounted() {
        // this.fetchRequests();
    },
    methods: {
        fetchRequests() {
            axios.get("/api/my-connection-requests").then((response) => {
                this.sentRequests = response.data.sent;
                this.incomingRequests = response.data.incoming;
            });
        },
        acceptRequest(request) {
            axios
                .post(`/api/accept-connection-request/${request.id}`)
                .then((response) => {
                    request.status = "accepted";
                });
        },
        rejectRequest(request) {
            axios
                .post(`/api/reject-connection-request/${request.id}`)
                .then((response) => {
                    request.status = "rejected";
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
    },
};
</script>

<template>
    <Head title="Business Connections" />
    <Modal :show="modal.open" @close="closeModal">
        <ConnectionRequestForm
            @close="closeModal"
            v-if="modal.component == 'ConnectionRequestForm'"
        />
    </Modal>
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
                            {{ request.connected_business.business_name }} ({{
                                request.status
                            }})
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
                            {{ request.business.business_name }} ({{
                                request.status
                            }})
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
