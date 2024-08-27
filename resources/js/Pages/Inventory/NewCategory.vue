<template>
    <div class="max-w-2xl p-6 mx-auto bg-white rounded-lg">
        <h2 class="text-2xl font-semibold">New Category</h2>

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
                <PrimaryButton type="submit" class="btn bg-slate-900 flex-1">
                    Save Category
                </PrimaryButton>
                <PrimaryButton
                    type="button"
                    @click="$emit('close')"
                    class="btn bg-rose-500 hover:bg-rose-600 text-white flex-1"
                >
                    Cancel
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>

<script>
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

export default {
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
            // Handle form submission logic here
            console.log("Form submitted:", this.form);
        },
    },
    components: {
        InputLabel,
        PrimaryButton,
        TextInput,
    },
};
</script>

<style scoped>
/* Additional styling can be added here if needed */
</style>
