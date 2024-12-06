<template>
    <div class="max-w-3xl mx-auto p-6 bg-white shadow-md rounded-lg">
        <h3 class="text-2xl font-semibold mb-6">Payment Information</h3>

        <form @submit.prevent="submitForm" class="space-y-6">
            <!-- Payment Type -->
            <div>
                <InputLabel
                    for="paymentType"
                    value="Payment Type"
                    required="true"
                />

                <Select
                    v-model="form.payment_type"
                    :options="paymentMethods"
                    optionLabel="name"
                    optionValue="name"
                    placeholder="Select Payment Method"
                    class="w-full md:w-56"
                    required
                    :invalid="errors.payment_type"
                    @update:modelValue="fetchPaymentInformation"
                />

                <InputError :message="errors.payment_type" />
            </div>

            <div
                v-if="form.processing"
                class="w-full flex justify-center items-center"
            >
                <LoadingIcon />
            </div>

            <!-- Custom Fields -->
            <div>
                <h4 class="text-lg font-medium mb-4">Custom Fields</h4>
                <div
                    v-for="(field, index) in form.payment_details"
                    :key="index"
                    class="space-y-4 flex"
                >
                    <div class="flex items-center space-x-4 mt-2">
                        <TextInput
                            v-model="field.name"
                            type="text"
                            placeholder="Field Name"
                            required
                        />
                        <TextInput
                            v-model="field.value"
                            type="text"
                            placeholder="Field Value"
                            required
                        />
                        <Button
                            icon="pi pi-times"
                            class="p-button-danger p-button-text"
                            @click="removeField(index)"
                            aria-label="Remove Field"
                        />
                    </div>
                </div>
                <PrimaryButton class="mt-3" @click="addField"
                    >+ Add Field</PrimaryButton
                >
            </div>

            <div class="flex gap-1 md:flex-row flex-col">
                <PrimaryButton
                    class="w-full justify-center flex-1"
                    type="submit"
                    :disabled="form.processing"
                    >Submit</PrimaryButton
                >
                <PrimaryButton
                    class="bg-rose-600 hover:bg-rose-500 text-white flex-1"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="$emit('close')"
                    type="button"
                >
                    Cancel
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>

<script>
import { useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Button from "primevue/button";
import Select from "primevue/select";
import { useUserStore } from "@/Store/UserStore";
import axios from "axios";
import LoadingIcon from "@/Components/LoadingIcon.vue";

export default {
    props: {
        paymentMethods: {
            type: Array,
            required: true,
        },
    },
    components: {
        InputLabel,
        TextInput,
        InputError,
        PrimaryButton,
        Button,
        Select,
        LoadingIcon,
    },
    setup(props, { emit }) {
        const form = useForm({
            payment_type: "",
            payment_details: [],
        });

        const addField = () => {
            form.payment_details.push({ name: "", value: "" });
        };

        const removeField = (index) => {
            form.payment_details.splice(index, 1);
        };

        const submitForm = async () => {
            emit("updateOrCreate", form);
            emit("close");
        };

        const fetchPaymentInformation = async (params) => {
            const payload = {
                payment_type: params,
            };
            form.processing = true;
            const response = await axios.post(
                `/api/business/${
                    useUserStore().business
                }/search-payment-information`,
                payload
            );
            const data = response.data.payment_details || [];
            form.payment_details.push(...data);
            form.processing = false;
        };

        return {
            form,
            addField,
            removeField,
            submitForm,
            errors: form.errors,
            fetchPaymentInformation,
        };
    },
};
</script>

<style scoped></style>
