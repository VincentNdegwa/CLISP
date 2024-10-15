<script>
import { useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";
import Button from "primevue/button";
import FileUpload from "primevue/fileupload";

export default {
    props: ["user"],
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
            user_id: this.$page.props.auth.user.id,
            logo: null,
        });

        return {
            form,
            businessTypes: [],
            industries: [],
            notification: {
                open: false,
                message: "",
                status: "error",
            },
            logoFile: null,
        };
    },
    components: {
        InputLabel,
        TextInput,
        InputError,
        PrimaryButton,
        Button,
        FileUpload,
    },
    mounted() {
        axios
            .get("/api/business/details")
            .then((response) => {
                this.businessTypes = response.data.businessTypes;
                this.industries = response.data.industries;
            })
            .catch((error) => {
                console.error(error);
            });
    },
    methods: {
        async handleLogoUpload() {
            let formData = new FormData();
            formData.append("file", this.logoFile);
            formData.append("folder", "business-logo");
            await axios
                .post("/api/file/upload", formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                })
                .then((response) => {
                    if (response.data.error) {
                        this.notification.open = true;
                        this.notification.message = "Failed to upload logo";
                        this.notification.status = "error";
                    } else {
                        this.form.logo = response.data.path;
                        this.notification.open = true;
                        this.notification.message =
                            "Logo uploaded successfully";
                        this.notification.status = "success";
                    }
                    console.log(response);
                })
                .catch((error) => {
                    console.error(error);
                    this.notification.open = true;
                    this.notification.message = "Failed to upload logo";
                    this.notification.status = "error";
                    return false;
                });
        },
        setLogoFile(event) {
            this.logoFile = event.target.files[0];
        },
        async submit() {
            if (
                this.logoFile != null &&
                this.logoFile != undefined &&
                this.logoFile != ""
            ) {
                await this.handleLogoUpload();
            }

            const formData = new FormData();
            for (const key in this.form) {
                formData.append(key, this.form[key]);
            }

            axios
                .post("/api/business/create", formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                })
                .then((res) => {
                    if (!res.data.error) {
                        this.$emit("close", res.data.data);
                        this.notification.open = true;
                        this.notification.message = res.data.message;
                        this.notification.status = "success";

                        window.location.reload()
                    }

                    if (res.data.error) {
                        this.form.hasErrors = true;
                        this.form.errors = res.data.errors;
                        this.notification.open = true;
                        this.notification.message = res.data.message;
                        this.notification.status = "error";
                    }
                })
                .catch((errorMessages) => {
                    console.log(errorMessages);
                    this.notification.open = true;
                    this.notification.message = errorMessages.message;
                    this.notification.status = "error";
                });
        },
    },
};
</script>

<template>
    <AlertNotification
        :open="notification.open"
        :message="notification.message"
        :status="notification.status"
    />
    <div
        class="w-full max-w-5xl mx-auto p-5 bg-white shadow-md rounded-md text-slate-950"
    >
        <div class="text-center text-2xl font-semibold mb-6">
            Create New Business
        </div>
        <form @submit.prevent="submit" class="grid gap-6 sm:grid-cols-2">
            <!-- Business Name -->
            <div>
                <InputLabel
                    for="business_name"
                    value="Business Name"
                    required="true"
                />
                <TextInput
                    id="business_name"
                    type="text"
                    class="mt-1 w-full"
                    v-model="form.business_name"
                    required
                    autocomplete="business_name"
                />
                <InputError class="mt-2" :message="form.errors.business_name" />
            </div>

            <!-- Email -->
            <div>
                <InputLabel
                    for="email"
                    value="Business Email"
                    required="true"
                />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 w-full"
                    v-model="form.email"
                    required
                    autocomplete="email"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Business Type -->
            <div>
                <InputLabel
                    for="business_type"
                    value="Business Type"
                    required="true"
                />
                <select
                    id="business_type"
                    class="mt-1 w-full p-2 border border-gray-300 rounded-md"
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

            <!-- Industry -->
            <div>
                <InputLabel for="industry" value="Industry" required="true" />
                <select
                    id="industry"
                    class="mt-1 w-full p-2 border border-gray-300 rounded-md"
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
                <InputError class="mt-2" :message="form.errors.industry_id" />
            </div>

            <!-- Location -->
            <div>
                <InputLabel for="location" value="Location" required="true" />
                <TextInput
                    id="location"
                    type="text"
                    class="mt-1 w-full"
                    v-model="form.location"
                    required
                    autocomplete="location"
                />
                <InputError class="mt-2" :message="form.errors.location" />
            </div>

            <!-- Phone Number -->
            <div>
                <InputLabel
                    for="phone_number"
                    value="Phone Number"
                    required="true"
                />
                <TextInput
                    id="phone_number"
                    type="text"
                    class="mt-1 w-full"
                    v-model="form.phone_number"
                    required
                    autocomplete="phone_number"
                />
                <InputError class="mt-2" :message="form.errors.phone_number" />
            </div>

            <!-- Website -->
            <div>
                <InputLabel for="website" value="Website" />
                <TextInput
                    id="website"
                    type="text"
                    class="mt-1 w-full"
                    v-model="form.website"
                    autocomplete="website"
                />
                <InputError class="mt-2" :message="form.errors.website" />
            </div>

            <!-- Registration Number -->
            <div>
                <InputLabel
                    for="registration_number"
                    value="Registration Number"
                    required="true"
                />
                <TextInput
                    id="registration_number"
                    type="text"
                    class="mt-1 w-full"
                    v-model="form.registration_number"
                    required
                    autocomplete="registration_number"
                />
                <InputError
                    class="mt-2"
                    :message="form.errors.registration_number"
                />
            </div>

            <!-- Business Logo -->
            <div>
                <div class="flex flex-col gap-1 text-ellipsis">
                    <InputLabel for="logo" value="Business Logo" />
                    <input
                        type="file"
                        @change="setLogoFile"
                        accept="image/*"
                        class=""
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.logo" />
            </div>

            <!-- Submit & Cancel Buttons -->
            <div class="sm:col-span-2 flex gap-2">
                <PrimaryButton
                    class="flex-1"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register Business
                </PrimaryButton>
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
<style></style>
