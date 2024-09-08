<!-- CustomerForm.vue -->
<template>
    <div class="bg-white h-fit p-6 rounded-lg shadow-md relative">
        <AlertNotification
            position="top"
            :open="form.success || form.error"
            :message="
                form.success ? form.success : form.error ? form.error : ''
            "
            :status="form.success ? 'success' : 'error'"
        />
        <h2 class="text-slate-900 text-xl font-semibold mb-4">
            {{ isEditing ? "Update Customer" : "Create New Customer" }}
        </h2>

        <form @submit.prevent="handleSubmit">
            <div class="mb-4">
                <InputLabel
                    for="full_names"
                    value="Full Names"
                    required="true"
                />
                <TextInput
                    id="full_names"
                    v-model="form.full_names"
                    class="block mt-1 w-full"
                    placeholder="Enter full names"
                    :error="form.errors.full_names"
                    required
                />
            </div>

            <div class="mb-4">
                <InputLabel for="email" value="Email" required="true" />
                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    class="block mt-1 w-full"
                    placeholder="Enter email address"
                    :error="form.errors.email"
                    required
                />
            </div>

            <div class="mb-4">
                <InputLabel
                    for="phone_number"
                    value="Phone Number"
                    required="true"
                />
                <TextInput
                    id="phone_number"
                    v-model="form.phone_number"
                    class="block mt-1 w-full"
                    placeholder="Enter phone number"
                    :error="form.errors.phone_number"
                    required
                />
            </div>

            <div class="mb-4">
                <InputLabel for="address" value="Address" required="true" />
                <TextInput
                    id="address"
                    v-model="form.address"
                    class="block mt-1 w-full"
                    placeholder="Enter address"
                    :error="form.errors.address"
                    required
                />
            </div>

            <div class="flex justify-end gap-1">
                <PrimaryButton
                    type="submit"
                    class="bg-slate-900 text-white flex-1"
                >
                    {{ isEditing ? "Update" : "Create" }} Customer
                </PrimaryButton>

                <PrimaryRoseButton @click="$emit('close')" class="flex-1">
                    Cancel
                </PrimaryRoseButton>
            </div>
        </form>
    </div>
</template>

<script>
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AlertNotification from "@/Components/AlertNotification.vue";
import { useCustomerStore } from "@/Store/Customer";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";

export default {
    components: {
        InputLabel,
        TextInput,
        PrimaryButton,
        PrimaryRoseButton,
    },
    props: {
        customer: {
            type: Object,
            default: () => ({
                full_names: "",
                email: "",
                phone_number: "",
                address: "",
            }),
        },
        isEditing: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            form: {
                full_names: this.customer.full_names,
                email: this.customer.email,
                phone_number: this.customer.phone_number,
                address: this.customer.address,
                errors: {},
                success: null,
                error: null,
            },
        };
    },
    methods: {
        async handleSubmit() {
            this.form.errors = {};
            this.form.success = null;
            this.form.error = null;

            try {
                const customerStore = useCustomerStore();
                if (this.isEditing) {
                    await customerStore.updateCustomer({
                        id: this.customer.id,
                        ...this.form,
                    });

                    if (customerStore.error == null) {
                        this.form.success = "Customer updated successfully!";
                        this.$emit("close");
                    } else {
                        this.form.error = customerStore.error;
                        // this.resetForm();
                    }
                } else {
                    await customerStore.createCustomer(this.form);
                    if (customerStore.error == null) {
                        this.form.success = "Customer created successfully!";
                        this.$emit("close");
                    } else {
                        this.form.error = customerStore.error;
                        // this.resetForm();
                    }
                }
            } catch (error) {
                this.form.errors = error.response.data.errors || {};
                this.form.error =
                    "An error occurred while processing your request.";
            }
        },
        resetForm() {
            this.form.full_names = "";
            this.form.email = "";
            this.form.phone_number = "";
            this.form.address = "";
        },
    },
};
</script>

<style scoped></style>
