<script>
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import ConnectionRequestForm from "./ConnectionRequestForm.vue";
import { useUserStore } from "@/Store/UserStore";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import axios from "axios";
import NoRecords from "@/Components/NoRecords.vue";
import RequestList from "./RequestList.vue";
import Paginator from "primevue/paginator";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        Modal,
        ConnectionRequestForm,
        ConfirmationModal,
        NoRecords,
        RequestList,
        Paginator,
    },
    data() {
        return {
            requests: [],
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
                this.requests = response.data.connections;
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
                this.requests.data.push(data.business_request);
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
        onPageChange() {},
        onRowChange() {},
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

        <!-- Sent Requests -->
        <div class="mb-10">
            <RequestList
                :requests="requests?.data || []"
                requestType="sent"
                pendingActionText="Cancel Request"
                approvedActionText="Terminate Connection"
                :pendingAction="
                    (request) => startMakingRequestChanges(request, 'Cancel')
                "
                :approvedAction="
                    (request) => startMakingRequestChanges(request, 'Terminate')
                "
            />
            <Paginator
                v-if="requests?.data?.length > 0"
                :totalRecords="requests?.total"
                :rows="requests?.per_page"
                :first="(requests?.current_page - 1) * requests?.per_page"
                @page="onPageChange"
                @update:rows="onRowChange"
                :rowsPerPageOptions="[10, 20, 50]"
            >
            </Paginator>
        </div>
    </AuthenticatedLayout>
</template>
