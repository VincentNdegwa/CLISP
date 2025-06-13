<script>
import { useResourceCategoryStore } from "@/Store/ResourceCategory";
import { useResourceStore } from "@/Store/Resource";
import { onMounted, ref } from "vue";
import { currencyConvertor } from "@/Store/CurrencyConvertStore";
import ModularDataTable from "@/Components/ModularDataTable.vue";
import Modal from "@/Components/Modal.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import NewResource from "./NewResource.vue";
import NewCategory from "./NewCategory.vue";
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
            
            // Table columns configuration
            columns: [
                {
                    header: "Item Name",
                    field: "item_name",
                    sortable: true,
                    template: (value, rowData) => {
                        return `<div class="flex items-center">
                                <div class="w-2 h-2 rounded-full bg-rose-500 mr-2"></div>
                                <span class="font-medium">${value}</span>
                            </div>`;
                    }
                },
                {
                    header: "Category",
                    field: "category.name",
                    sortable: true,
                    format: (value, rowData) => {
                        return value || '--';
                    },
                    template: (value, rowData) => {
                        if (!value) return '<span>--</span>';
                        return `<span class="p-tag p-component p-tag-info text-xs">${value}</span>`;
                    }
                },
                {
                    header: "Quantity",
                    field: "quantity",
                    sortable: true,
                    template: (value) => {
                        return `<span class="font-semibold">${value}</span>`;
                    }
                },
                {
                    header: "Unit",
                    field: "unit",
                    template: (value) => {
                        return `<span class="px-2 py-1 bg-yellow-50 text-xs ring-1 ring-yellow-600/20 ring-inset rounded-md text-yellow-800">${value}</span>`;
                    }
                },
                {
                    header: "Price",
                    field: "price",
                    sortable: true,
                    format: (value, rowData) => this.convertCurrency(value)
                },
                {
                    header: "Date Added",
                    field: "date_added",
                    sortable: true,
                    format: (value) => this.formatDate(value)
                },
                {
                    header: "Source",
                    field: "details.source",
                    format: (value) => value || '--'
                }
            ],
            
            // Toolbar actions
            startActions: [
                {
                    label: "Add",
                    icon: "pi pi-plus",
                    severity: "secondary",
                    size: "small",
                    command: () => this.openResorceForm('NewResource')
                },
                {
                    label: "Category",
                    icon: "pi pi-folder-plus",
                    severity: "secondary",
                    size: "small",
                    command: () => this.openNewCategoryForm()
                }
            ],
            
            // Row actions
            rowActions: [
                {
                    label: 'View',
                    icon: 'pi pi-eye',
                    command: (item) => this.viewItem(item.id)
                },
                {
                    label: 'Edit',
                    icon: 'pi pi-pencil',
                    command: (item) => this.editResource(item)
                },
                {
                    label: 'Delete',
                    icon: 'pi pi-trash',
                    command: (item) => this.handleResourceDelete(item)
                }
            ],
            
            // Filters
            filters: [
                {
                    label: "Category",
                    field: "category",
                    type: "dropdown",
                    options: [],
                    optionLabel: "name",
                    optionValue: "id",
                    placeholder: "Select Category"
                }
            ]
        };
    },
    computed: {
        tableData() {
            return this.resources.items?.data || [];
        },
        totalRecords() {
            return this.resources.items?.total || 0;
        },
        currentPage() {
            return this.resources.items?.current_page || 1;
        },
        rowsPerPage() {
            return this.resources.items?.per_page || 10;
        }
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
        handleSearch(search) {
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
        handleFilterChange(filters) {
            this.query.category = filters.category;
            this.makeQuery(this.query);
        },
        viewItem(id) {
            const url = `/inventory/resources/${id}`;
            window.open(url, "_self");
        },
        exportCSV() {
        },
        handlePageChange(event) {
            if (this.query.page != event.page + 1) {
                this.query.page = event.page + 1;
                this.makeQuery(this.query);
            }
        },
        handleRowsChange(rows) {
            this.query.rows = rows;
            this.makeQuery(this.query);
        },
        convertCurrency(currency) {
            return currencyConvertor().convertMyCurrency(currency);
        }
    },
    mounted() {
        // Update filter options when categories are loaded
        this.$watch(
            () => this.category.items.data,
            (newCategories) => {
                if (newCategories && newCategories.length > 0) {
                    this.filters[0].options = newCategories;
                }
            },
            { immediate: true }
        );
    },
    components: {
        ModularDataTable,
        Modal,
        NewResource,
        NewCategory,
        ConfirmationModal,
        Drawer
    }
};
</script>

<template>
    <div>
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

        <!-- Modular Data Table Component -->
        <ModularDataTable 
            :value="tableData"
            :columns="columns"
            :loading="resources.loading"
            :startActions="startActions"
            :rowActions="rowActions"
            :filters="filters"
            :dataKey="'id'"
            :rows="rowsPerPage"
            :totalRecords="totalRecords"
            :currentPage="currentPage"
            :rowsPerPageOptions="[10, 20, 50]"
            searchPlaceholder="Search resources..."
            emptyMessage="No resources found."
            :exportable="true"
            @search="handleSearch"
            @page-change="handlePageChange"
            @rows-change="handleRowsChange"
            @filter-change="handleFilterChange"
            @export="exportCSV"
        />

        <!-- Modals -->
        <Modal :show="modal.open" @close="closeModal">
            <NewResource v-if="modal.component === 'NewResource'" @close="closeModal" :category="category"
                @addResource="addResource" :loading="resources.loading" newResource="true" dataEdit="null" />
            <NewResource v-if="modal.component === 'UpdateResource'" @close="closeModal" :category="category"
                @addResource="addResource" :dataEdit="edit_form" newResource="false" @updateResource="updateResource"
                :loading="resources.loading" />

            <NewCategory v-if="modal.component === 'NewCategory'" @close="closeModal" @newCategory="newCategory" />
        </Modal>

        <!-- Drawer -->
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
    </div>
</template>

<style scoped></style>
