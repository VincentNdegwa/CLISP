<script>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useResourceCategoryStore } from "@/Store/ResourceCategory";
import { Head } from "@inertiajs/vue3";

export default {
    components: {
        AuthenticatedLayout,
        Head,
    },
    setup() {
        const categories = useResourceCategoryStore();
        categories.fetchResourceCategory();
        return {
            categories,
        };
    },
};
</script>
<template>
    <Head title="Resource Categories" />
    <AuthenticatedLayout>
        <AlertNotification
            :open="categories.error != null"
            :message="categories.error"
            :status="categories.error ? 'error' : 'success'"
        />
        <div class="mx-auto p-4 h-fit">
            <div class="w-full mt-2 flex justify-between">
                <h1 class="text-xl font-bold mb-4">Resource Categories</h1>
                <SearchInput />
                <div class="join gap-2">
                    <button
                        @click="openNewCategoryForm"
                        class="btn bg-slate-950 text-white"
                    >
                        Add Categories
                    </button>
                </div>
            </div>

            <div class="h-[74vh] overflow-y-scroll relative mt-1">
                <table class="min-w-full bg-white relative">
                    <thead class="sticky top-0 bg-slate-200">
                        <tr>
                            <th class="py-2 px-4 border-b">#</th>
                            <th class="py-2 px-4 border-b">Name</th>
                            <th class="py-2 px-4 border-b">Description</th>
                        </tr>
                    </thead>

                    <tbody class="">
                        <tr
                            v-for="(item, index) in categories.items.data"
                            :key="index"
                            class="hover:bg-gray-100 transition-colors"
                        >
                            <td class="py-2 px-4 border-b">{{ item.id }}</td>
                            <td class="py-2 px-4 border-b">{{ item.name }}</td>
                            <td class="py-2 px-4 border-b">
                                {{ item.description }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center">
                <button
                    class="bg-gray-300 text-gray-700 py-2 px-4 rounded hover:bg-gray-400"
                    :disabled="!categories.items.prev_page_url"
                    @click="fetchCategories(currentPage - 1)"
                >
                    Previous
                </button>
                <span
                    >Page {{ currentPage }} of
                    {{ categories.items.last_page }}</span
                >
                <button
                    class="bg-gray-300 text-gray-700 py-2 px-4 rounded hover:bg-gray-400"
                    :disabled="!categories.items.next_page_url"
                    @click="fetchCategories(currentPage + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
