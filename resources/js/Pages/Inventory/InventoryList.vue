<script>
import Modal from "@/Components/Modal.vue";
import SearchInput from "@/Components/SearchInput.vue";
import NewResource from "./NewResource.vue";
import NewCategory from "./NewCategory.vue";
import { useResourceCategoryStore } from "@/Store/ResourceCategory";
import { useResourceStore } from "@/Store/Resource";
import TableSkeleton from "@/Components/TableSkeleton.vue";
import { ref } from "vue";

export default {
    setup() {
        const closeModal = () => {
            modal.value.open = false;
            modal.value.component = "";
        };
        const category = useResourceCategoryStore();
        category.fetchResourceCategory();

        const resources = useResourceStore();
        resources.fetchResources();

        const addResource = async (item) => {
            await resources.addResource(item);
            closeModal();
        };

        const modal = ref({
            open: false,
            component: "",
        });
        const openNewResorceForm = () => {
            modal.value.open = true;
            modal.value.component = "NewResource";
        };
        const openNewCategoryForm = () => {
            modal.value.open = true;
            modal.value.component = "NewCategory";
        };

        return {
            category,
            resources,
            addResource,
            modal,
            openNewResorceForm,
            openNewCategoryForm,
            closeModal,
        };
    },
    data() {
        return {
            notification: {
                open: false,
                message: "",
                status: "",
            },
        };
    },
    methods: {
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
    },
    components: {
        SearchInput,
        Modal,
        NewResource,
        NewCategory,
        TableSkeleton,
    },
};
</script>

<template>
    <div class="h-[83vh]">
        <AlertNotification
            :open="notification.open"
            :message="notification.message"
            :status="notification.status"
        />
        <AlertNotification
            :open="resources.success || resources.error"
            :message="resources.success || resources.error"
            :status="resources.success ? 'success' : 'error'"
        />

        <div class="w-full mt-2 flex justify-between">
            <SearchInput />
            <div class="join gap-2">
                <!-- <button class="btn join-item text-white">
                    <i class="bi bi-funnel"></i> Filter
                </button> -->
                <button
                    class="btn join-item text-white"
                    @click="openNewResorceForm"
                >
                    Add Resources
                </button>

                <button
                    @click="openNewCategoryForm"
                    class="btn bg-slate-950 text-white"
                >
                    Add Categories
                </button>
            </div>
        </div>
        <div class="overflow-x-auto h-[75vh] w-full">
            <TableSkeleton v-if="resources.loading" />
            <table v-else class="table w-full">
                <thead>
                    <tr>
                        <th class="text-slate-900 font-medium">#</th>
                        <th class="text-slate-900 font-medium">Item Name</th>
                        <th class="text-slate-900 font-medium">Category</th>
                        <th class="text-slate-900 font-medium">Quantity</th>
                        <th class="text-slate-900 font-medium">Unit</th>
                        <th class="text-slate-900 font-medium">Price</th>
                        <th class="text-slate-900 font-medium">Date Added</th>
                        <th class="text-slate-900 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, index) in resources.items.data"
                        :key="item.item_id"
                    >
                        <td>{{ index + 1 }}</td>
                        <td>{{ item.item_name }}</td>
                        <td>{{ item.category.name }}</td>
                        <td>{{ item.quantity }}</td>
                        <td>{{ item.unit }}</td>
                        <td>{{ item.price }}</td>
                        <td>{{ formatDate(item.date_added) }}</td>
                        <td>
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
                                    <li><a>VIew</a></li>
                                    <li><a>Edit</a></li>
                                    <li><a>Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center">
            <button
                :class="[
                    'py-2 px-4 rounded',
                    !resources.items.prev_page_url
                        ? 'bg-gray-300 text-gray-700 cursor-not-allowed'
                        : 'bg-slate-900 text-white',
                ]"
                :disabled="resources.items.prev_page_url == null"
                @click="fetchCategories(resources.items.current_page - 1)"
            >
                Previous
            </button>
            <span
                >Page {{ resources.items.current_page }} of
                {{ resources.items.last_page }}</span
            >
            <button
                :class="[
                    'py-2 px-4 rounded',
                    !resources.items.prev_page_url
                        ? 'bg-gray-300 text-gray-700 cursor-not-allowed'
                        : 'bg-slate-900 text-white',
                ]"
                :disabled="resources.items.next_page_url == null"
                @click="fetchCategories(resources.items.current_page + 1)"
            >
                Next
            </button>
        </div>
    </div>
    <Modal :show="modal.open" @close="closeModal">
        <NewResource
            v-if="modal.component === 'NewResource'"
            @close="closeModal"
            :category="category"
            @addResource="addResource"
        />

        <NewCategory
            v-if="modal.component === 'NewCategory'"
            @close="closeModal"
            @newCategory="newCategory"
        />
    </Modal>
</template>

<style scoped></style>
