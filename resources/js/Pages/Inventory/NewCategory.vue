<script>
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

export default {
    props: ["newCategory", "data", "loading"],
    data() {
        return {
            form: {
                name: "",
                description: "",
            },
        };
    },
    methods: {
        submitForm() {
            if (this.newCategory === "false") {
                this.form.id = this.data.id;
                this.$emit("updateCategory", this.form);
            } else {
                this.$emit("newCategory", this.form);
            }
        },
    },
    components: {
        InputLabel,
        PrimaryButton,
        TextInput,
    },
    mounted() {
        if (this.newCategory === "false") {
            this.form = { ...this.data };
        }
    },
};
</script>

<template>
    <div class="max-w-2xl p-6 mx-auto bg-white rounded-lg">
        <h2 class="text-2xl font-semibold">
            {{ newCategory == "true" ? "New Category" : "Update Category" }}
        </h2>

        <form @submit.prevent="submitForm" class="space-y-6 w-full">
            <!-- Category Name -->
            <div>
                <InputLabel value="Category Name" required />
                <TextInput
                    v-model="form.name"
                    type="text"
                    id="name"
                    class="input input-bordered w-full bg-white ring-1 ring-slate-300"
                    required
                />
            </div>

            <!-- Description -->
            <div>
                <InputLabel value="Description" />
                <textarea
                    v-model="form.description"
                    id="description"
                    class="textarea textarea-bordered w-full bg-white ring-1 ring-slate-300"
                    rows="4"
                ></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex w-full text-white gap-2">
                <PrimaryButton
                    type="submit"
                    class="btn bg-slate-900 flex-1"
                    :disabled="loading"
                >
                    {{
                        newCategory == "true"
                            ? "Save Category"
                            : "Update Category"
                    }}
                </PrimaryButton>
                <PrimaryButton
                    type="button"
                    @click="$emit('close')"
                    class="btn bg-rose-500 hover:bg-rose-600 text-white flex-1"
                    :disabled="loading"
                >
                    Cancel
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>
