<script>
import Modal from "@/Components/Modal.vue";
import SearchInput from "@/Components/SearchInput.vue";
import NewResource from "./NewResource.vue";
import NewCategory from "./NewCategory.vue";
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
        },
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
    },
};
</script>

<template>
    <div class="">
        <AlertNotification
            :open="notification.open"
            :message="notification.message"
            :status="notification.status"
        />
        <AlertNotification
            :open="resources.success != null || resources.error != null"
            :message="
                resources.success != null
                    ? resources.success
                    : '' || resources.error != null
                    ? resources.error
                    : ''
            "
            :status="resources.success ? 'success' : 'error'"
        />
        <ConfirmationModal
            :isOpen="confirmBox.open"
            :message="confirmBox.message"
            :title="confirmBox.title"
            @confirm="handleCofirm"
            @close="closeConfirm"
        />

        <div class="mt-1 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <SearchInput @search="makeSearch" />

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
                                <div class="inline-block">
                                    Filter By Category
                                </div>
                                <select
                                    v-model="query.category"
                                    @change="filterByCategory"
                                    class="select select-bordered bg-white text-slate-950 ring-1 ring-slate-800"
                                >
                                    <option
                                        value="Filter By Category"
                                        selected
                                        disabled
                                    >
                                        Filter By Category
                                    </option>
                                    <option
                                        v-for="cat in category.items.data"
                                        :key="cat.id"
                                        :value="cat.id"
                                    >
                                        {{ cat.name }}
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
            </div>

            <div class="join gap-2">
                <button
                    class="btn join-item text-white"
                    @click="() => openResorceForm('NewResource')"
                >
                    Add Resources
                </button>

                <button
                    @click="openNewCategoryForm"
                    class="btn bg-slate-950 text-white"
                >
                    Add Categories
                </button>
                <Button
                    icon="pi pi-external-link"
                    label="Export"
                    @click="exportCSV($event)"
                />
            </div>
        </div>
        <div
            v-if="resources.items?.data?.length > 0"
            class="h-[75vh] overflow-y-scroll relative mt-1"
        >
            <DataTable
                :value="resources.items.data"
                :loading="resources.loading"
                dataKey="id"
                tableStyle="width:100%"
                ref="dt"
            >
                <!-- Item Name -->
                <Column header="Item Name" field="item_name" />

                <!-- Category -->
                <Column header="Category" field="category.name">
                    <template #body="slotProps">
                        {{ slotProps.data.category?.name || "--" }}
                    </template>
                </Column>

                <!-- Quantity -->
                <Column header="Quantity" field="quantity" />

                <!-- Unit -->
                <Column header="Unit" field="unit" />

                <!-- Price -->
                <Column header="Price" field="price">
                    <template #body="slotProps">
                        {{ convertCurrency(slotProps.data.price) }}
                    </template>
                </Column>

                <!-- Date Added -->
                <Column header="Date Added" field="date_added">
                    <template #body="slotProps">
                        {{ formatDate(slotProps.data.date_added) }}
                    </template>
                </Column>

                <Column header="Source" field="source">
                    <template #body="slotProps">
                        {{ slotProps.data.details.source|| '--' }}
                    </template>
                </Column>

                <!-- Actions -->
                <Column header="Actions">
                    <template #body="slotProps">
                        <div class="card flex justify-center">
                            <Button
                                type="button"
                                icon="pi pi-ellipsis-v"
                                @click="
                                    toggleActionMenu(slotProps.data, $event)
                                "
                                aria-haspopup="true"
                                severity="contrast"
                                size="small"
                                aria-controls="action_menu"
                            />
                            <Menu
                                ref="menu"
                                :id="'action_menu_' + slotProps.data.id"
                                :model="[
                                    {
                                        label: 'View',
                                        icon: 'pi pi-arrow-up-right',
                                        command: () =>
                                            viewItem(selectedItem?.id),
                                    },
                                    {
                                        label: 'Edit',
                                        icon: 'pi pi-pencil',

                                        command: () =>
                                            editResource(selectedItem),
                                    },
                                    {
                                        label: 'Delete',
                                        icon: 'pi pi-trash',

                                        command: () =>
                                            handleResourceDelete(selectedItem),
                                    },
                                ]"
                                :popup="true"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <NoRecords v-else />

        <div v-if="resources.items?.data?.length > 0">
            <Paginator
                :totalRecords="resources.items?.total"
                :rows="resources.items?.per_page"
                :first="
                    (resources.items?.current_page - 1) *
                    resources.items?.per_page
                "
                @page="onPageChange"
                @update:rows="onRowChange"
                :rowsPerPageOptions="[10, 20, 50]"
            >
            </Paginator>
        </div>
    </div>
    <Modal :show="modal.open" @close="closeModal">
        <NewResource
            v-if="modal.component === 'NewResource'"
            @close="closeModal"
            :category="category"
            @addResource="addResource"
            :loading="resources.loading"
            newResource="true"
            dataEdit="null"
        />
        <NewResource
            v-if="modal.component === 'UpdateResource'"
            @close="closeModal"
            :category="category"
            @addResource="addResource"
            :dataEdit="edit_form"
            newResource="false"
            @updateResource="updateResource"
            :loading="resources.loading"
        />

        <NewCategory
            v-if="modal.component === 'NewCategory'"
            @close="closeModal"
            @newCategory="newCategory"
        />
    </Modal>

    <Drawer
        v-model:visible="drawer.open"
        :header="
            drawer.component == 'NewResource'
                ? 'Add New Resource'
                : 'Update Resource'
        "
        position="right"
        class="!w-full md:!w-[60rem]"
    >
        <NewResource
            v-if="drawer.component === 'NewResource'"
            @close="closeModal"
            :category="category"
            @addResource="addResource"
            :loading="resources.loading"
            newResource="true"
            dataEdit="null"
        />
        <NewResource
            v-if="drawer.component === 'UpdateResource'"
            @close="closeModal"
            :category="category"
            @addResource="addResource"
            :dataEdit="edit_form"
            newResource="false"
            @updateResource="updateResource"
            :loading="resources.loading"
        />
    </Drawer>
</template>

<style scoped></style>
