<script>
import Modal from "@/Components/Modal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useResourceCategoryStore } from "@/Store/ResourceCategory";
import { Head } from "@inertiajs/vue3";
import NewCategory from "./NewCategory.vue";
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
        NewCategory,
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

        const categories = useResourceCategoryStore();
        categories.fetchResourceCategory(params.value);

        const categoryAdd = async (category) => {
            await categories.addItem(category);
            if (categories.success) {
                closeModal();
            }
        };

        const categoryUpdate = async (category) => {
            await categories.updateCategory(category);
            if (categories.success) {
                closeModal();
            }
        };

        const categoryDelete = async (id) => {
            await categories.deleteCategory(id);
            if (categories.success) {
                closeModal();
            }
        };

        const modal = ref({
            open: false,
            component: "",
        });

        const openNewCategoryForm = (component) => {
            modal.value.open = true;
            modal.value.component = component;
        };

        const closeModal = () => {
            modal.value.open = false;
        };

        const paginateCategories = (page) => {
            params.value.page = page.page + 1;
            categories.fetchResourceCategory(params.value);
        };

        const onRowChange = (rows) => {
            params.value.rows = rows;
        };

        return {
            categories,
            modal,
            openNewCategoryForm,
            closeModal,
            categoryAdd,
            categoryUpdate,
            categoryDelete,
            params,
            paginateCategories,
            onRowChange,
        };
    },
    data() {
        return {
            category_data: {},
            confirmBox: {
                open: false,
                message: "Are you sure you want to proceed?",
                title: "Confirm Action",
            },
            category_to_delete: {},
            selectedCategory: null,

            // Table columns configuration 
            columns: [
                { header: "Name", field: "name", sortable: true },
                { header: "Description", field: "description", sortable: false },
            ],
            
            // Start actions 
            startActions: [
                {
                    label: "Add Category",
                    icon: "pi pi-plus",
                    severity: "secondary",
                    size: "small",
                    command: () => this.openNewCategoryForm("NewCategory"),
                },
            ],
            
            // Row actions
            rowActions: [
                {
                    label: "Edit",
                    icon: "pi pi-pencil",
                    command: (data) => this.openEditCategory(data),
                },
                {
                    label: "Delete",
                    icon: "pi pi-trash",
                    command: (data) => this.openConfirm(
                        `Are you sure you want to delete category ${data.name}? This process cannot be undone`,
                        "Confirm Item Delete",
                        data
                    ),
                },
            ],
        };
    },
    computed: {
        tableData() {
            return this.categories.items?.data || [];
        },
        totalRecords() {
            return this.categories.items?.total || 0;
        },
        currentPage() {
            return this.categories.items?.current_page || 1;
        },
        rowsPerPage() {
            return this.params?.rows || 20;
        }
    },
    methods: {
        openEditCategory(data) {
            this.openNewCategoryForm("UpdateCategory");
            this.category_data = data;
        },
        updateCategory(data) {
            this.categoryUpdate(data);
        },
        openConfirm(message, title, data) {
            this.confirmBox.open = true;
            this.confirmBox.title = title;
            this.confirmBox.message = message;
            this.category_to_delete = data;
        },
        closeConfirm() {
            this.confirmBox.open = false;
            this.confirmBox.message = "Are you sure you want to proceed?";
            this.confirmBox.title = "Confirm Action";
        },
        handleCofirm() {
            this.categoryDelete(this.category_to_delete.id);
        },
        handleRowAction({ action, row }) {
            if (action.label === "Edit") {
                this.openEditCategory(row);
            } else if (action.label === "Delete") {
                this.openConfirm(
                    `Are you sure you want to delete category ${row.name}? This process cannot be undone`,
                    "Confirm Item Delete",
                    row
                );
            }
        },
        handleSearch(query) {
            this.params.search = query;
            this.categories.fetchResourceCategory(this.params);
        },
        handlePageChange(event) {
            this.paginateCategories(event);
        },
        handleRowsChange(rows) {
            this.onRowChange(rows);
        }
    },
};
</script>

<template>
    <Head title="Resource Categories" />
    <AuthenticatedLayout>
        <ConfirmationModal
            :isOpen="confirmBox.open"
            :message="confirmBox.message"
            :title="confirmBox.title"
            @confirm="handleCofirm"
            @close="closeConfirm"
        />

        <AlertNotification
            :open="categories.error != null"
            :message="categories.error ? categories.error : ''"
            :status="categories.error ? 'error' : 'success'"
        />

        <AlertNotification
            :open="categories.success != null"
            :message="categories.success ? categories.success : ''"
            status="success"
        />

        <TableSkeleton v-if="categories.loading && !categories.items?.data" />

        <ModularDataTable
            v-else
            :value="tableData"
            :loading="categories.loading"
            dataKey="id"
            :columns="columns"
            :startActions="startActions"
            :rowActions="rowActions"
            searchPlaceholder="Search categories..."
            :rows="rowsPerPage"
            :totalRecords="totalRecords"
            :currentPage="currentPage"
            :rowsPerPageOptions="[10, 20, 50]"
            @page-change="handlePageChange"
            @rows-change="handleRowsChange"
            @row-action="handleRowAction"
            @search="handleSearch"
            emptyMessage="No categories found"
            exportable
            stripedRows
        />

        <Modal :show="modal.open" @close="closeModal">
            <NewCategory
                v-if="modal.component === 'NewCategory'"
                @close="closeModal"
                @newCategory="categoryAdd"
                newCategory="true"
                data="null"
                :loading="categories.loading"
                @updateCategory="updateCategory"
            />
            <NewCategory
                v-if="modal.component === 'UpdateCategory'"
                @close="closeModal"
                @newCategory="categoryAdd"
                newCategory="false"
                :data="category_data"
                :loading="categories.loading"
                @updateCategory="updateCategory"
            />
        </Modal>
    </AuthenticatedLayout>
</template>
