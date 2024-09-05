<script>
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import ConnectionRequestForm from "./ConnectionRequestForm.vue";
import { useUserStore } from "@/Store/UserStore";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import axios from "axios";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        Modal,
        ConnectionRequestForm,
        ConfirmationModal,
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
            confirmation: {
                isOpen: false,
                title: "",
                message: "",
                method: null,
            },
            selectedRequest: null,
            isDropdownOpen: false,
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
                this.sentRequests = response.data.sent;
                this.incomingRequests = response.data.incoming;
            } catch (error) {
                this.displayNotification("Failed to fetch requests", "error");
            } finally {
                this.loading = false;
            }
        },
        async makeRequestChange(url, request, additionalData = {}) {
            try {
                const response = await axios.post(url, {
                    request_id: request.id,
                    ...additionalData,
                });

                if (!response.data.error) {
                    request.connection_status = response.data.newStatus;
                    this.displayNotification(response.data.message, "success");
                } else {
                    this.displayNotification(response.data.message, "error");
                }
            } catch (error) {
                this.displayNotification(
                    "An error occurred, please try again later",
                    "error"
                );
            }
        },
        acceptRequest(request) {
            this.makeRequestChange(
                "/api/business/approve-connection-request",
                request,
                {
                    receiving_user_id: this.$page.props.auth.user.id,
                }
            );
        },
        rejectRequest(request) {
            this.makeRequestChange(
                "/api/business/reject-connection-request",
                request,
                {
                    receiving_user_id: this.$page.props.auth.user.id,
                }
            );
        },
        cancelRequest(request) {
            this.makeRequestChange(
                "/api/business/cancel-connection-request",
                request
            );
        },
        terminateConnection(request) {
            this.makeRequestChange(
                "/api/business/terminate-connection",
                request
            );
        },
        openModal(component) {
            this.modal.open = true;
            this.modal.component = component;
        },
        closeModal() {
            this.modal.open = false;
            this.modal.component = "";
        },
        addRequest(data) {
            if (!data.error) {
                this.displayNotification(data.message, "success");
                this.closeModal();
                this.sentRequests.push(data.business_request);
            }
        },
        displayNotification(message, status) {
            this.notification.message = message;
            this.notification.status = status;
            this.notification.open = true;
        },
        startMakingRequestChanges(request, method) {
            console.log(method);

            this.selectedRequest = request;

            switch (method) {
                case "Approve":
                    this.confirmation.title = "Approve Request";
                    this.confirmation.message =
                        "Are you sure you want to approve this connection request?";
                    this.confirmation.method = this.acceptRequest;
                    break;
                case "Reject":
                    this.confirmation.title = "Reject Request";
                    this.confirmation.message =
                        "Are you sure you want to reject this connection request?";
                    this.confirmation.method = this.rejectRequest;
                    break;
                case "Cancel":
                    this.confirmation.title = "Cancel Request";
                    this.confirmation.message =
                        "Are you sure you want to cancel this connection request?";
                    this.confirmation.method = this.cancelRequest;
                    break;
                case "Terminate":
                    this.confirmation.title = "Terminate Connection";
                    this.confirmation.message =
                        "Are you sure you want to terminate this connection?";
                    this.confirmation.method = this.terminateConnection;
                    break;
            }

            this.confirmation.isOpen = true;
        },
        confirmAction() {
            if (this.confirmation.method && this.selectedRequest) {
                this.confirmation.method(this.selectedRequest);
            }
            this.confirmation.isOpen = false;
            this.selectedRequest = null;
        },
        cancelMakingRequest() {
            this.confirmation.isOpen = false;
            this.selectedRequest = null;
            this.confirmation.message = "";
            this.confirmation.title = "";
            this.confirmation.method = null;
        },
        toggleDropdown() {
            this.isDropdownOpen = !this.isDropdownOpen;
        },
    },
};
</script>

