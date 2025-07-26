<script>
import Modal from "@/Components/Modal.vue";
import SearchInput from "@/Components/SearchInput.vue";
import NewResource from "./Create.vue";
import NewCategory from "../Category/Create.vue";
import { useResourceCategoryStore } from "@/Store/ResourceCategory";
import { useResourceStore } from "@/Store/Resource";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import { onMounted, ref } from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import NoRecords from "@/Components/NoRecords.vue";
import TableDisplay from "@/Layouts/TableDisplay.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import Menu from "primevue/menu";
import Paginator from "primevue/paginator";
import { currencyConvertor } from "@/Store/CurrencyConvertStore";
import Drawer from "primevue/drawer";
import Toolbar from "primevue/toolbar";
import InputText from "primevue/inputtext";
import Dropdown from "primevue/dropdown";
import Tag from "primevue/tag";
import Badge from "primevue/badge";

export default {
    setup() {
        const closeModal = () => {
            modal.value.open = false;
            modal.value.component = "";
        };
        const closeDrawer = () => {
            drawer.value.open = false;
            drawer.value.component = "";
        };
        const category = useResourceCategoryStore();

        const resources = useResourceStore();
        resources.fetchResources();

        const addResource = async (item) => {
            await resources.addResource(item);
            if (!resources.error) {
                closeDrawer();
            }
        };

        const modal = ref({
            open: false,
            component: "",
        });

        const drawer = ref({
            open: false,
            component: "",
        });
        const selectedResource = ref(null);
        const openResorceForm = (component) => {
            drawer.value.open = true;
            drawer.value.component = component;
        };
        const openNewCategoryForm = () => {
            modal.value.open = true;
            modal.value.component = "NewCategory";
        };

        const makeQuery = async (query) => {
            await resources.fetchResources(query);
        };

        const updateResource = async (resource) => {
            await resources.updateResource(resource);
            if (!resources.error) {
                closeDrawer();
            }
        };

        const deleteResource = async (id) => {
            await resources.deleteResource(id);
        };

        return {
            category,
            resources,
            addResource,
            modal,
            drawer,
            openResorceForm,
            openNewCategoryForm,
            closeModal,
            makeQuery,
            updateResource,
            deleteResource,
        };
    },
    data() {
        return {
            notification: {
                open: false,
                message: "",
                status: "",
            },
            query: {
                search: null,
                category: null,
                page: 1,
                rows: 20,
            },
            edit_form: {},
            confirmBox: {
                open: false,
                message: "Are you sure you want to proceed?",
                title: "Confirm Action",
            },
            item_to_delete: {},
            isDropdownOpen: false,
            selectedItem: null,
            table_header: [
                "#",
                "Item Name",
                "Category",
                "Quantity",
                "Unit",
                "Price",
                "Date Added",
                "Actions",
            ],
            items: [
                {
                    label: 'View',
                    icon: 'pi pi-eye',
                    command: () => {
                        this.viewItem(this.selectedItem.id);
                    }
                },
                {
                    label: 'Edit',
                    icon: 'pi pi-pencil',
                    command: () => {
                        this.editResource(this.selectedItem);
                    }
                }, {
                    label: 'Delete',
                    icon: 'pi pi-trash',
                    command: () => {
                        this.handleResourceDelete(this.selectedItem);
                    }
                }
            ]
        };
    },
    methods: {
        openConfirm(message, title) {
            this.confirmBox.open = true;
            this.confirmBox.title = title;
            this.confirmBox.message = message;
        },
        closeConfirm() {
            this.confirmBox.open = false;
            this.confirmBox.message = "Are you sure you want to proceed?";
            this.confirmBox.title = "Confirm Action";
        },
        handleCofirm() {
            this.deleteResource(this.item_to_delete.id);
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString();
        },

        async newCategory(data) {
            this.hideNotification();
            const categoryStore = useResourceCategoryStore();
            try {
                await categoryStore.addItem(data);

                if (categoryStore.error) {
                    this.displayNotification(categoryStore.error, "error");
                } else {
                    this.displayNotification(categoryStore.success, "success");
                    this.closeModal();
                }
            } catch (error) {
                this.displayNotification(categoryStore.error, "error");
            }
        },
        displayNotification(message, status) {
            this.notification.open = true;
            this.notification.message = message;
            this.notification.status = status;
        },
        hideNotification() {
            this.notification.open = false;
            this.notification.message = "";
        },
        makeSearch(search) {
            this.query.search = search;
            this.makeQuery(this.query);
        },
        editResource(data) {
            this.edit_form = { ...data };
            this.openResorceForm("UpdateResource");
        },
        handleResourceDelete(data) {
            this.openConfirm(
                `Are you sure you want to delete item ${data.item_name}? This process cannot be undone`,
                "Confirm Item Delete"
            );
            this.item_to_delete = data;
        },
        filterByCategory() {
            this.makeQuery(this.query);
            this.isDropdownOpen = false;
        },
        toggleDropdown() {
            this.isDropdownOpen = !this.isDropdownOpen;
        },
        closeDropdown() {
            this.isDropdownOpen = false;
        },
        clearFilters() {
            this.query.category = "";
            this.isDropdownOpen = false;
            this.makeQuery(this.query);
        },
        viewItem(id) {
            const url = `/inventory/resources/${id}`;
            window.open(url, "_self");
        },
        toggleActionMenu(item, event) {
            this.selectedItem = item;
            this.$refs.menu.toggle(event);
        },
        exportCSV() {
            this.$refs.dt.exportCSV();
        },
        onPageChange(event) {
            if (this.query.page != event.page + 1) {
                this.query.page = event.page + 1;
                this.makeQuery(this.query);
            }
        },
        onRowChange(row) {
            this.query.rows = row;
            this.makeQuery(this.query);
        },
        convertCurrency(currency) {
            return currencyConvertor().convertMyCurrency(currency);
        }, toggle(event, data) {
            this.selectedItem = data;
            this.$refs.menu.toggle(event);
        }
    },
    components: {
        SearchInput,
        Modal,
        NewResource,
        NewCategory,
        TableSkeleton,
        ConfirmationModal,
        NoRecords,
        TableDisplay,
        DataTable,
        Column,
        Button,
        Menu,
        Paginator,
        Drawer,
        Toolbar,
        InputText,
        Dropdown,
        Tag,
        Badge,
    }
};
</script>

