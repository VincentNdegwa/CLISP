<template>
    <Head title="Logistic Shipments" />
    <AuthenticatedLayout>
        <h2 class="text-2xl font-bold mb-3">Logistic Shipments</h2>
        <Toolbar class="bg-slate-900" style="padding: 0rem 1rem">
            <template #start>
                <div class="flex gap-">
                    <div class="flex items-center" ref="dropdown">
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
                                class="dropdown-content flex flex-col gap-2 bg-white text-slate-900 rounded-t-none rounded-b-md z-[100] min-w-52 p-2 shadow"
                            >
                                <li class="flex flex-col">
                                    <span>Transaction Status</span>
                                    <Select
                                        @change="toggleDropdown"
                                        :options="statuses"
                                        v-model="filterParams.status"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Select Status"
                                        class="w-full"
                                    />
                                </li>

                                <li class="flex flex-col">
                                    <span>Transaction Type</span>
                                    <Select
                                        @change="toggleDropdown"
                                        :options="transaction_types"
                                        v-model="filterParams.incoming"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Select Status"
                                        class="w-full"
                                    />
                                </li>
                            </ul>
                        </div>

                        <PrimaryButton
                            @click="clearFilters"
                            class="flex gap-1 bg-slate-900"
                        >
                            <span>Clear Filters</span>
                            <i class="bi bi-x-lg"></i>
                        </PrimaryButton>
                    </div>
                </div>
            </template>
            <template #end>
                <div class="flex gap-1">
                    <PrimaryButton
                        @click="collapseAll"
                        class="flex gap-2 max:h-fit"
                    >
                        <span> Callapse All </span> <i class="pi pi-minus"></i>
                    </PrimaryButton>
                    <PrimaryButton
                        @click="exportCSV"
                        class="flex gap-2 max:h-fit"
                    >
                        <span> Export </span>
                        <i class="pi pi-external-link"></i>
                    </PrimaryButton>
                </div>
            </template>
        </Toolbar>
        <TableSkeleton v-if="transactionStore.loading" />
        <DataTable
            v-else-if="
                transactionStore.shipments.data &&
                transactionStore.shipments.data?.data.length > 0
            "
            v-model:expandedRows="expandedRows"
            :value="transactionStore.shipments.data?.data"
            :loading="transactionStore.loading"
            dataKey="id"
            ref="dt"
            tableStyle="min-width: 60rem"
        >
            <!-- Expander Column -->
            <Column expander style="width: 1rem" />

            <!-- Transaction Type (Borrowing/Leasing) -->
            <Column field="type" header="Type" />

            <!-- Initiator Business -->
            <Column header="Initiator">
                <template #body="slotProps">
                    {{ slotProps.data.initiator.business_name }}
                </template>
            </Column>

            <!-- Receiver Business or Customer -->
            <Column header="Receiver">
                <template #body="slotProps">
                    <span v-if="slotProps.data.receiver_business">
                        {{ slotProps.data.receiver_business.business_name }}
                    </span>
                    <span v-else-if="slotProps.data.receiver_customer">
                        {{ slotProps.data.receiver_customer.full_names }}
                    </span>
                    <span v-else> --- </span>
                </template>
            </Column>

            <!-- Lease Start/End Dates -->
            <Column header="Lease Period">
                <template #body="slotProps">
                    {{ formatDate(slotProps.data.lease_start_date) }} -
                    {{ formatDate(slotProps.data.lease_end_date) }}
                </template>
            </Column>

            <!-- Status -->
            <Column header="Status">
                <template #body="slotProps">
                    <Tag
                        :value="slotProps.data.status"
                        :severity="getStatusSeverity(slotProps.data.status)"
                        style="text-transform: capitalize"
                    />
                </template>
            </Column>

            <!-- Total Price -->
            <Column header="Total Price">
                <template #body="slotProps">
                    {{ formatCurrency(slotProps.data.totalPrice) }}
                </template>
            </Column>

            <Column header="Actions">
                <template #body="slotProps">
                    <div
                        class="flex gap-1"
                        @click="() => selectTransaction(slotProps.data)"
                    >
                        <Button
                            label="Dispatch All"
                            @click="openModal('ShipmentCounts')"
                            size="small"
                            v-if="
                                slotProps.data.transaction_type == 'Outgoing' &&
                                (slotProps.data.status == 'paid' ||
                                    slotProps.data.status ==
                                        'partially-dispatched')
                            "
                            :disabled="slotProps.data.status == 'approved'"
                        />
                        <Button
                            label="Return All"
                            size="small"
                            severity="info"
                            @click="returnAll"
                            v-if="
                                slotProps.data.transaction_type == 'Incoming' &&
                                slotProps.data.status === 'completed' &&
                                slotProps.data.type !== 'purchase'
                            "
                        />
                        <Button
                            label="Receive All"
                            size="small"
                            severity="success"
                            @click="openModal('ReceiveCount')"
                            v-if="
                                slotProps.data.transaction_type ===
                                    'Incoming' &&
                                (slotProps.data.status === 'dispatched' ||
                                    slotProps.data.status ===
                                        'partially-dispatched')
                            "
                        />
                        <Button
                            label="Reject All"
                            size="small"
                            severity="danger"
                            @click="rejectAll"
                            v-if="
                                slotProps.data.transaction_type ===
                                    'Incoming' &&
                                (slotProps.data.status === 'dispatched' ||
                                    slotProps.data.status ===
                                        'partially-dispatched')
                            "
                        />
                    </div>
                </template>
            </Column>

            <!-- Row Expansion Template -->
            <template #expansion="slotProps">
                <div class="p-0 -mt-3">
                    <DataTable :value="slotProps.data.items" tableStyle="">
                        <Column header="Item Name">
                            <template #body="itemSlotProps">
                                {{ itemSlotProps.data.item.item_name }}
                            </template>
                        </Column>
                        <Column field="quantity" header="Quantity"></Column>

                        <Column field="status" header="Item Status">
                            <template #body="itemSlotProps">
                                <Tag
                                    :value="itemSlotProps.data.status"
                                    :severity="
                                        getItemSeverity(
                                            itemSlotProps.data.status
                                        )
                                    "
                                    style="text-transform: capitalize"
                                />
                            </template>
                        </Column>
                        <Column field="price" header="Unit Price">
                            <template #body="itemSlotProps">
                                {{ formatCurrency(itemSlotProps.data.price) }}
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </template>
        </DataTable>
        <NoRecords v-else />
        <Toast />
        <ConfirmationModal
            :isOpen="confirmation.isOpen"
            :title="confirmation.title"
            :message="confirmation.message"
            @confirm="confirmAction"
            @close="cancelMakingRequest"
        />
        <Modal :show="modal.open" @close="closeModal">
            <ShipmentCounts
                v-if="modal.component == 'ShipmentCounts'"
                :transaction="selectedTransactionData"
                @dispatchItems="dispatchAll"
                @close="closeModal"
            />
            <ReceiveCount
                v-if="modal.component == 'ReceiveCount'"
                :transaction="selectedTransactionData"
                @receiveItems="receiveAll"
                @close="closeModal"
            />
        </Modal>
    </AuthenticatedLayout>
    <AlertNotification
        :open="
            transactionStore.success != null || transactionStore.error != null
        "
        :message="transactionStore.success || transactionStore.error"
        :status="transactionStore.success ? 'success' : 'error'"
    />
