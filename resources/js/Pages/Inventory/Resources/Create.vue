<script>
import { useResourceCategoryStore } from "@/Store/ResourceCategory";
import axios from "axios";
import { onMounted } from "vue";
import { VDateInput } from "vuetify/labs/VDateInput";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import FileUpload from "primevue/fileupload";
import FormLayout from "@/Layouts/FormLayout.vue";

export default {
    props: ["dataEdit", "newResource", "loading", "category"],

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
                item_image: null,
                date: null,
                details: {
                    purchase_price: "",
                    lease_price: "",
                    borrow_fee: "",
                    tax_rate: 0,
                    tax_type: "",
                },
            },
            formData,
            taxTypes: [
                { name: "Inclusive", value: "Inclusive" },
                { name: "Exclusive", value: "Exclusive" },
            ],
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
        TextInput,
        InputError,
        PrimaryButton,
        VDateInput,
        Select,
        Textarea,
        FileUpload,
        FormLayout,
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
                            purchase_price: "",
                            lease_price: "",
                            borrow_fee: "",
                            tax_rate: "",
                            tax_type: "",
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
                purchase_price: "",
                lease_price: "",
                borrow_fee: "",
                tax_rate: 0,
                tax_type: "",
            },
            item_image: null,
        };
    },
};
</script>

<template>
    <FormLayout>
        <form @submit.prevent="submitForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Item Name -->
                <div>
                    <InputLabel value="Item Name" required="true" />
                    <TextInput
                        v-model="form.item_name"
                        type="text"
                        id="item_name"
                        class="w-full"
                        required
                    />
                    <InputError :message="null" />
                </div>

                <!-- Quantity -->
                <div>
                    <InputLabel value="Quantity" required="true" />
                    <TextInput
                        v-model="form.quantity"
                        :readonly="newResource == 'false'"
                        type="number"
                        id="quantity"
                        class="w-full"
                        min="1"
                        required
                    />
                    <InputError :message="null" />
                </div>

                <!-- Unit -->
                <div>
                    <InputLabel value="Unit" required="true" />
                    <TextInput
                        v-model="form.unit"
                        type="text"
                        id="unit"
                        class="w-full"
                        required
                    />
                    <InputError :message="null" />
                </div>

                <!-- Price -->
                <div>
                    <InputLabel value="Price" required="true" />
                    <TextInput
                        v-model="form.price"
                        type="number"
                        id="price"
                        class="w-full"
                        min="0"
                        step="0.01"
                        required
                    />
                    <InputError :message="null" />
                </div>

                <!-- Tax Rate -->
                <div>
                    <InputLabel value="Tax Rate (%)" required="true" />
                    <TextInput
                        v-model="form.details.tax_rate"
                        type="number"
                        id="tax_rate"
                        class="w-full"
                        min="0"
                        max="100"
                        step="0.01"
                        required
                    />
                    <InputError :message="null" />
                </div>

                <!-- Tax Type -->
                <div>
                    <InputLabel value="Tax Type" required="true" />
                    <Select
                        v-model="form.details.tax_type"
                        :options="taxTypes"
                        optionLabel="name"
                        optionValue="value"
                        placeholder="Select Tax Type"
                        class="w-full"
                    />
                    <InputError :message="null" />
                </div>

                <!-- Category -->
                <div>
                    <InputLabel value="Category" />
                    <Select
                        v-model="form.category_id"
                        :options="category.items.data"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Select Category"
                        class="w-full"
                    />
                    <InputError :message="null" />
                </div>

                <!-- Purchase Price -->
                <div>
                    <InputLabel value="Purchase Price" />
                    <TextInput
                        v-model="form.details.purchase_price"
                        type="number"
                        id="purchase_price"
                        class="w-full"
                        min="0"
                        step="0.01"
                    />
                    <InputError :message="null" />
                </div>

                <!-- Lease Price -->
                <div>
                    <InputLabel value="Lease Price" />
                    <TextInput
                        v-model="form.details.lease_price"
                        type="number"
                        id="lease_price"
                        class="w-full"
                        min="0"
                        step="0.01"
                    />
                    <InputError :message="null" />
                </div>

                <!-- Borrow Fee -->
                <div>
                    <InputLabel value="Borrow Fee" />
                    <TextInput
                        v-model="form.details.borrow_fee"
                        type="number"
                        id="borrow_fee"
                        class="w-full"
                        min="0"
                        step="0.01"
                    />
                    <InputError :message="null" />
                </div>
            </div>

            <!-- Description -->
            <div>
                <InputLabel value="Description" />
                <Textarea
                    v-model="form.description"
                    id="description"
                    rows="3"
                    class="w-full"
                    autoResize
                />
                <InputError :message="null" />
            </div>

            <!-- Photos -->
            <div>
                <InputLabel value="Photos" />
                <div
                    class="bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg p-2"
                >
                    <input
                        @change="addResourceImage"
                        type="file"
                        id="photos"
                        class="w-full text-sm text-slate-500 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-rose-50 file:text-rose-600 dark:file:bg-rose-900/30 dark:file:text-rose-400 hover:file:bg-rose-100 dark:hover:file:bg-rose-900/40"
                    />
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex w-full gap-2">
                <PrimaryButton
                    type="submit"
                    class="flex-1 py-3 bg-rose-500 hover:bg-rose-600 text-white font-medium rounded-lg shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all flex justify-center items-center"
                    :disabled="loading"
                >
                    {{ newResource != "false" ? "Save Item" : "Update Item" }}
                </PrimaryButton>
            </div>
        </form>
    </FormLayout>
</template>