<template>
    <div class="">
        <AlertNotification :open="notification.open" :message="notification.message" :status="notification.status" />
        <AlertNotification :open="resources.success != null || resources.error != null" :message="
                resources.success != null
                    ? resources.success
                    : '' || resources.error != null
                    ? resources.error
                    : ''
            " :status="resources.success ? 'success' : 'error'" />
        <ConfirmationModal :isOpen="confirmBox.open" :message="confirmBox.message" :title="confirmBox.title"
            @confirm="handleCofirm" @close="closeConfirm" />

        <div class="card">
            <Toolbar class="mb-4">
                <template #start>
                    <div class="flex flex-wrap gap-2">
                        <Button label="Add" severity="secondary" size="small" icon="pi pi-plus"
                            @click="() => openResorceForm('NewResource')" />
                        <Button label="Category" icon="pi pi-folder-plus" size="small" @click="openNewCategoryForm"
                            severity="secondary" />
                    </div>
                </template>

                <template #end>
                    <div class="flex flex-wrap gap-2">
                        <div class="relative">
                            <Button icon="pi pi-filter" size="small" label="Filters" @click="toggleDropdown" outlined
                                :class="{ 'p-button-info': isDropdownOpen }" />

                            <div v-if="isDropdownOpen"
                                class="absolute right-0 top-full mt-2 bg-slate-50 dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 p-4 z-50 w-80">
                                <div class="flex justify-between items-center mb-3">
                                    <h3 class="font-medium text-slate-900 dark:text-white">Filters</h3>
                                    <Button icon="pi pi-times" @click.stop="toggleDropdown" text />
                                </div>

                                <div class="space-y-4">
                                    <Dropdown v-model="query.category" @change="filterByCategory"
                                            :options="category.items.data" optionLabel="name" optionValue="id"
                                            placeholder="Select Category" class="w-full" />

                                    <div
                                        class="flex justify-between pt-2 border-t border-slate-200 dark:border-slate-700">
                                        <Button @click.stop="clearFilters" icon="pi pi-filter-slash"
                                            label="Clear Filters" text />
                                        <Button @click.stop="toggleDropdown" label="Apply" size="small" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <Button label="Export" size="small" icon="pi pi-download" severity="info"
                            @click="exportCSV($event)" />
                    </div>
                </template>

            </Toolbar>

            <div class="flex justify-between flex-column sm:flex-row mb-4">
                <div class="w-full sm:w-64 mb-3 sm:mb-0">
                    <InputText v-model="query.search" @input="makeSearch" placeholder="Search resources..."
                        class="w-full" />
                </div>

                <div v-if="resources.items?.data?.length > 0" class="flex items-center">
                    <span class="text-sm text-slate-500 dark:text-slate-400">
                        {{ resources.items.data.length }} resources found
                    </span>
                </div>
            </div>

            <NoRecords v-if="!resources.loading && resources.items?.data?.length === 0" message="No resources found."
                class="mt-4" />

            <DataTable :value="resources.items.data" :loading="resources.loading" dataKey="id" responsiveLayout="scroll"
                class="p-datatable-sm" ref="dt">
                <Column header="Item Name" field="item_name" sortable>
                    <template #body="slotProps">
                        <div class="flex items-center">
                            <div class="w-2 h-2 rounded-full bg-rose-500 mr-2"></div>
                            <span class="font-medium">{{ slotProps.data.item_name }}</span>
                        </div>
                    </template>
                </Column>

                <Column header="Category" field="category.name" sortable>
                    <template #body="slotProps">
                        <Tag v-if="slotProps.data.category?.name" :value="slotProps.data.category?.name" severity="info"
                            class="text-xs" />
                        <span v-else>--</span>
                    </template>
                </Column>

                <Column header="Quantity" field="quantity" sortable>
                    <template #body="slotProps">
                        <span class="font-semibold">{{ slotProps.data.quantity }}</span>
                    </template>
                </Column>

                <Column header="Unit" field="unit">
                    <template #body="slotProps">
                        <Badge :value="slotProps.data.unit" severity="secondary" />
                    </template>
                </Column>

                <Column header="Price" field="price" sortable>
                    <template #body="slotProps">
                        <span class="text-slate-700 dark:text-slate-300">{{ convertCurrency(slotProps.data.price)
                            }}</span>
                    </template>
                </Column>

                <Column header="Date Added" field="date_added" sortable>
                    <template #body="slotProps">
                        <span class="text-slate-600 dark:text-slate-400 text-sm">{{
                            formatDate(slotProps.data.date_added) }}</span>
                    </template>
                </Column>

                <Column header="Source" field="source">
                    <template #body="slotProps">
                        <span class="text-slate-600 dark:text-slate-400">{{ slotProps.data.details.source || '--'
                            }}</span>
                    </template>
                </Column>

                <Column header="Actions" :exportable="false">
                    <template #body="slotProps">
                        <Button type="button" severity="secondary" icon="pi pi-ellipsis-v"
                            @click="toggle($event, slotProps.data)" aria-haspopup="true" aria-controls="overlay_menu" />
                        <Menu ref="menu" id="overlay_menu" :model="items" :popup="true" />
                    </template>
                </Column>
            </DataTable>


            <Paginator v-if="resources.items?.data?.length > 0" :rows="resources.items.per_page"
                :totalRecords="resources.items.total"
                :first="(resources.items.current_page - 1) * resources.items.per_page" @page="onPageChange"
                @update:rows="onRowChange" :rowsPerPageOptions="[10, 20, 50]" class="mt-4" />
        </div>

    </div>
    <Modal :show="modal.open" @close="closeModal">
        <NewResource v-if="modal.component === 'NewResource'" @close="closeModal" :category="category"
            @addResource="addResource" :loading="resources.loading" newResource="true" dataEdit="null" />
        <NewResource v-if="modal.component === 'UpdateResource'" @close="closeModal" :category="category"
            @addResource="addResource" :dataEdit="edit_form" newResource="false" @updateResource="updateResource"
            :loading="resources.loading" />

        <NewCategory v-if="modal.component === 'NewCategory'" @close="closeModal" @newCategory="newCategory" />
    </Modal>

    <Drawer v-model:visible="drawer.open" :header="
            drawer.component == 'NewResource'
                ? 'Add New Resource'
                : 'Update Resource'
        " position="right" class="!w-full md:!w-[60rem]">
        <NewResource v-if="drawer.component === 'NewResource'" @close="closeModal" :category="category"
            @addResource="addResource" :loading="resources.loading" newResource="true" dataEdit="null" />
        <NewResource v-if="drawer.component === 'UpdateResource'" @close="closeModal" :category="category"
            @addResource="addResource" :dataEdit="edit_form" newResource="false" @updateResource="updateResource"
            :loading="resources.loading" />
    </Drawer>
</template>

<style scoped></style>
