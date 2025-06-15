<script>
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import TextAreaInput from "@/Components/TextAreaInput.vue"
import FormLayout from "@/Layouts/FormLayout.vue";
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
        InputError,
        TextAreaInput,
        FormLayout
    },
    mounted() {
        if (this.newCategory === "false") {
            this.form = { ...this.data };
        }
    },
};
</script>

<template>
    <FormLayout :title="newCategory == 'false' ? 'Update Category' : 'New Category'">
      
        <form @submit.prevent="submitForm" class="space-y-6 w-full">
            <!-- Category Name -->
            <div>
                <InputLabel value="Category Name" required="true" />
                <TextInput v-model="form.name" type="text" id="name" class="w-full" required />
                <InputError :message="null" />
            </div>

            <!-- Description -->
            <div>
                <InputLabel value="Description" />
                <TextAreaInput v-model="form.description" id="description"
                    class="w-full bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slat-50 rounded-md" rows="4"
                    :autoResize="true" />
                <InputError :message="null" />
            </div>

            <!-- Submit Button -->
            <div class="flex w-full gap-2">
                <PrimaryButton type="submit"
                    class="flex-1 py-3 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-50 font-medium rounded-lg shadow-lg transition-all flex justify-center items-center"
                    :disabled="loading">
                    {{ loading ? "Loading..." : newCategory == "false" ? "Update Category" : "Save Category" }}
                </PrimaryButton>

                <PrimaryButton type="button" @click="$emit('close')"
                    class="flex-1 py-3 bg-rose-500 hover:bg-rose-600 text-slate-50 font-medium rounded-lg shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all flex justify-center items-center"
                    :disabled="loading">
                    Cancel
                </PrimaryButton>
            </div>
        </form>

    </FormLayout>
</template>
