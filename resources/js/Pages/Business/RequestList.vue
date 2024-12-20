<template>
    <DataTable
        v-if="requests.length > 0"
        :value="requests"
        class="min-h-[75vh]"
        responsiveLayout="scroll"
    >
        <Column field="request_type" header="Trend">
            <template #body="slotProps">
                <i :class="getTrend(slotProps.data.request_type)"></i>
            </template>
        </Column>
        <Column field="business_name" header="Business">
            <template #body="slotProps">
                <span>{{ getBusinessName(slotProps.data) }}</span>
            </template>
        </Column>
        <Column field="connection_status" header="Status">
            <template #body="slotProps">
                <Badge
                    :value="slotProps.data.connection_status"
                    :severity="
                        getBadgeSeverity(slotProps.data.connection_status)
                    "
                    class="capitalize"
                />
            </template>
        </Column>
        <Column field="user_requester.name" header="Requested By">
            <template #body="slotProps">
                <span>{{ slotProps.data.user_requester.name }}</span>
            </template>
        </Column>
        <Column field="user_requester.email" header="Requester Email">
            <template #body="slotProps">
                <span>{{ slotProps.data.user_requester.email }}</span>
            </template>
        </Column>
        <Column field="created_at" header="Request Date">
            <template #body="slotProps">
                <span>{{
                    new Date(slotProps.data.created_at).toLocaleString()
                }}</span>
            </template>
        </Column>
        <Column field="email" :header="emailLabel">
            <template #body="slotProps">
                <span>{{ getReceiverEmail(slotProps.data) }}</span>
            </template>
        </Column>
        <Column field="phone_number" :header="phoneLabel">
            <template #body="slotProps">
                <span>{{ getReceiverPhone(slotProps.data) }}</span>
            </template>
        </Column>
        <Column header="Actions">
            <template #body="slotProps">
                <div class="card flex justify-center">
                    <Button
                        type="button"
                        icon="pi pi-ellipsis-v"
                        @click="toggle(slotProps.data, $event)"
                        aria-haspopup="true"
                        severity="contrast"
                        size="small"
                        aria-controls="overlay_menu"
                    />
                    <Menu
                        ref="menu"
                        :id="'overlay_menu_' + slotProps.data.id"
                        :model="actionItem"
                        :popup="true"
                    />
                </div>
            </template>
        </Column>
    </DataTable>
    <NoRecords v-else />
</template>

<script>
import NoRecords from "@/Components/NoRecords.vue";
import Badge from "primevue/badge";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Menu from "primevue/menu";

export default {
    props: {
        requests: Array,
        request_type: String,
        pendingActionText: String,
        approvedActionText: String,
        cancelAction: Function,
        approveAction: Function,
        rejectAction: Function,
        terminateAction: Function,
    },
    components: {
        DataTable,
        Column,
        Badge,
        NoRecords,
        Menu,
        Button,
    },

    methods: {
        getBusinessName(request) {
            return this.request_type === "sent"
                ? request.business_receiver.business_name
                : request.business_requester.business_name;
        },
        getReceiverEmail(request) {
            return this.request_type === "sent"
                ? request.business_receiver.email
                : request.business_requester.email;
        },
        getReceiverPhone(request) {
            return this.request_type === "sent"
                ? request.business_receiver.phone_number
                : request.business_requester.phone_number;
        },
        getBadgeSeverity(status) {
            return {
                approved: "success",
                pending: "warning",
                rejected: "danger",
                cancelled: "info",
                terminated: "secondary",
            }[status];
        },
        handleRequestChange(request, action) {
            if (action === "cancelAction") {
                this.cancelAction(request);
            } else if (action === "approveAction") {
                this.approveAction(request);
            } else if (action === "terminateAction") {
                this.terminateAction(request);
            } else if (action == "rejectAction") {
                this.rejectAction(request);
            }
        },
        toggle(data, event) {
            this.watchRequests(data);
            this.selectedData = data;
            this.$refs.menu.toggle(event);
        },
        watchRequests(data) {
            console.log(data);
            this.actionItem = [];

            let canCancel = {
                label: "Cancel Request",
                icon: "pi pi-times",
                command: () => {
                    this.handleRequestChange(data, "cancelAction");
                },
            };

            let canApprove = {
                label: "Approve Request",
                icon: "pi pi-check",
                command: () => {
                    this.handleRequestChange(data, "approveAction");
                },
            };
            let canTerminate = {
                label: "Terminate Connection",
                icon: "pi pi-times",
                command: () => {
                    this.handleRequestChange(data, "terminateAction");
                },
            };

            let canReject = {
                label: "Reject Request",
                icon: "pi pi-times",
                command: () => {
                    this.handleRequestChange(data, "rejectAction");
                },
            };
            if (
                data.connection_status === "pending" &&
                data.request_type === "sent"
            ) {
                this.actionItem = [canCancel];
            }
            if (
                data.connection_status === "pending" &&
                data.request_type === "receive"
            ) {
                this.actionItem = [canApprove, canReject];
            }
            if (data.connection_status === "approved") {
                this.actionItem = [canTerminate];
            }
        },
        getTrend(trend) {
            if (trend == "sent") {
                return "pi pi-arrow-up-right";
            }

            if (trend == "receive") {
                return "pi pi-arrow-down-left";
            }
        },
    },
    computed: {
        emailLabel() {
            return this.request_type === "sent"
                ? "Receiver Email"
                : "Requester Email";
        },
        phoneLabel() {
            return this.request_type === "sent"
                ? "Receiver Phone"
                : "Requester Phone";
        },
    },
    watch: {},
    data() {
        return {
            selectedData: null,
            actionItem: [],
        };
    },
};
</script>
