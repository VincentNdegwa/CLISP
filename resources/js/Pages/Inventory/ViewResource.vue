<script>
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";

export default {
    props: ["item"],
    components: {
        AuthenticatedLayout,
        Head,
        PrimaryButton,
    },
};
</script>
<template>
    <Head :title="item.item_name" />
    <AuthenticatedLayout>
        <div class="mx-auto p-4">
            <!-- Header Section -->
            <div class="flex items-center justify-between border-b-2 pb-4 mb-6">
                <a
                    :href="route('inventory.resources')"
                    class="bg-slate-900 text-white flex gap-3 p-2 rounded-md items-center"
                >
                    <i class="bi bi-arrow-left-circle text-xl"></i>
                    <div>Back</div>
                </a>
                <h1 class="text-3xl font-extrabold text-slate-900">
                    Item Details
                </h1>
                <button
                    class="px-4 py-2 bg-rose-500 text-white font-semibold rounded-lg hover:bg-rose-600 transition duration-200"
                ></button>
            </div>

            <!-- Item Image and Info Section -->
            <div class="lg:flex gap-8">
                <div class="md:h-72 md:w-72">
                    <div class="relative rounded-lg overflow-hidden">
                        <img
                            :src="item.item_image || '/images/no-image.avif'"
                            alt="Item Image"
                            class="w-full h-full object-cover"
                        />
                    </div>
                </div>

                <!-- Item Details -->
                <div class="lg:w-1/2 mt-6 lg:mt-0 space-y-4 p-2">
                    <h2 class="text-2xl font-semibold text-slate-800">
                        {{ item.item_name }}
                    </h2>
                    <p class="text-slate-600">
                        {{ item.description }}
                    </p>
                    <div class="space-y-3 mt-3 w-ful">
                        <div
                            class="flex gap-10 justify-between items-center text-slate-700"
                        >
                            <span class="font-medium">Quantity Available:</span>
                            <span class="font-bold text-lg"
                                >{{ item.quantity }}
                            </span>
                        </div>
                        <div
                            class="flex gap-10 justify-between items-center text-slate-700"
                        >
                            <span class="font-medium">Category:</span>
                            <span class="font-bold text-lg">{{
                                item.category.name
                            }}</span>
                        </div>
                        <div
                            class="flex gap-10 justify-between items-center text-slate-700"
                        >
                            <span class="font-medium">Location:</span>
                            <span class="font-bold text-lg"
                                >{{ item.business.business_name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History Section -->
            <div class="mt-10">
                <div class="w-full flex justify-between">
                    <h3
                        class="text-xl font-bold text-slate-900 border-b-2 pb-2"
                    >
                        Transaction History
                    </h3>
                    <PrimaryButton> View All </PrimaryButton>
                </div>
                <div class="overflow-x-auto mt-4 h-fit">
                    <table
                        class="table w-full bg-slate-50 rounded-lg shadow-sm"
                    >
                        <thead
                            class="bg-slate-100 text-slate-800 text-sm uppercase tracking-wide"
                        >
                            <tr>
                                <th class="py-3 px-4 text-left">Date</th>
                                <th class="py-3 px-4 text-left">
                                    Transaction Type
                                </th>
                                <th class="py-3 px-4 text-left">Quantity</th>
                                <th class="py-3 px-4 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-700">
                            <tr
                                class="border-b hover:bg-slate-100 transition duration-150"
                            >
                                <td class="py-3 px-4">2024-09-01</td>
                                <td class="py-3 px-4">Borrowing</td>
                                <td class="py-3 px-4">2</td>
                                <td class="py-3 px-4">Completed</td>
                            </tr>
                            <tr
                                class="border-b hover:bg-slate-100 transition duration-150"
                            >
                                <td class="py-3 px-4">2024-08-28</td>
                                <td class="py-3 px-4">Lending</td>
                                <td class="py-3 px-4">3</td>
                                <td class="py-3 px-4">Pending</td>
                            </tr>
                            <tr
                                class="hover:bg-slate-100 transition duration-150"
                            >
                                <td class="py-3 px-4">2024-08-20</td>
                                <td class="py-3 px-4">Purchase</td>
                                <td class="py-3 px-4">5</td>
                                <td class="py-3 px-4">Cancelled</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
