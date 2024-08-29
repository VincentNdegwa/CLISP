<script>
import Modal from "@/Components/Modal.vue";
import SearchInput from "@/Components/SearchInput.vue";
import NewResource from "./NewResource.vue";
import NewCategory from "./NewCategory.vue";
import { useResourceCategoryStore } from "@/Store/ResourceCategory";

export default {
    setup() {
        const category = useResourceCategoryStore();
        category.fetchResourceCategory();

        return {
            category,
        };
    },
    data() {
        return {
            inventoryItems: [
                {
                    item_id: 1,
                    item_name: "Laptop",
                    category: "Electronics",
                    quantity: 10,
                    unit: "Pieces",
                    price: 1000.0,
                    date_added: "2024-08-27",
                },
                {
                    item_id: 2,
                    item_name: "Office Chair",
                    category: "Furniture",
                    quantity: 5,
                    unit: "Pieces",
                    price: 150.0,
                    date_added: "2024-08-25",
                },
            ],
            modal: {
                open: false,
                component: "",
            },
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
        openNewResorceForm() {
            this.modal.open = true;
            this.modal.component = "NewResource";
        },
        openNewCategoryForm() {
            this.modal.open = true;
            this.modal.component = "NewCategory";
        },
        closeModal() {
            this.modal.open = false;
        },
        async newCategory(data) {
            this.hideNotification();
            const categoryStore = useResourceCategoryStore();
            try {
                await categoryStore.addItem(data);

                if (categoryStore.error) {
                    console.log(categoryStore.error);
                    this.displayNotification(categoryStore.error, "error");
                } else {
                    this.displayNotification(categoryStore.success, "success");
                    this.closeModal();
                }
            } catch (error) {
                console.error("Failed to add category:", error);
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
        <div class="overflow-x-auto h-[75vh]">
            <table class="table w-full">
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
                        v-for="(item, index) in inventoryItems"
                        :key="item.item_id"
                    >
                        <td>{{ index + 1 }}</td>
                        <td>{{ item.item_name }}</td>
                        <td>{{ item.category }}</td>
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
                                    <li><a>Edit</a></li>
                                    <li><a>Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="join bg-gray-200 text-slate-800 mt-2">
            <button
                class="join-item bg-gray-200 text-slate-800 hover:text-white btn btn-sm"
            >
                1
            </button>
            <button
                class="join-item bg-gray-200 text-slate-800 hover:text-white btn btn-sm"
            >
                2
            </button>
            <button
                class="join-item bg-gray-200 text-slate-800 hover:text-white btn btn-sm"
            >
                3
            </button>
            <button
                class="join-item bg-gray-200 text-slate-800 hover:text-white btn btn-sm"
            >
                4
            </button>
        </div>
    </div>
    <Modal :show="modal.open" @close="closeModal">
        <NewResource
            v-if="modal.component === 'NewResource'"
            @close="closeModal"
            :category="category"
        />

        <NewCategory
            v-if="modal.component === 'NewCategory'"
            @close="closeModal"
            @newCategory="newCategory"
        />
    </Modal>
</template>

<style scoped></style>