</template>

<script>
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useTransactionStore } from "@/Store/TransactionStore";
import { Head } from "@inertiajs/vue3";
import Button from "primevue/button";
import Column from "primevue/column";
import DataTable from "primevue/datatable";
import Select from "primevue/select";
import Tag from "primevue/tag";
import Toast from "primevue/toast";
import Toolbar from "primevue/toolbar";
import { watch } from "vue";
import { onMounted, ref } from "vue";
import ShipmentCounts from "./ShipmentCounts.vue";
import ReceiveCount from "./ReceiveCount.vue";
import NoRecords from "@/Components/NoRecords.vue";
import { currencyConvertor } from "@/Store/CurrencyConvertStore";

export default {
    components: {
        Button,
        Column,
        DataTable,
        Tag,
        AuthenticatedLayout,
        Head,
        PrimaryButton,
        Toolbar,
        TableSkeleton,
        Toast,
        Select,
        ConfirmationModal,
        Modal,
        ShipmentCounts,
        ReceiveCount,
        NoRecords,
    },
    setup() {
        const transactionStore = useTransactionStore();
        const filterParams = ref({
            incoming: "all",
            type: "",
            items_count: 20,
            isB2B: "all",
            page: 0,
            search: "",
            status: "all",
        });

        onMounted(() => {
            transactionStore.getTransactionLogistics(filterParams.value);
        });
        const dispactItems = (dispatchparams) => {
            transactionStore.dispatchItems(dispatchparams);
        };
        const receiveItems = (receiverparams) => {
            transactionStore.receiveItems(receiverparams);
        };
        watch(
            filterParams,
            () => {
                transactionStore.getTransactionLogistics(filterParams.value);
            },
            { deep: true }
        );
        return {
            filterParams,
            transactionStore,
            dispactItems,
            receiveItems,
        };
    },
    data() {
        return {
            expandedRows: {},
            transaction_types: [
                { label: "All", value: "all" },
                { label: "Incomming", value: "incoming" },
                { label: "Outgoing", value: "outgoing" },
            ],
            statuses: [
                { label: "All", value: "all" },
                { label: "Paid", value: "paid" },
                { label: "Dispatched", value: "dispatched" },
                {
                    label: "Partially-Dispatched",
                    value: "partially-dispatched",
                },
                { label: "Completed", value: "completed" },
                { label: "Canceled", value: "canceled" },
                { label: "Return", value: "return" },
            ],
            isDropdownOpen: false,
            confirmation: {
                isOpen: false,
                title: "",
                message: "",
                method: null,
            },
            modal: {
                open: false,
                component: "",
            },
            selectedTransactionData: {},
        };
    },
    methods: {
        toggleDropdown() {
            this.isDropdownOpen = !this.isDropdownOpen;
        },
        clearFilters() {
            this.filterParams.incoming = "all";
            this.filterParams.search = "";
            this.filterParams.status = "all";
        },
        collapseAll() {
            this.expandedRows = null;
        },
        formatDate(date) {
            const options = { year: "numeric", month: "long", day: "numeric" };
            return new Date(date).toLocaleDateString(undefined, options);
        },
        formatCurrency(value) {
            return currencyConvertor().convertMyCurrency(value);
        },
        getStatusSeverity(status) {
            switch (status) {
                case "pending":
                    return "info";
                case "approved":
                    return "secondary";
                case "paid":
                case "completed":
                case "received":
                    return "success";
                case "dispatched":
                    return "warn";
                case "transit":
                    return "warn";
                case "canceled":
                    return "danger";
                case "return":
                case "returned":
                    return "warn";
                default:
                    return "danger";
            }
        },

        getItemSeverity(status) {
            return this.getStatusSeverity(status);
        },
        exportCSV() {
            this.$refs.dt.exportCSV();
        },
        checkConfirmation(method, methodText) {
            switch (methodText) {
                case "dispatch_all":
                    this.createModalConfimation(
                        "Are you sure you want to dispatch all the items to the recepient?",
                        "Dispatch one Item",
                        method
                    );
                    break;
                case "dispatch_one":
                    this.createModalConfimation(
                        "Are you sure you want to dispatch this items to the recepient?",
                        "Dispatch one Item",
                        method
                    );

                    break;
                default:
                    break;
            }
        },
        createModalConfimation(message, title, method) {
            this.confirmation.isOpen = true;
            this.confirmation.message = message;
            this.confirmation.title = title;
            this.confirmation.method = method;
        },
        confirmAction() {
            if (this.confirmation.method) {
                this.confirmation.method();
            }
            this.confirmation.isOpen = false;
        },
        cancelMakingRequest() {
            this.confirmation.isOpen = false;
            this.confirmation.message = "";
            this.confirmation.title = "";
            this.confirmation.method = null;
        },
        openModal(component) {
            this.modal.open = true;
            this.modal.component = component;
        },
        closeModal() {
            this.modal.open = false;
            this.modal.component = "";
        },
        selectTransaction(data) {
            this.selectedTransactionData = data;
            this.selectedTransactionData.items = data.items?.map((item) => ({
                ...item,
                quantity_ship: item.quantity,
            }));
        },
        dispatchAll(params) {
            this.dispactItems(params);
        },
        receiveAll(params) {
            this.receiveItems(params);
        },
        getParams() {
            let params = {
                transaction_id: "",
                transaction_type: "",
                items: [],
            };
            params.transaction_id = this.selectedTransactionData.id;
            params.transaction_type = this.selectedTransactionData.type;
            params.items = this.selectedTransactionData.items.map((item) => ({
                item_id: item.item.id,
                quantity: item.quantity,
                quantity_ship: item.quantity_ship,
            }));
            return params;
        },
        returnAll() {
            this.createModalConfimation(
                "Are you sure you want to return all the items?",
                "Return all the items",
                () => {
                    this.transactionStore.returnItems(this.getParams());
                }
            );

            this.closeModal();
        },
        rejectAll() {
            this.createModalConfimation(
                "Are you sure you want to reject all the items?",
                "Reject all the items",
                () => {
                    this.transactionStore.rejectItems(this.getParams());
                }
            );

            this.closeModal();
        },
    },
};
</script>

<style></style>
