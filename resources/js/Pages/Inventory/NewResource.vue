<script>
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { useResourceCategoryStore } from "@/Store/ResourceCategory";
import axios from "axios";
import { onMounted } from "vue";
import { VDateInput } from "vuetify/labs/VDateInput";

export default {
    props: ["dataEdit", "newResource", "loading"],

    setup() {
        const category = useResourceCategoryStore();

        onMounted(() => {
            category.fetchResourceCategory();
        });
        return { category };
    },

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
                details: {
                    purchase_price: "",
                    lease_price: "",
                    borrow_fee: "",
                    tax_rate: "",
                    tax_type: "Exclusive",
                },
                item_image: null,
                date: null,
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
                        if (this.newResource != "false") {
                            this.$emit("addResource", this.form);
                        } else {
                            this.$emit("updateResource", this.form);
                        }
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
        dateChange(event) {
            console.log(event);
        },
    },
    components: {
        InputLabel,
        PrimaryButton,
        VDateInput,
    },
    watch: {
        dataEdit: {
            handler(newValue) {
                if (newValue && typeof newValue === "object") {
                    this.form = { ...this.form, ...newValue };
                } else {
                    this.form = {
                        item_name: "",
                        description: "",
                        category_id: "",
                        quantity: "",
                        unit: "",
                        price: "",
                        details: {
                            purchase_price: "", // New field
                            lease_price: "", // New field
                            borrow_fee: "", // New field
                            tax_rate: "", // New field
                            tax_type: "Exclusive", // New field
                        },
                        item_image: null,
                        date: null,
                    };
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
            details: {
                purchase_price: "", // New field
                lease_price: "", // New field
                borrow_fee: "", // New field
                tax_rate: "", // New field
                tax_type: "Exclusive", // New field
            },
            item_image: null,
        };
    },
};
</script>

<template>
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
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
                    <InputLabel value="Category" />
                    <select
                        v-model="form.category_id"
                        id="category"
                        class="select select-bordered w-full bg-white ring-1 ring-slate-300"
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
                        :readonly="newResource == 'false'"
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

                <!-- Purchase Price -->
                <div>
                    <InputLabel value="Purchase Price" />
                    <input
                        v-model="form.details.purchase_price"
                        type="number"
                        id="purchase_price"
                        class="input input-bordered w-full bg-white ring-1 ring-slate-300"
                        min="0"
                        step="0.01"
                    />
                </div>

                <!-- Lease Price -->
                <div>
                    <InputLabel value="Lease Price" />
                    <input
                        v-model="form.details.lease_price"
                        type="number"
                        id="lease_price"
                        class="input input-bordered w-full bg-white ring-1 ring-slate-300"
                        min="0"
                        step="0.01"
                    />
                </div>

                <!-- Borrow Fee -->
                <div>
                    <InputLabel value="Borrow Fee" />
                    <input
                        v-model="form.details.borrow_fee"
                        type="number"
                        id="borrow_fee"
                        class="input input-bordered w-full bg-white ring-1 ring-slate-300"
                        min="0"
                        step="0.01"
                    />
                </div>

                <!-- Tax Rate -->
                <div>
                    <InputLabel value="Tax Rate (%)" />
                    <input
                        v-model="form.details.tax_rate"
                        type="number"
                        id="tax_rate"
                        class="input input-bordered w-full bg-white ring-1 ring-slate-300"
                        min="0"
                        max="100"
                        step="0.01"
                    />
                </div>

                <!-- Tax Type -->
                <div>
                    <InputLabel value="Tax Type" />
                    <select
                        v-model="form.details.tax_type"
                        id="tax_type"
                        class="select select-bordered w-full bg-white ring-1 ring-slate-300"
                    >
                        <option value="Inclusive">Inclusive</option>
                        <option value="Exclusive">Exclusive</option>
                    </select>
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
            </div>
        </form>
    </div>
</template>
