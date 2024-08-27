<template>
    <div class="mx-auto bg-white rounded-lg shadow-lg p-3">
        <h2 class="text-2xl font-semibold mb-6">New Resource</h2>

        <form @submit.prevent="submitForm" class="bg-white">
            <!-- Item Name -->
            <div class="mb-4">
                <label
                    for="item_name"
                    class="block text-sm font-medium text-gray-700"
                    >Item Name</label
                >
                <input
                    v-model="form.item_name"
                    type="text"
                    id="item_name"
                    class="input input-bordered w-full bg-white ring-1 ring-slate-300"
                    required
                />
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label
                    for="description"
                    class="block text-sm font-medium text-gray-700"
                    >Description</label
                >
                <textarea
                    v-model="form.description"
                    id="description"
                    class="textarea textarea-bordered w-full bg-white ring-1 ring-slate-300"
                    rows="3"
                    required
                ></textarea>
            </div>

            <!-- Category -->
            <div class="mb-4">
                <label
                    for="category"
                    class="block text-sm font-medium text-gray-700"
                    >Category</label
                >
                <select
                    v-model="form.category"
                    id="category"
                    class="select select-bordered w-full bg-white ring-1 ring-slate-300"
                    required
                >
                    <option disabled value="">Select Category</option>
                    <option>Electronics</option>
                    <option>Furniture</option>
                    <option>Office Supplies</option>
                    <option>Other</option>
                </select>
            </div>

            <!-- Quantity -->
            <div class="mb-4">
                <label
                    for="quantity"
                    class="block text-sm font-medium text-gray-700"
                    >Quantity</label
                >
                <input
                    v-model="form.quantity"
                    type="number"
                    id="quantity"
                    class="input input-bordered w-full bg-white ring-1 ring-slate-300"
                    min="1"
                    required
                />
            </div>

            <!-- Unit -->
            <div class="mb-4">
                <label
                    for="unit"
                    class="block text-sm font-medium text-gray-700"
                    >Unit</label
                >
                <input
                    v-model="form.unit"
                    type="text"
                    id="unit"
                    class="input input-bordered w-full bg-white ring-1 ring-slate-300"
                    required
                />
            </div>

            <!-- Price -->
            <div class="mb-4">
                <label
                    for="price"
                    class="block text-sm font-medium text-gray-700"
                    >Price</label
                >
                <input
                    v-model="form.price"
                    type="number"
                    id="price"
                    class="input input-bordered w-full bg-white ring-1 ring-slate-300"
                    min="0"
                    step="0.01"
                    required
                />
            </div>

            <!-- Date Added -->
            <div class="mb-4">
                <label
                    for="date_added"
                    class="block text-sm font-medium text-gray-700"
                    >Date Added</label
                >
                <input
                    v-model="form.date_added"
                    type="date"
                    id="date_added"
                    class="input input-bordered w-full bg-white ring-1 ring-slate-300"
                    required
                />
            </div>

            <!-- Photos -->
            <div class="mb-4">
                <label
                    for="photos"
                    class="block text-sm font-medium text-gray-700"
                    >Photos</label
                >
                <input
                    type="file"
                    id="photos"
                    class="file-input file-input-bordered w-full bg-white ring-1 ring-slate-300"
                    multiple
                />
            </div>

            <!-- Submit Button -->
            <div class="flex w-full text-white gap-2">
                <button type="submit" class="btn bg-slate-900 flex-1">
                    Save Item
                </button>
                <button
                    type="button"
                    @click="$emit('close')"
                    class="btn bg-rose-500 hover:bg-rose-600 text-white flex-1"
                >
                    Cancel
                </button>
            </div>
        </form>
    </div>
</template>

<script>
export default {
    data() {
        return {
            form: {
                item_name: "",
                description: "",
                category: "",
                quantity: "",
                unit: "",
                price: "",
                date_added: "",
                photos: null,
            },
        };
    },
    methods: {
        submitForm() {
            // Handle form submission logic here
            console.log("Form submitted:", this.form);
        },
    },
};
</script>

<style scoped>
/* Additional styling can be added here if needed */
</style>
