<script>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import currencyCodes from "@/currency";

import Select from "primevue/select";

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
            user_id: this.user?.id,
            currency_code: "USD",
        });

        return {
            form,
            currencyCodes,
        };
    },
    components: {
        GuestLayout,
        Head,
        InputLabel,
        TextInput,
        InputError,
        PrimaryButton,
        Select,
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
        getCurrencyName(code) {
            const currency = this.currencyCodes.find(
                (currency) => currency.code === code
            );
            return currency ? currency.code : "";
        },
    },
};
</script>

<template>
    <Head title="Register Business" />

    <div
        class="w-full min-h-screen bg-white dark:bg-gradient-to-br dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 text-slate-900 dark:text-white"
    >
        <!-- Steps indicator -->
        <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 pt-8">
            <div class="relative">
                <!-- Steps line -->
                <div class="absolute top-4 left-0 w-full h-1 bg-slate-200 dark:bg-white/10 rounded-full"></div>

                <!-- Steps circles -->
                <div class="relative z-10 flex justify-between items-center">
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center text-white font-semibold shadow-lg shadow-rose-500/30">1</div>
                        <span class="mt-2 text-xs text-rose-600 dark:text-rose-400 font-medium">Register</span>
                    </div>

                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center text-white font-semibold shadow-lg shadow-rose-500/30">2</div>
                        <span class="mt-2 text-xs text-rose-600 dark:text-rose-400 font-medium">Business</span>
                    </div>
<!-- 
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-white/20 flex items-center justify-center text-slate-500 dark:text-white/70 font-semibold">3</div>
                        <span class="mt-2 text-xs text-slate-500 dark:text-white/50 font-medium">Plan</span>
                    </div>
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-white/20 flex items-center justify-center text-slate-500 dark:text-white/70 font-semibold">4</div>
                        <span class="mt-2 text-xs text-slate-500 dark:text-white/50 font-medium">Payment</span>
                    </div>
                    
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-white/20 flex items-center justify-center text-slate-500 dark:text-white/70 font-semibold">5</div>
                        <span class="mt-2 text-xs text-slate-500 dark:text-white/50 font-medium">Complete</span>
                    </div> -->
                </div>
            </div>
        </div>

        <div class="flex flex-col w-full p-4 sm:p-6 items-center mt-8">
            <form
            @submit.prevent="submit"
            class="dark:bg-transparent dark:backdrop-blur-sm p-6 shadow-xl rounded-xl border border-slate-200 dark:border-white/10 md:min-w-[40rem] max-w-[50rem] sm:min-w-[30rem] min-w-full"
            >
            <div class="text-2xl font-bold text-slate-900 dark:text-white text-center mb-2">Register your business!</div>
                <div class="mt-4">
                    <InputLabel
                        for="business_name"
                        value="Business Name"
                        required="true"
                    />

                    <TextInput
                        id="business_name"
                        type="text"
                        v-model="form.business_name"
                        required
                        autofocus
                        autocomplete="business_name"
                    />

                    <InputError
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
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="email"
                    />

                    <InputError :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel
                        for="email"
                        value="Business Currency"
                        required="true"
                    />
                    <p class="text-rose-600 dark:text-rose-400 text-sm mb-2">
                        Please note: The selected currency will be used for all
                        transactions. Ensure that you choose carefully, as
                        changes cannot be made after your business is created.
                    </p>

                    <Select
                        v-model="form.currency_code"
                        :options="currencyCodes"
                        optionLabel="name"
                        optionValue="code"
                        placeholder="Select a Currency"
                        class="w-full"
                    >
                        <template #value="slotProps">
                            <div
                                v-if="slotProps.value"
                                class="flex items-center"
                            >
                                <div>
                                    {{ getCurrencyName(slotProps.value) }}
                                </div>
                            </div>
                            <span v-else>
                                {{ slotProps.placeholder }}
                            </span>
                        </template>
                        <template #option="slotProps">
                            <div class="flex items-center">
                                <div>{{ slotProps.option.code }}</div>
                                <span class="mx-1">-</span>
                                <div>{{ slotProps.option.name }}</div>
                            </div>
                        </template>
                    </Select>
                    <InputError :message="form.errors.currency_code" />
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
                        v-model="form.location"
                        required
                        autocomplete="location"
                    />

                    <InputError :message="form.errors.location" />
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
                        v-model="form.phone_number"
                        required
                        autocomplete="phone_number"
                    />

                    <InputError
                        :message="form.errors.phone_number"
                    />
                </div>
                <div class="mt-4">
                    <InputLabel for="business_type" value="Business Type" />

                    <Select
                        id="business_type"
                        v-model="form.business_type_id"
                        :options="businessTypes"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Select Business Type"
                        class="w-full"
                    />

                    <InputError
                        :message="form.errors.business_type_id"
                    />
                </div>

                <div class="mt-4">
                    <InputLabel for="industry" value="Industry" />

                    <Select
                        id="industry"
                        v-model="form.industry_id"
                        :options="industries"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Select Industry"
                        class="w-full"
                    />

                    <InputError
                        :message="form.errors.industry_id"
                    />
                </div>

                <div class="mt-4">
                    <InputLabel for="website" value="Website" />

                    <TextInput
                        id="website"
                        type="text"
                        v-model="form.website"
                        autocomplete="website"
                    />

                    <InputError :message="form.errors.website" />
                </div>

                <div class="mt-4">
                    <InputLabel
                        for="registration_number"
                        value="Registration Number"
                    />

                    <TextInput
                        id="registration_number"
                        type="text"
                        v-model="form.registration_number"
                        autocomplete="registration_number"
                    />

                    <InputError
                        :message="form.errors.registration_number"
                    />
                </div>

                <div class="mt-6">
                    <button
                        type="submit"
                        class="w-full py-3 bg-rose-500 hover:bg-rose-600 text-white font-medium rounded-lg shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all flex justify-center items-center"
                        :class="{ 'opacity-75': form.processing }"
                        :disabled="form.processing"
                    >
                        Register Business
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
