<template>
    <AuthenticatedLayout>
        <Head title="Warehouse Details" />

        <div>
            <AlertNotification
                :open="error || success"
                :message="error || success"
                :status="error ? 'error' : success ? 'success' : 'error'"
            />

            <!-- Main content -->
            <div class="mb-6">
                <Toolbar class="mb-4">
                    <template #end>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-if="warehouse"
                                icon="pi pi-pencil"
                                label="Edit Warehouse"
                                severity="secondary"
                                size="small"
                                @click="showEditWarehouse = true"
                            />
                        </div>
                    </template>
                </Toolbar>

                <LoadingUI v-if="loading" />
                <div v-else-if="warehouse" class="flex flex-col space-y-6">
                    <!-- Warehouse Details Card -->
                    <Card class="p-2">
                        <template #header>
                            <div
                                class="flex justify-between items-center w-full"
                            >
                                <div>
                                    <h3 class="text-xl font-medium">
                                        Warehouse Details
                                    </h3>
                                    <p
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        General information and location details
                                    </p>
                                </div>
                                <Tag
                                    :severity="
                                        warehouse.status === 'active'
                                            ? 'success'
                                            : 'danger'
                                    "
                                    :value="
                                        warehouse.status === 'active'
                                            ? 'Active'
                                            : 'Inactive'
                                    "
                                />
                            </div>
                        </template>

                        <template #content>
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6"
                            >
                                <div class="col-span-1">
                                    <div
                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        Name
                                    </div>
                                    <div class="mt-1 text-lg font-semibold">
                                        {{ warehouse.name }}
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <div
                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        Code
                                    </div>
                                    <div class="mt-1 text-lg font-semibold">
                                        {{ warehouse.code || "N/A" }}
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <div
                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        Default
                                    </div>
                                    <div class="mt-1">
                                        <Badge
                                            :value="
                                                warehouse.is_default
                                                    ? 'Yes'
                                                    : 'No'
                                            "
                                            :severity="
                                                warehouse.is_default
                                                    ? 'success'
                                                    : 'info'
                                            "
                                        />
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <div
                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        Address
                                    </div>
                                    <div class="mt-1">
                                        {{ warehouse.address || "N/A" }}
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <div
                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        City
                                    </div>
                                    <div class="mt-1">
                                        {{ warehouse.city || "N/A" }}
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <div
                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        State
                                    </div>
                                    <div class="mt-1">
                                        {{ warehouse.state || "N/A" }}
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <div
                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        Postal Code
                                    </div>
                                    <div class="mt-1">
                                        {{ warehouse.postal_code || "N/A" }}
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <div
                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        Country
                                    </div>
                                    <div class="mt-1">
                                        {{ warehouse.country || "N/A" }}
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <div
                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        Contact Person
                                    </div>
                                    <div class="mt-1">
                                        {{ warehouse.contact_person || "N/A" }}
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <div
                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        Contact Phone
                                    </div>
                                    <div class="mt-1">
                                        {{ warehouse.contact_phone || "N/A" }}
                                    </div>
                                </div>
                                <div class="col-span-1">
                                    <div
                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        Contact Email
                                    </div>
                                    <div class="mt-1">
                                        {{ warehouse.contact_email || "N/A" }}
                                    </div>
                                </div>
                                <div class="col-span-3">
                                    <div
                                        class="text-sm font-medium text-slate-500 dark:text-slate-400"
                                    >
                                        Notes
                                    </div>
                                    <div class="mt-1">
                                        {{
                                            warehouse.notes ||
                                            "No notes available"
                                        }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Warehouse Zones Card -->
                    <Card class="p-2">
                        <template #header>
                            <div
                                class="flex justify-between items-center w-full"
                            >
                                <div>
                                    <h3 class="text-xl font-medium">
                                        Warehouse Zones
                                    </h3>
                                    <p
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Designated areas within the warehouse
                                    </p>
                                </div>
                                <Button
                                    icon="pi pi-plus"
                                    label="Add Zone"
                                    severity="primary"
                                    size="small"
                                    @click="showAddZoneDialog = true"
                                />
                            </div>
                        </template>

                        <template #content>
                            <div
                                v-if="
                                    warehouse.zones &&
                                    warehouse.zones.length > 0
                                "
                            >
                                <DataTable
                                    :value="warehouse.zones"
                                    stripedRows
                                    responsiveLayout="scroll"
                                >
                                    <Column field="name" header="Name" />
                                    <Column field="code" header="Code" />
                                    <Column field="zone_type" header="Type">
                                        <template #body="{ data }">
                                            <Tag
                                                :value="
                                                    formatZoneType(
                                                        data.zone_type
                                                    )
                                                "
                                                severity="info"
                                            />
                                        </template>
                                    </Column>
                                    <Column field="status" header="Status">
                                        <template #body="{ data }">
                                            <Tag
                                                :severity="
                                                    data.status === 'active'
                                                        ? 'success'
                                                        : 'warning'
                                                "
                                                :value="
                                                    formatStatus(data.status)
                                                "
                                            />
                                        </template>
                                    </Column>
                                    <Column
                                        field="temperature_controlled"
                                        header="Temp. Controlled"
                                    >
                                        <template #body="{ data }">
                                            <i
                                                v-if="
                                                    data.temperature_controlled
                                                "
                                                class="pi pi-check-circle text-green-500 text-xl"
                                            />
                                            <i
                                                v-else
                                                class="pi pi-times-circle text-gray-400 text-xl"
                                            />
                                        </template>
                                    </Column>
                                    <Column header="Actions">
                                        <template #body="{ data }">
                                            <div class="flex gap-2">
                                                <Button
                                                    icon="pi pi-eye"
                                                    text
                                                    severity="info"
                                                    @click="
                                                        viewZoneDetails(data)
                                                    "
                                                />
                                                <!-- <Button
                                                    icon="pi pi-pencil"
                                                    text
                                                    severity="warning"
                                                    @click="editZone(data)"
                                                />
                                                <Button
                                                    icon="pi pi-trash"
                                                    text
                                                    severity="danger"
                                                    @click="
                                                        confirmDeleteZone(data)
                                                    "
                                                /> -->
                                            </div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </div>
                            <div v-else class="text-center py-6">
                                <i
                                    class="pi pi-sitemap text-4xl text-slate-300 dark:text-slate-600 mb-2"
                                ></i>
                                <p class="text-slate-500 dark:text-slate-400">
                                    No zones created for this warehouse
                                </p>
                                <Button
                                    label="Create New Zone"
                                    icon="pi pi-plus"
                                    @click="showAddZoneDialog = true"
                                    class="mt-3"
                                    size="small"
                                />
                            </div>
                        </template>
                    </Card>

                    <!-- Bin Locations Card -->
                    <Card class="p-2">
                        <template #header>
                            <div
                                class="flex justify-between items-center w-full"
                            >
                                <div>
                                    <h3 class="text-xl font-medium">
                                        Bin Locations
                                    </h3>
                                    <p
                                        class="text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Specific storage locations within the
                                        warehouse
                                    </p>
                                </div>
                                <Button
                                    icon="pi pi-plus"
                                    label="Add Bin Location"
                                    severity="primary"
                                    size="small"
                                    @click="showAddBinDialog = true"
                                />
                            </div>
                        </template>

                        <template #content>
                            <div
                                v-if="
                                    warehouse.binLocations &&
                                    warehouse.binLocations.length > 0
                                "
                            >
                                <DataTable
                                    :value="warehouse.binLocations"
                                    stripedRows
                                    paginator
                                    :rows="10"
                                    :rowsPerPageOptions="[5, 10, 20, 50]"
                                    responsiveLayout="scroll"
                                >
                                    <Column field="name" header="Name" />
                                    <Column field="code" header="Code" />
                                    <Column field="location" header="Location">
                                        <template #body="{ data }">
                                            {{ formatLocation(data) }}
                                        </template>
                                    </Column>
                                    <Column field="capacity" header="Capacity">
                                        <template #body="{ data }">
                                            {{ data.capacity }}
                                            {{ data.capacity_unit }}
                                        </template>
                                    </Column>
                                    <Column field="status" header="Status">
                                        <template #body="{ data }">
                                            <Tag
                                                :severity="
                                                    getBinStatusSeverity(
                                                        data.status
                                                    )
                                                "
                                                :value="
                                                    formatStatus(data.status)
                                                "
                                            />
                                        </template>
                                    </Column>
                                    <Column header="Actions">
                                        <template #body="{ data }">
                                            <div class="flex gap-2">
                                                <Button
                                                    icon="pi pi-eye"
                                                    text
                                                    severity="info"
                                                    @click="
                                                        viewBinDetails(data)
                                                    "
                                                />
                                                <!-- <Button
                                                    icon="pi pi-pencil"
                                                    text
                                                    severity="warning"
                                                    @click="editBin(data)"
                                                />
                                                <Button
                                                    icon="pi pi-trash"
                                                    text
                                                    severity="danger"
                                                    @click="
                                                        confirmDeleteBin(data)
                                                    "
                                                /> -->
                                            </div>
                                        </template>
                                    </Column>
                                </DataTable>
                            </div>
                            <div v-else class="text-center py-6">
                                <i
                                    class="pi pi-box text-4xl text-slate-300 dark:text-slate-600 mb-2"
                                ></i>
                                <p class="text-slate-500 dark:text-slate-400">
                                    No bin locations created for this warehouse
                                </p>
                                <Button
                                    label="Create New Bin Location"
                                    icon="pi pi-plus"
                                    @click="showAddBinDialog = true"
                                    class="mt-3"
                                    size="small"
                                />
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Not found state -->
                <div
                    v-else-if="!loading"
                    class="bg-white dark:bg-slate-800 rounded-lg shadow dark:shadow-slate-700/30 p-6"
                >
                    <div class="text-center">
                        <i
                            class="pi pi-exclamation-circle text-5xl text-slate-400 dark:text-slate-500 mb-4"
                        ></i>
                        <h3 class="text-xl font-medium mb-2">
                            No warehouse found
                        </h3>
                        <p class="text-slate-500 dark:text-slate-400 mb-6">
                            The requested warehouse could not be found.
                        </p>

                        <Button
                            label="Go Back to Warehouses"
                            icon="pi pi-arrow-left"
                            @click="$inertia.visit(route('warehouses.index'))"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Dialogs and Modals -->
        <Dialog
            v-model:visible="showZoneDetails"
            modal
            header="Zone Details"
            :style="{ width: '450px' }"
        >
            <div v-if="selectedZone" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Name
                        </p>
                        <p class="font-medium">{{ selectedZone.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Code
                        </p>
                        <p class="font-medium">
                            {{ selectedZone.code || "N/A" }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Type
                        </p>
                        <Tag
                            :value="formatZoneType(selectedZone.zone_type)"
                            severity="info"
                        />
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Status
                        </p>
                        <Tag
                            :severity="
                                selectedZone.status === 'active'
                                    ? 'success'
                                    : 'warning'
                            "
                            :value="formatStatus(selectedZone.status)"
                        />
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Temperature Controlled
                        </p>
                        <p class="font-medium">
                            {{
                                selectedZone.temperature_controlled
                                    ? "Yes"
                                    : "No"
                            }}
                        </p>
                    </div>
                    <div v-if="selectedZone.temperature_controlled">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Min Temperature
                        </p>
                        <p class="font-medium">
                            {{ selectedZone.min_temperature }}
                            {{ selectedZone.temperature_unit }}
                        </p>
                    </div>
                    <div v-if="selectedZone.temperature_controlled">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Max Temperature
                        </p>
                        <p class="font-medium">
                            {{ selectedZone.max_temperature }}
                            {{ selectedZone.temperature_unit }}
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Description
                    </p>
                    <p class="font-medium">
                        {{
                            selectedZone.description ||
                            "No description available"
                        }}
                    </p>
                </div>
            </div>
            <template #footer>
                <Button
                    label="Close"
                    icon="pi pi-times"
                    @click="showZoneDetails = false"
                    text
                />
            </template>
        </Dialog>

        <Dialog
            v-model:visible="showBinDetails"
            modal
            header="Bin Location Details"
            :style="{ width: '450px' }"
        >
            <div v-if="selectedBin" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Name
                        </p>
                        <p class="font-medium">{{ selectedBin.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Code
                        </p>
                        <p class="font-medium">
                            {{ selectedBin.code || "N/A" }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Aisle
                        </p>
                        <p class="font-medium">
                            {{ selectedBin.aisle || "N/A" }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Rack
                        </p>
                        <p class="font-medium">
                            {{ selectedBin.rack || "N/A" }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Shelf
                        </p>
                        <p class="font-medium">
                            {{ selectedBin.shelf || "N/A" }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Bin
                        </p>
                        <p class="font-medium">
                            {{ selectedBin.bin || "N/A" }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Capacity
                        </p>
                        <p class="font-medium">
                            {{ selectedBin.capacity }}
                            {{ selectedBin.capacity_unit }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Status
                        </p>
                        <Tag
                            :severity="getBinStatusSeverity(selectedBin.status)"
                            :value="formatStatus(selectedBin.status)"
                        />
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Location Type
                        </p>
                        <Tag
                            :value="
                                formatLocationType(selectedBin.location_type)
                            "
                            severity="info"
                        />
                    </div>
                </div>
            </div>
            <template #footer>
                <Button
                    label="Close"
                    icon="pi pi-times"
                    @click="showBinDetails = false"
                    text
                />
            </template>
        </Dialog>

        <!-- Delete Zone Confirmation Dialog -->
        <Dialog
            v-model:visible="showDeleteZoneDialog"
            modal
            header="Confirm Delete"
            :style="{ width: '450px' }"
            :closable="false"
        >
            <div class="flex align-items-center justify-content-center">
                <i
                    class="pi pi-exclamation-triangle mr-3 text-yellow-500"
                    style="font-size: 2rem"
                />
                <span
                    >Are you sure you want to delete the zone
                    <strong>{{ deleteZoneItem?.name }}</strong
                    >?</span
                >
            </div>
            <template #footer>
                <Button
                    label="No"
                    icon="pi pi-times"
                    text
                    @click="showDeleteZoneDialog = false"
                />
                <Button
                    label="Yes"
                    icon="pi pi-check"
                    severity="danger"
                    @click="confirmZoneDelete"
                />
            </template>
        </Dialog>

        <!-- Delete Bin Confirmation Dialog -->
        <Dialog
            v-model:visible="showDeleteBinDialog"
            modal
            header="Confirm Delete"
            :style="{ width: '450px' }"
            :closable="false"
        >
            <div class="flex align-items-center justify-content-center">
                <i
                    class="pi pi-exclamation-triangle mr-3 text-yellow-500"
                    style="font-size: 2rem"
                />
                <span
                    >Are you sure you want to delete the bin location
                    <strong>{{ deleteBinItem?.name }}</strong
                    >?</span
                >
            </div>
            <template #footer>
                <Button
                    label="No"
                    icon="pi pi-times"
                    text
                    @click="showDeleteBinDialog = false"
                />
                <Button
                    label="Yes"
                    icon="pi pi-check"
                    severity="danger"
                    @click="confirmBinDelete"
                />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Head } from "@inertiajs/vue3";
import { useWarehouseStore } from "@/Store/Warehouse";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

import Button from "primevue/button";
import Card from "primevue/card";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Dialog from "primevue/dialog";
import Tag from "primevue/tag";
import Badge from "primevue/badge";
import Toolbar from "primevue/toolbar";
import LoadingUI from "@/Components/LoadingUI.vue";
import AlertNotification from "@/Components/AlertNotification.vue";

const props = defineProps({
    warehouse_id: {
        type: [Number, String],
        required: true,
    },
});

const warehouseStore = useWarehouseStore();
const loading = computed(() => warehouseStore.loading);
const error = computed(() => warehouseStore.error);
const success = ref(null);

const warehouse = computed(() => {
    if (!warehouseStore.singleWarehouse) return null;
    const warehouseData = { ...warehouseStore.singleWarehouse };
    if (warehouseData.bin_locations) {
        warehouseData.binLocations = warehouseData.bin_locations;
    }
    
    return warehouseData;
});

const showZoneDetails = ref(false);
const showBinDetails = ref(false);
const showAddZoneDialog = ref(false);
const showAddBinDialog = ref(false);
const showEditWarehouse = ref(false);
const selectedZone = ref(null);
const selectedBin = ref(null);

const showDeleteZoneDialog = ref(false);
const showDeleteBinDialog = ref(false);
const deleteZoneItem = ref(null);
const deleteBinItem = ref(null);

const fetchWarehouseData = async () => {
    await warehouseStore.getWarehouse(props.warehouse_id);
};

const formatZoneType = (type) => {
    if (!type) return "Standard";

    const typeMap = {
        storage: "Storage",
        picking: "Picking",
        packing: "Packing",
        receiving: "Receiving",
        shipping: "Shipping",
        returns: "Returns",
        quarantine: "Quarantine",
    };

    return typeMap[type] || type.charAt(0).toUpperCase() + type.slice(1);
};

const formatStatus = (status) => {
    if (!status) return "Unknown";
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatLocationType = (type) => {
    if (!type) return "Standard";

    const typeMap = {
        standard: "Standard",
        receiving: "Receiving",
        shipping: "Shipping",
        quarantine: "Quarantine",
        returns: "Returns",
    };

    return typeMap[type] || type.charAt(0).toUpperCase() + type.slice(1);
};

const formatLocation = (bin) => {
    const parts = [];

    if (bin.aisle) parts.push(`Aisle: ${bin.aisle}`);
    if (bin.rack) parts.push(`Rack: ${bin.rack}`);
    if (bin.shelf) parts.push(`Shelf: ${bin.shelf}`);
    if (bin.bin) parts.push(`Bin: ${bin.bin}`);

    return parts.length > 0 ? parts.join(", ") : "No location details";
};

const getBinStatusSeverity = (status) => {
    const severityMap = {
        active: "success",
        inactive: "secondary",
        full: "warning",
        maintenance: "help",
    };

    return severityMap[status] || "info";
};

// Actions
const viewZoneDetails = (zone) => {
    selectedZone.value = zone;
    showZoneDetails.value = true;
};

const viewBinDetails = (bin) => {
    selectedBin.value = bin;
    showBinDetails.value = true;
};

const editZone = (zone) => {
    console.log("Edit zone:", zone);
    // Implementation for zone editing
};

const editBin = (bin) => {
    console.log("Edit bin:", bin);
    // Implementation for bin editing
};

const confirmDeleteZone = (zone) => {
    deleteZoneItem.value = zone;
    showDeleteZoneDialog.value = true;
};

const confirmDeleteBin = (bin) => {
    deleteBinItem.value = bin;
    showDeleteBinDialog.value = true;
};

const confirmZoneDelete = () => {
    console.log("Deleting zone:", deleteZoneItem.value);
    showDeleteZoneDialog.value = false;
    deleteZoneItem.value = null;
};

const confirmBinDelete = () => {
    console.log("Deleting bin:", deleteBinItem.value);
    showDeleteBinDialog.value = false;
    deleteBinItem.value = null;
};

onMounted(() => {
    fetchWarehouseData();
});
</script>
