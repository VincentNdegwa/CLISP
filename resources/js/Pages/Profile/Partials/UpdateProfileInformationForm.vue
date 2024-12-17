<script setup>
import { ref } from "vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import axios from "axios";
import { useLoadImageStore } from "@/Store/loadImageStore";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    profile_image: user.profile_image,
});

const imagePreview = useLoadImageStore().getImage(user.profile_image,"/images/default-profile.png");
const imageFile = ref(null);

const uploadImage = async () => {
    if (!imageFile.value) {
        return;
    }

    const formData = new FormData();
    formData.append("file", imageFile.value);
    formData.append("folder", "profiles");

    try {
        const response = await axios.post(route("file.upload"), formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });

        if (!response.data.error) {
            imagePreview.value = response.data.path;
            form.profile_image = response.data.path;
        }
    } catch (error) {
        console.error("Image upload failed:", error);
    }
};

const updateProfile = async () => {
    try {
        if (imageFile.value) {
            await uploadImage();
        }
        form.patch(route("profile.update"));
    } catch (error) {
        console.error("Failed to update profile:", error);
    }
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form @submit.prevent="updateProfile" class="mt-6 space-y-6">
            <!-- Profile Picture -->
            <div>
                <InputLabel for="profile_image" value="Profile Picture" />
                <div class="flex items-end gap-4 mt-1 relative">
                    <img
                        :src="imagePreview"
                        alt="Profile Image"
                        class="w-32 h-32 rounded-full object-cover"
                    />
                    <label
                        for="profile_image_input"
                        class="cursor-pointer absolute bottom-0 left-2 bg-gray-200 px-2 rounded-md hover:bg-gray-300"
                    >
                        <i class="pi pi-upload"></i>
                        <input
                            id="profile_image_input"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="
                                (event) => {
                                    imageFile = event.target.files[0];
                                    uploadImage();
                                }
                            "
                        />
                    </label>
                </div>
                <InputError class="mt-2" :message="form.errors.profile_image" />
            </div>

            <!-- Name -->
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Email Verification -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800 flex gap-2 flex-wrap">
                    <div class="text-rose-600" >
                        Your email address is unverified.
                    </div>
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
