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
import ModularDataTable from "@/Components/ModularDataTable.vue";
import LoadingUI from "@/Components/LoadingUI.vue";
import Badge from "primevue/badge";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        Modal,
        ConnectionRequestForm,
        ConfirmationModal,
        NoRecords,
        ModularDataTable,
        LoadingUI,
        Badge,
    },
    data() {
        return {
            requests: [],
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
            tableColumns: [
                {
                    header: "Request From",
                    field: "business_requester.business_name",
                    sortable: true,
                },
                {
                    header: "Request To",
                    field: "business_receiver.business_name",
                    sortable: true,
                },
                {
                    header: "Request Type",
                    field: "request_type",
                    sortable: true,
                },
                {
                    header: "Status",
                    field: "connection_status",
                    sortable: true,
                    template: this.renderStatus,
                },
                {
                    header: "Date",
                    field: "created_at",
                    sortable: true,
                    format: (value) => this.formatDate(value),
                },
            ],
            tableFilters: [
                {
                    label: "Status",
                    field: "connection_status",
                    type: "dropdown",
                    options: [
                        { label: "All", value: null },
                        { label: "Pending", value: "pending" },
                        { label: "Approved", value: "approved" },
                        { label: "Rejected", value: "rejected" },
                    ],
                },
            ],
            searchQuery: "",
            currentPage: 1,
            rowsPerPage: 10,
            sortField: null,
            sortOrder: null,
        };
    },
    computed: {
        tableRowActions() {
            return [
                // {
                //     label: "View Details",
                //     icon: "pi pi-eye",
                //     command: (row) => this.viewDetails(row),
                // },
                {
                    label: "Cancel Request",
                    icon: "pi pi-times",
                    command: (row) => this.startMakingRequestChanges(row, "Cancel"),
                    visible: (row) => row.connection_status === "pending" && row.request_type=='sent',
                },
                {
                    label: "Approve Request",
                    icon: "pi pi-check",
                    command: (row) => this.startMakingRequestChanges(row, "Approve"),
                    visible: (row) => row.connection_status === "pending" && row.request_type !=='sent',
                },
                {
                    label: "Reject Request",
                    icon: "pi pi-times",
                    command: (row) => this.startMakingRequestChanges(row, "Reject"),
                    visible: (row) => row.connection_status === "pending" && row.request_type !=='sent',
                },
                {
                    label: "Terminate Connection",
                    icon: "pi pi-trash",
                    command: (row) => this.startMakingRequestChanges(row, "Terminate"),
                    visible: (row) => row.connection_status === "approved",
                },
            ];
        },
        filteredRowActions() {
            return this.requests?.data?.map(row => {
                return this.tableRowActions.filter(action => {
                    return !action.visible || action.visible(row);
                });
            }) || [];
        },
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
                this.loading = true;
                const response = await axios.post(url, {
                    request_id: request.id,
                    ...additionalData,
                });

                if (!response.data.error) {
                    this.requests.data = this.requests.data.map((req) => {
                        if (req.id === request.id) {
                            return { ...req, connection_status: response.data.connection_status };
                        }
                        return req;
                    });
                    this.displayNotification(response.data.message, "success");
                } else {
                    this.displayNotification(response.data.message, "error");
                }
            } catch (error) {
                this.displayNotification(
                    "An error occurred, please try again later",
                    "error"
                );
            } finally {
                this.loading = false;
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
        onPageChange(event) {
            this.currentPage = event.page + 1;
        },
        onRowsChange(rows) {
            this.rowsPerPage = rows;
        },
        onSort(event) {
            this.sortField = event.sortField;
            this.sortOrder = event.sortOrder;
        },
        onSearch(query) {
            this.searchQuery = query;
        },
        onFilterChange(filters) {
            console.log("Filters changed:", filters);
        },
        formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return new Intl.DateTimeFormat('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            }).format(date);
        },
        renderStatus(status, rowData) {
            const statusMap = {
                pending: { severity: 'warning', label: 'Pending' },
                approved: { severity: 'success', label: 'Approved' },
                rejected: { severity: 'danger', label: 'Rejected' }
            };
            
            const statusInfo = statusMap[status] || { severity: 'info', label: status };
            
            return `<span class="p-tag p-tag-${statusInfo.severity}">${statusInfo.label}</span>`;
        },
        // viewDetails(row) {
        //     console.log("View details for:", row);
        // }
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
        <LoadingUI v-if="loading" customClass="h-[90vh]" />
        <ModularDataTable
            v-else
                :value="requests?.data || []"
                :loading="loading"
                :columns="tableColumns"
                :rowActions="tableRowActions"
                :filters="tableFilters"
                :totalRecords="requests?.total || 0"
                :rows="requests?.per_page || 10"
                :currentPage="requests?.current_page || 1"
                :rowsPerPageOptions="[10, 20, 50]"
                :startActions="[
                    {
                        label: 'Request',
                        icon: 'pi pi-plus',
                        command: () => openModal('ConnectionRequestForm')
                    }
                ]"
                @page-change="onPageChange"
                @rows-change="onRowsChange"
                @sort="onSort"
                @search="onSearch"
                @filter-change="onFilterChange"
                dataKey="id"
                :rowHover="true"
                emptyMessage="No connection requests found"
            />
    </AuthenticatedLayout>
</template>