<template>
    <Head title="Business Connections" />
    <ConfirmationModal
        :isOpen="confirmation.isOpen"
        :title="confirmation.title"
        :message="confirmation.message"
        @confirm="confirmAction"
        @close="cancelMakingRequest"
    />
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
            <div class="flex items-center">
                <div class="dropdown">
                    <div
                        tabindex="0"
                        role="button"
                        class="btn m-1 bg-slate-900 text-white"
                        @click="toggleDropdown"
                    >
                        Filters <i class="bi bi-funnel"></i>
                    </div>
                    <ul
                        tabindex="0"
                        v-if="isDropdownOpen"
                        class="dropdown-content flex flex-col gap-2 bg-white text-slate-900 rounded-box z-[1] w-52 p-2 shadow"
                    >
                        <li>
                            <div class="flex flex-col gap-1">
                                <div class="inline-block">Filter By Status</div>
                                <select
                                    class="select select-bordered bg-white text-slate-950 ring-1 ring-slate-800"
                                >
                                    <option
                                        value="Filter By Status"
                                        selected
                                        disabled
                                    >
                                        Filter By Status
                                    </option>
                                </select>
                            </div>
                        </li>
                        <li
                            class="mt-3 p-2 bg-slate-900 text-white rounded-md text-center hover:bg-slate-800 transition-all ease-linear duration-700"
                        >
                            <button @click="clearFilters">
                                Clear Filters <i class="bi bi-trash"></i>
                            </button>
                        </li>
                    </ul>
                </div>
                <PrimaryButton
                    @click="() => openModal('ConnectionRequestForm')"
                >
                    Make Request
                </PrimaryButton>
            </div>
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
            <h2 class="text-xl font-semibold mb-4 text-center">
                Sent Requests
            </h2>
            <div class="min-h-[70vh] h-fit flex flex-wrap gap-2">
                <div
                    v-if="sentRequests.length"
                    v-for="request in sentRequests"
                    :key="request.id"
                    class="h-fit w-full md:w-96"
                >
                    <div
                        class="p-6 bg-white shadow-md rounded-lg border border-gray-300 flex flex-col justify-between"
                    >
                        <div>
                            <p class="text-lg font-bold text-gray-800 mb-2">
                                {{ request.business_receiver.business_name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Status:</strong>
                                <span
                                    class="capitalize ms-1 font-bold"
                                    :class="{
                                        'text-green-500':
                                            request.connection_status ===
                                            'approved',
                                        'text-yellow-500':
                                            request.connection_status ===
                                            'pending',
                                        'text-red-500':
                                            request.connection_status ===
                                            'rejected',
                                        'text-orange-500':
                                            request.connection_status ===
                                            'cancelled',
                                        'text-purple-500':
                                            request.connection_status ===
                                            'terminated',
                                    }"
                                >
                                    {{ request.connection_status }}
                                </span>
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Requested By:</strong>
                                {{ request.user_requester.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Email:</strong>
                                {{ request.user_requester.email }}
                            </p>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">
                                <strong>Request Date:</strong>
                                {{
                                    new Date(
                                        request.created_at
                                    ).toLocaleString()
                                }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Receiver Email:</strong>
                                {{ request.business_receiver.email }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Receiver Phone:</strong>
                                {{ request.business_receiver.phone_number }}
                            </p>
                        </div>
                        <div class="mt-4">
                            <button
                                v-if="request.connection_status === 'pending'"
                                @click="
                                    startMakingRequestChanges(request, 'Cancel')
                                "
                                class="w-full bg-red-600 text-white px-4 py-2 rounded mt-2"
                            >
                                Cancel Request
                            </button>
                            <button
                                v-else-if="
                                    request.connection_status === 'approved'
                                "
                                @click="
                                    startMakingRequestChanges(
                                        request,
                                        'Terminate'
                                    )
                                "
                                class="w-full bg-yellow-500 text-white px-4 py-2 rounded mt-2"
                            >
                                Terminate Connection
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center text-gray-700">
                    No sent requests.
                </div>
            </div>
        </div>

        <!-- Incoming Requests -->
        <div v-if="activeTab === 'incoming'">
            <h2 class="text-xl font-semibold mb-4 text-center">
                Incoming Requests
            </h2>
            <div class="min-h-[70vh] h-fit flex flex-wrap gap-2">
                <div
                    v-if="incomingRequests.length"
                    v-for="request in incomingRequests"
                    :key="request.id"
                    class="h-fit w-full md:w-96"
                >
                    <div
                        class="p-6 bg-white shadow-md rounded-lg border border-gray-300 flex flex-col justify-between"
                    >
                        <div>
                            <p class="text-lg font-bold text-gray-800 mb-2">
                                {{ request.business_requester.business_name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Status:</strong>
                                <span
                                    class="capitalize ms-1 font-bold"
                                    :class="{
                                        'text-green-500':
                                            request.connection_status ===
                                            'approved',
                                        'text-yellow-500':
                                            request.connection_status ===
                                            'pending',
                                        'text-red-500':
                                            request.connection_status ===
                                            'rejected',
                                        'text-orange-500':
                                            request.connection_status ===
                                            'cancelled',
                                        'text-purple-500':
                                            request.connection_status ===
                                            'terminated',
                                    }"
                                >
                                    {{ request.connection_status }}
                                </span>
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Requested By:</strong>
                                {{ request.user_requester.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Email:</strong>
                                {{ request.user_requester.email }}
                            </p>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-gray-600">
                                <strong>Request Date:</strong>
                                {{
                                    new Date(
                                        request.created_at
                                    ).toLocaleString()
                                }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Requester Email:</strong>
                                {{ request.business_requester.email }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <strong>Requester Phone:</strong>
                                {{ request.business_requester.phone_number }}
                            </p>
                        </div>
                        <div class="mt-4 flex justify-between">
                            <button
                                v-if="request.connection_status === 'pending'"
                                @click="
                                    startMakingRequestChanges(
                                        request,
                                        'Approve'
                                    )
                                "
                                class="bg-green-600 text-white px-4 py-2 rounded w-1/2 mr-2"
                            >
                                Accept
                            </button>
                            <button
                                v-if="request.connection_status === 'pending'"
                                @click="
                                    startMakingRequestChanges(request, 'Reject')
                                "
                                class="bg-red-600 text-white px-4 py-2 rounded w-1/2"
                            >
                                Reject
                            </button>
                            <button
                                v-else-if="
                                    request.connection_status === 'approved'
                                "
                                @click="
                                    startMakingRequestChanges(
                                        request,
                                        'Terminate'
                                    )
                                "
                                class="bg-yellow-500 text-white px-4 py-2 rounded w-1/2 flex-1"
                            >
                                Terminate Connection
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center text-gray-700">
                    No incoming requests.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
