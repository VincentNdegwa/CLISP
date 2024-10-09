<script>
import Modal from "@/Components/Modal.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useResourceCategoryStore } from "@/Store/ResourceCategory";
import { Head } from "@inertiajs/vue3";
import NewCategory from "./NewCategory.vue";
import { ref } from "vue";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import NoRecords from "@/Components/NoRecords.vue";
import Column from "primevue/column";
import Button from "primevue/button";
import DataTable from "primevue/datatable";
import Menu from "primevue/menu";

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Modal,
        NewCategory,
        TableSkeleton,
        ConfirmationModal,
        NoRecords,
        Column,
        Button,
        DataTable,
        Menu,
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
            if (page > 0) {
                params.value.page = page;
                categories.fetchResourceCategory(params.value);
            }
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
        };
    },
    methods: {
        openEditCategory(data) {
            console.log(data);

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
        toggleActionMenu(data, event) {
            event.preventDefault();
            this.selectedCategory = data;
            this.$refs.menu.toggle(event);
        },
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
        <div class="mx-auto p-4 h-fit">
            <div class="w-full mt-2 flex justify-between">
                <h1 class="text-xl font-bold mb-4">Resource Categories</h1>
                <div class="join gap-2">
                    <button
                        @click="() => openNewCategoryForm('NewCategory')"
                        class="btn bg-slate-950 text-white"
                    >
                        Add Categories
                    </button>
                </div>
            </div>

            <TableSkeleton v-if="categories.loading" />
            <NoRecords v-else-if="categories.items?.data?.length == 0" />

            <div v-else class="h-[74vh] overflow-y-scroll relative mt-1">
                <DataTable
                    v-if="categories.items?.data?.length > 0"
                    :value="categories.items.data"
                    dataKey="id"
                    :tableStyle="{ width: '100%' }"
                    class="min-w-full"
                >
                    <!-- Name -->
                    <Column header="Name" field="name" />

                    <!-- Description -->
                    <Column header="Description" field="description" />

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
                                    aria-controls="'action_menu_' + slotProps.data.id"
                                />
                                <Menu
                                    ref="menu"
                                    :id="'action_menu_' + slotProps.data.id"
                                    :model="[
                                        {
                                            label: 'Edit',
                                            icon: 'pi pi-pencil',
                                            command: () =>
                                                openEditCategory(
                                                    selectedCategory
                                                ),
                                        },
                                        {
                                            label: 'Delete',
                                            icon: 'pi pi-trash',
                                            command: () =>
                                                openConfirm(
                                                    `Are you sure you want to delete category ${selectedCategory.name}? This process cannot be undone`,
                                                    'Confirm Item Delete',
                                                    selectedCategory
                                                ),
                                        },
                                    ]"
                                    :popup="true"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>

            <div
                v-if="categories.items?.data?.length > 0"
                class="flex justify-between items-center"
            >
                <button
                    :class="[
                        'py-2 px-4 rounded',
                        categories.items?.prev_page_url
                            ? 'bg-slate-900 text-white'
                            : 'bg-gray-300 text-gray-700 cursor-not-allowed',
                    ]"
                    :disabled="!categories.items?.prev_page_url"
                    @click="paginateCategories(params.page - 1)"
                >
                    Previous
                </button>
                <span>
                    Page {{ categories.items?.current_page }} of
                    {{ categories.items?.last_page }}
                </span>
                <button
                    :class="[
                        'py-2 px-4 rounded',
                        categories.items?.next_page_url
                            ? 'bg-slate-900 text-white'
                            : 'bg-gray-300 text-gray-700 cursor-not-allowed',
                    ]"
                    :disabled="!categories.items?.next_page_url"
                    @click="paginateCategories(params.page + 1)"
                >
                    Next
                </button>
            </div>
        </div>

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
