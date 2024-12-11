<template>
    <DataTable
        v-if="requests.length > 0"
        :value="requests"
        class="min-h-[75vh]"
        responsiveLayout="scroll"
    >
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

                <!-- <div class="flex justify-between">
                    <button
                        v-if="slotProps.data.connection_status === 'pending'"
                        @click="
                            handleRequestChange(slotProps.data, 'pendingAction')
                        "
                        class="bg-green-600 text-white px-4 py-2 rounded mr-2"
                    >
                        {{ pendingActionText }}
                    </button>
                    <button
                        v-else-if="
                            slotProps.data.connection_status === 'approved'
                        "
                        @click="
                            handleRequestChange(
                                slotProps.data,
                                'approvedAction'
                            )
                        "
                        class="bg-yellow-500 text-white px-4 py-2 rounded"
                    >
                        {{ approvedActionText }}
                    </button>
                </div> -->
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
        requests: Object,
        requestType: String,
        pendingActionText: String,
        approvedActionText: String,
        pendingAction: Function,
        approvedAction: Function,
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
            return this.requestType === "sent"
                ? request.business_receiver.business_name
                : request.business_requester.business_name;
        },
        getReceiverEmail(request) {
            return this.requestType === "sent"
                ? request.business_receiver.email
                : request.business_requester.email;
        },
        getReceiverPhone(request) {
            return this.requestType === "sent"
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
            if (action === "pendingAction") {
                this.pendingAction(request);
            } else if (action === "approvedAction") {
                this.approvedAction(request);
            }
        },
        toggle(data, event) {
            this.selectedData = data;
            this.watchRequests(data);
            this.$refs.menu.toggle(event);
        },
        watchRequests(data) {
            this.actionItem = [];

            let canCancel = {
                label: "Cancel Request",
                icon: "pi pi-arrow-up-right",
                command: () => {
                    this.handleRequestChange(data, "pendingAction");
                },
            };
            let canTerminate = {
                label: "Terminate Connection",
                icon: "pi pi-arrow-up-right",
                command: () => {
                    this.handleRequestChange(data, "approvedAction");
                },
            };
            if (data.connection_status === "pending") {
                this.actionItem = [canCancel];
            }
            if (data.connection_status === "approved") {
                this.actionItem = [canTerminate];
            }
        },
    },
    computed: {
        emailLabel() {
            return this.requestType === "sent"
                ? "Receiver Email"
                : "Requester Email";
        },
        phoneLabel() {
            return this.requestType === "sent"
                ? "Receiver Phone"
                : "Requester Phone";
        },
    },
    data() {
        return {
            selectedData: null,
            actionItem: [],
        };
    },
};
</script>
