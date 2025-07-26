<script>
import Modal from "@/Components/Modal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useShipmentStore } from "@/Store/Shipment";
import { Head } from "@inertiajs/vue3";
import NewShipment from "./NewShipment.vue";
import { ref, computed } from "vue";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import AlertNotification from "@/Components/AlertNotification.vue";
import ModularDataTable from "@/Components/ModularDataTable.vue";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Modal,
        NewShipment,
        TableSkeleton,
        ConfirmationModal,
        AlertNotification,
        ModularDataTable,
    },
    setup() {
        const params = ref({
            page: 1,
            rows: 20,
        });

        const shipments = useShipmentStore();
        shipments.fetchShipments(params.value);

        const shipmentAdd = async (shipment) => {
            await shipments.addShipment(shipment);
            if (shipments.success) {
                closeModal();
            }
        };

        const shipmentUpdate = async (shipment) => {
            await shipments.updateShipment(shipment);
            if (shipments.success) {
                closeModal();
            }
        };

        const shipmentDelete = async (id) => {
            await shipments.deleteShipment(id);
            if (shipments.success) {
                closeModal();
            }
        };

        const modal = ref({
            open: false,
            component: "",
        });

        const openNewShipmentForm = (component) => {
            modal.value.open = true;
            modal.value.component = component;
        };

        const closeModal = () => {
            modal.value.open = false;
        };

        const paginateShipments = (page) => {
            params.value.page = page.page + 1;
            shipments.fetchShipments(params.value);
        };

        const onRowChange = (rows) => {
            params.value.rows = rows;
        };

        return {
            shipments,
            modal,
            openNewShipmentForm,
            closeModal,
            shipmentAdd,
            shipmentUpdate,
            shipmentDelete,
            params,
            paginateShipments,
            onRowChange,
        };
    },
    data() {
        return {
            shipment_data: {},
            confirmBox: {
                open: false,
                message: "Are you sure you want to proceed?",
                title: "Confirm Action",
            },
            shipment_to_delete: {},
            selectedShipment: null,

            // Table columns configuration 
            columns: [
                { 
                    header: "Shipment #", 
                    field: "shipment_number", 
                    sortable: true 
                },
                { 
                    header: "Order #", 
                    field: "sales_order.order_number", 
                    sortable: true 
                },
                { 
                    header: "Date", 
                    field: "shipment_date", 
                    sortable: true,
                    format: (value) => {
                        return new Date(value).toLocaleDateString();
                    }
                },
                { 
                    header: "Tracking #", 
                    field: "tracking_number", 
                    sortable: false 
                },
                { 
                    header: "Carrier", 
                    field: "carrier.name", 
                    sortable: true 
                },
                { 
                    header: "Status", 
                    field: "status", 
                    sortable: true,
                    template: (value, row) => {
                        const statusText = row.status_text;
                        const statusClass = row.status_class;
                        return `<span class="px-2 py-1 text-xs font-medium rounded-md ${statusClass}">${statusText}</span>`;
                    }
                },
            ],
            
            // Start actions 
            startActions: [
                {
                    label: "New Shipment",
                    icon: "pi pi-plus",
                    severity: "secondary",
                    size: "small",
                    command: () => this.openNewShipmentForm("NewShipment"),
                },
            ],
            
            // Filters
            filters: [
                {
                    label: "Status",
                    field: "status",
                    type: "dropdown",
                    options: [
                        { label: "All", value: null },
                        { label: "Pending", value: 0 },
                        { label: "Processing", value: 1 },
                        { label: "Shipped", value: 2 },
                        { label: "Delivered", value: 3 },
                        { label: "Returned", value: 4 },
                    ],
                },
                {
                    label: "Date From",
                    field: "date_from",
                    type: "date",
                },
                {
                    label: "Date To",
                    field: "date_to",
                    type: "date",
                },
            ],
            
            // Row actions
            rowActions: [
                {
                    label: "View",
                    icon: "pi pi-eye",
                    command: (data) => this.viewShipment(data),
                },
                {
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: (data) => this.openEditShipment(data),
                },
                {
                    label: "Mark as Shipped",
                    icon: "pi pi-send",
                    command: (data) => this.markAsShipped(data),
                    visible: (data) => data.status === 0 || data.status === 1, // Only for pending or processing
                },
                {
                    label: "Mark as Delivered",
                    icon: "pi pi-check-circle",
                    command: (data) => this.markAsDelivered(data),
                    visible: (data) => data.status === 2, // Only for shipped
                },
                {
                    label: "Delete",
                    icon: "pi pi-trash",
                    command: (data) => this.openConfirm(
                        `Are you sure you want to delete shipment ${data.shipment_number}? This process cannot be undone`,
                        "Confirm Shipment Delete",
                        data
                    ),
                    visible: (data) => data.status === 0, // Only for pending
                },
            ],
        };
    },
    computed: {
        tableData() {
            return this.shipments.items?.data || [];
        },
        totalRecords() {
            return this.shipments.items?.total || 0;
        },
        currentPage() {
            return this.shipments.items?.current_page || 1;
        },
        rowsPerPage() {
            return this.params?.rows || 20;
        }
    },
    methods: {
        viewShipment(data) {
            // Navigate to shipment details page
            this.$inertia.visit(`/shipments/${data.id}`);
        },
        openEditShipment(data) {
            this.openNewShipmentForm("UpdateShipment");
            this.shipment_data = data;
        },
        updateShipment(data) {
            this.shipmentUpdate(data);
        },
        openConfirm(message, title, data) {
            this.confirmBox.open = true;
            this.confirmBox.title = title;
            this.confirmBox.message = message;
            this.shipment_to_delete = data;
        },
        closeConfirm() {
            this.confirmBox.open = false;
            this.confirmBox.message = "Are you sure you want to proceed?";
            this.confirmBox.title = "Confirm Action";
        },
        handleConfirm() {
            this.shipmentDelete(this.shipment_to_delete.id);
        },
        markAsShipped(data) {
            this.shipments.markAsShipped(data.id);
        },
        markAsDelivered(data) {
            this.shipments.markAsDelivered(data.id);
        },
        handleRowAction({ action, row }) {
            if (action.label === "View") {
                this.viewShipment(row);
            } else if (action.label === "Edit") {
                this.openEditShipment(row);
            } else if (action.label === "Mark as Shipped") {
                this.markAsShipped(row);
            } else if (action.label === "Mark as Delivered") {
                this.markAsDelivered(row);
            } else if (action.label === "Delete") {
                this.openConfirm(
                    `Are you sure you want to delete shipment ${row.shipment_number}? This process cannot be undone`,
                    "Confirm Shipment Delete",
                    row
                );
            }
        },
        handleSearch(query) {
            this.params.search = query;
            this.shipments.fetchShipments(this.params);
        },
        handleFilterChange(filters) {
            this.params = { ...this.params, ...filters };
            this.shipments.fetchShipments(this.params);
        },
        handlePageChange(event) {
            this.paginateShipments(event);
        },
        handleRowsChange(rows) {
            this.onRowChange(rows);
        }
    },
};
</script>

