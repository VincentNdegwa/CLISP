<script>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";

export default {
    props: ["businessTypes", "industries", "user"],
    data() {
        const today = new Date();
        const formattedDate = today.toISOString().split("T")[0];

        const form = useForm({
            business_name: "",
            business_type_id: "",
            location: "",
            phone_number: "",
            email: "",
            website: "",
            industry_id: "",
            registration_number: "",
            date_registered: formattedDate,
            user_id: this.user.id,
        });

        return {
            form,
        };
    },
    components: {
        GuestLayout,
        Head,
        InputLabel,
        TextInput,
        InputError,
        PrimaryButton,
    },
    methods: {
        submit() {
            axios.post("api/business/create", this.form).then((res) => {
                console.log(res);

                if (!res.data.error) {
                    router.visit("subscription", {
                        method: "get",
                        data: { id: res.data.data.business_id },
                    });
                }

                if (res.data.error) {
                    this.form.hasErrors = true;
                    this.form.errors = res.data.errors;
                }
            });
        },
    },
};
</script>

<template>
    <Head title="Register Business" />

    <div
        class="w-full max-h-screen h-screen overflow-y-scroll bg-white grid place-items-center text-slate-950"
    >
        <ul class="steps hidden md:grid">
            <li class="step step-warning">Register</li>
            <li class="step step-warning">Register Business</li>
            <li class="step">Choose Plan</li>
            <li class="step">Make Payment</li>
            <li class="step">Complete</li>
        </ul>
        <div class="flex flex-col w-full p-2 sm:p-0 sm:w-fit">
            <div class="text-xl text-center">Register your business!</div>
            <form
                @submit.prevent="submit"
                class="bg-white p-3 text-slate-950 shadow-md rounded-md md:min-w-[40rem] sm:min-w-[30rem] min-w-full"
            >
                <div class="mt-4">
                    <InputLabel
                        for="business_name"
                        value="Business Name"
                        required="true"
                    />

                    <TextInput
                        id="business_name"
                        type="text"
                        class="mt-1 block w-full border"
                        v-model="form.business_name"
                        required
                        autofocus
                        autocomplete="business_name"
                    />

                    <InputError
                        class="mt-2"
                        :message="form.errors.business_name"
                    />
                </div>
                <div class="mt-4">
                    <InputLabel
                        for="email"
                        value="Business Email"
                        required="true"
                    />

                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full border"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="email"
                    />

                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel
                        for="business_type"
                        value="Business Type"
                        required="true"
                    />

                    <select
                        id="business_type"
                        class="mt-1 block w-full p-2 border border-gray-300 rounded-md"
                        v-model="form.business_type_id"
                        required
                    >
                        <option value="">Select Business Type</option>
                        <option
                            v-for="type in businessTypes"
                            :key="type.id"
                            :value="type.id"
                        >
                            {{ type.name }}
                        </option>
                    </select>

                    <InputError
                        class="mt-2"
                        :message="form.errors.business_type_id"
                    />
                </div>

                <div class="mt-4">
                    <InputLabel
                        for="industry"
                        value="Industry"
                        required="true"
                    />

                    <select
                        id="industry"
                        class="mt-1 block w-full p-2 border border-gray-300 rounded-md"
                        v-model="form.industry_id"
                        required
                    >
                        <option value="">Select Industry</option>
                        <option
                            v-for="industry in industries"
                            :key="industry.id"
                            :value="industry.id"
                        >
                            {{ industry.name }}
                        </option>
                    </select>

                    <InputError
                        class="mt-2"
                        :message="form.errors.industry_id"
                    />
                </div>

                <div class="mt-4">
                    <InputLabel
                        for="location"
                        value="Location"
                        required="true"
                    />

                    <TextInput
                        id="location"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.location"
                        required
                        autocomplete="location"
                    />

                    <InputError class="mt-2" :message="form.errors.location" />
                </div>

                <div class="mt-4">
                    <InputLabel
                        for="phone_number"
                        value="Phone Number"
                        required="true"
                    />

                    <TextInput
                        id="phone_number"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.phone_number"
                        required
                        autocomplete="phone_number"
                    />

                    <InputError
                        class="mt-2"
                        :message="form.errors.phone_number"
                    />
                </div>

                <div class="mt-4">
                    <InputLabel for="website" value="Website" />

                    <TextInput
                        id="website"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.website"
                        autocomplete="website"
                    />

                    <InputError class="mt-2" :message="form.errors.website" />
                </div>

                <div class="mt-4">
                    <InputLabel
                        for="registration_number"
                        value="Registration Number"
                        required="true"
                    />

                    <TextInput
                        id="registration_number"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.registration_number"
                        required
                        autocomplete="registration_number"
                    />

                    <InputError
                        class="mt-2"
                        :message="form.errors.registration_number"
                    />
                </div>

                <PrimaryButton
                    class="mt-4 w-full"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register Business
                </PrimaryButton>
            </form>
        </div>
    </div>
</template>
