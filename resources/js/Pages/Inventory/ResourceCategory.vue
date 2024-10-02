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

export default {
    components: {
        AuthenticatedLayout,
        Head,
        Modal,
        NewCategory,
        TableSkeleton,
        ConfirmationModal,
        NoRecords,
    },
    setup() {
        const categories = useResourceCategoryStore();
        categories.fetchResourceCategory();
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

        return {
            categories,
            modal,
            openNewCategoryForm,
            closeModal,
            categoryAdd,
            categoryUpdate,
            categoryDelete,
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
                <table class="min-w-full bg-white relative table">
                    <thead
                        class="sticky top-0 bg-gray-200 z-[2] font-bold text-slate-950"
                    >
                        <tr>
                            <th class="py-2 px-4 border">#</th>
                            <th class="py-2 px-4 border">Name</th>
                            <th class="py-2 px-4 border">Description</th>
                            <th class="py-2 px-4 border">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="">
                        <tr
                            v-for="(item, index) in categories.items?.data"
                            :key="index"
                            class="hover:bg-gray-100 transition-colors"
                        >
                            <td class="py-2 px-4 border-b">{{ item.id }}</td>
                            <td class="py-2 px-4 border-b">{{ item.name }}</td>
                            <td class="py-2 px-4 border-b">
                                {{ item.description }}
                            </td>
                            <td class="py-2 px-4 border-b">
                                <div class="dropdown dropdown-left">
                                    <div
                                        tabindex="0"
                                        class="btn btn-xs bg-blue-500 text-white"
                                    >
                                        Action
                                    </div>
                                    <ul
                                        tabindex="0"
                                        class="dropdown-content menu bg-white rounded-box z-[1] w-52 p-2 shadow"
                                    >
                                        <li @click="openEditCategory(item)">
                                            <a>Edit</a>
                                        </li>
                                        <li
                                            @click="
                                                () =>
                                                    openConfirm(
                                                        `Are you sure you want to delete category ${item.name}? This process cannot be undone`,
                                                        'Confirm Item Delete',
                                                        item
                                                    )
                                            "
                                        >
                                            <a>Delete</a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="categories.items?.data?.length > 0"
                class="flex justify-between items-center"
            >
                <button
                    :class="[
                        'py-2 px-4 rounded',
                        !categories.items?.prev_page_url
                            ? 'bg-gray-300 text-gray-700 cursor-not-allowed'
                            : 'bg-slate-900 text-white',
                    ]"
                    :disabled="categories?.items?.prev_page_url == null"
                    @click="fetchCategories(categories.items?.current_page - 1)"
                >
                    Previous
                </button>
                <span
                    >Page {{ categories.items?.current_page }} of
                    {{ categories.items?.last_page }}</span
                >
                <button
                    :class="[
                        'py-2 px-4 rounded',
                        !categories.items?.prev_page_url
                            ? 'bg-gray-300 text-gray-700 cursor-not-allowed'
                            : 'bg-slate-900 text-white',
                    ]"
                    :disabled="categories.items?.next_page_url == null"
                    @click="fetchCategories(categories.items?.current_page + 1)"
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