<template>
    <Head title="Shipments" />
    <AuthenticatedLayout>
        <ConfirmationModal
            :isOpen="confirmBox.open"
            :message="confirmBox.message"
            :title="confirmBox.title"
            @confirm="handleConfirm"
            @close="closeConfirm"
        />

        <AlertNotification
            :open="shipments.error != null"
            :message="shipments.error ? shipments.error : ''"
            :status="shipments.error ? 'error' : 'success'"
        />

        <AlertNotification
            :open="shipments.success != null"
            :message="shipments.success ? shipments.success : ''"
            status="success"
        />

        <TableSkeleton v-if="shipments.loading && !shipments.items?.data" />

        <ModularDataTable
            v-else
            :value="tableData"
            :loading="shipments.loading"
            dataKey="id"
            :columns="columns"
            :startActions="startActions"
            :rowActions="rowActions"
            :filters="filters"
            searchPlaceholder="Search shipments..."
            :rows="rowsPerPage"
            :totalRecords="totalRecords"
            :currentPage="currentPage"
            :rowsPerPageOptions="[10, 20, 50]"
            @page-change="handlePageChange"
            @rows-change="handleRowsChange"
            @row-action="handleRowAction"
            @search="handleSearch"
            @filter-change="handleFilterChange"
            emptyMessage="No shipments found"
            exportable
            stripedRows
            toolbarTitle="Shipments"
        />

        <Modal :show="modal.open" @close="closeModal">
            <NewShipment
                v-if="modal.component === 'NewShipment'"
                @close="closeModal"
                @newShipment="shipmentAdd"
                newShipment="true"
                data="null"
                :loading="shipments.loading"
            />
            <NewShipment
                v-if="modal.component === 'UpdateShipment'"
                @close="closeModal"
                @newShipment="shipmentAdd"
                newShipment="false"
                :data="shipment_data"
                :loading="shipments.loading"
                @updateShipment="updateShipment"
            />
        </Modal>
    </AuthenticatedLayout>
</template>
