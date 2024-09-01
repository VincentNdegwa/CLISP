<script>
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";

export default {
    props: ["category", "dataEdit", "newResource", "loading"],

    data() {
        const formData = new FormData();
        return {
            form: {
                item_name: "",
                description: "",
                category_id: "",
                quantity: "",
                unit: "",
                price: "",
                date_added: "",
                item_image: null,
            },
            formData,
        };
    },
    methods: {
        async submitForm() {
            if (this.formData.has("file") && this.formData.has("folder")) {
                try {
                    const response = await axios.post(
                        "/api/file/upload",
                        this.formData
                    );

                    if (response.data.error) {
                        alert(response.data.message);
                    } else {
                        const link = response.data.path;
                        this.form.item_image = link;
                        this.$emit("addResource", this.form);
                    }
                } catch (error) {
                    alert(error);
                    console.log(error);
                }
            } else {
                if (this.newResource != "false") {
                    this.$emit("addResource", this.form);
                } else {
                    this.$emit("updateResource", this.form);
                }
            }
        },
        addResourceImage(event) {
            const file = event.target.files[0];
            this.formData.append("file", file);
            this.formData.append("folder", "resources");
        },
    },
    components: {
        InputLabel,
        PrimaryButton,
    },
    watch: {
        dataEdit: {
            handler(newValue) {
                if (newValue && newValue != null) {
                    this.form = newValue;
                }
            },
            deep: true,
            immediate: true,
        },
    },
    unmounted() {
        this.form = {
            item_name: "",
            description: "",
            category_id: "",
            quantity: "",
            unit: "",
            price: "",
            date_added: "",
            item_image: null,
        };
    },
};
</script>

<style scoped></style>

<template>
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-semibold mb-6">
            {{
                newResource != "false"
                    ? "New Inventory Item"
                    : "Update Inventory Item"
            }}
        </h2>

        <form @submit.prevent="submitForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Item Name -->
                <div>
                    <InputLabel value="Item Name" required />
                    <input
                        v-model="form.item_name"
                        type="text"
                        id="item_name"
                        class="input input-bordered w-full bg-white ring-1 ring-slate-300"
                        required
                    />
                </div>

                <!-- Category -->
                <div>
                    <InputLabel value="Category" required />
                    <select
                        v-model="form.category_id"
                        id="category"
                        class="select select-bordered w-full bg-white ring-1 ring-slate-300"
                        required
                    >
                        <option disabled value="">Select Category</option>
                        <option
                            v-for="(item, index) in category.items.data"
                            :key="index"
                            :value="item.id"
                        >
                            {{ item.name }}
                        </option>
                    </select>
                </div>

                <!-- Quantity -->
                <div>
                    <InputLabel value="Quantity" required />
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
                <div>
                    <InputLabel value="Unit" required />
                    <input
                        v-model="form.unit"
                        type="text"
                        id="unit"
                        class="input input-bordered w-full bg-white ring-1 ring-slate-300"
                        required
                    />
                </div>

                <!-- Price -->
                <div>
                    <InputLabel value="Price" required />
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
                <div>
                    <InputLabel value="Date Added" required />
                    <input
                        v-model="form.date_added"
                        type="date"
                        id="date_added"
                        class="input input-bordered w-full bg-white ring-1 ring-slate-300"
                        required
                    />
                </div>
            </div>

            <!-- Description -->
            <div>
                <InputLabel value="Description" />
                <textarea
                    v-model="form.description"
                    id="description"
                    class="textarea textarea-bordered w-full bg-white ring-1 ring-slate-300"
                    rows="3"
                ></textarea>
            </div>

            <!-- Photos -->
            <div>
                <InputLabel value="Photos" />
                <input
                    @change="addResourceImage"
                    type="file"
                    id="photos"
                    class="file-input file-input-bordered w-full bg-white ring-1 ring-slate-300"
                />
            </div>

            <!-- Submit Button -->
            <div class="flex w-full text-white gap-2">
                <PrimaryButton
                    type="submit"
                    class="btn bg-slate-900 flex-1"
                    :disabled="loading"
                >
                    {{ newResource != "false" ? "Save Item" : "Update Item" }}
                </PrimaryButton>
                <PrimaryButton
                    type="button"
                    @click="$emit('close')"
                    :disabled="loading"
                    class="btn bg-rose-600 hover:bg-rose-500 text-white flex-1"
                >
                    Cancel
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>
