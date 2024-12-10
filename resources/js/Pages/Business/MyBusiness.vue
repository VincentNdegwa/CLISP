<script>
import Modal from "@/Components/Modal.vue";
import NewBusiness from "@/Components/NewBusiness.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useMyBusiness } from "@/Store/MyBusiness";
import { useUserStore } from "@/Store/UserStore";
import { Head, usePage } from "@inertiajs/vue3";
import Avatar from "primevue/avatar";
import Badge from "primevue/badge";
import PaymentInformationForm from "./PaymentInformationForm.vue";
import { usePaymentMethods } from "@/Store/PaymentMethods";
import { onMounted, ref } from "vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

export default {
    components: {
        AuthenticatedLayout,
        Avatar,
        Badge,
        PrimaryButton,
        Head,
        Modal,
        NewBusiness,
        PaymentInformationForm,
        ConfirmationModal,
    },
    setup() {
        const { props } = usePage();
        const myBusiness = useMyBusiness();
        const paymentMethodsStore = usePaymentMethods();
        const notification = ref({
            status: "",
            message: "",
        });

        const queries = ref({
            category: "Information-Required",
        });

        myBusiness.fetchMyBusiness({
            userId: props.auth.user.id,
            businessId: useUserStore().business,
        });

        const fetchUpdatedBusiness = () => {
            myBusiness.fetchMyBusiness({
                userId: props.auth.user.id,
                businessId: useUserStore().business,
            });
        };

        const createOrUpdate = async (params) => {
            await paymentMethodsStore.createOrUpdatePaymentInformation(params);
        };

        const setDefault = async (paymentMethod) => {
            try {
                await paymentMethodsStore.setDefault(paymentMethod.id);
            } catch (error) {
                console.error("Failed to set default payment method:", error);
            }
        };
        onMounted(async () => {
            await paymentMethodsStore.fetchPaymentMethods(queries.value);
            await paymentMethodsStore.fetchPaymentInformations();
        });

        return {
            myBusiness,
            fetchUpdatedBusiness,
            paymentMethodsStore,
            createOrUpdate,
            setDefault,
        };
    },
    methods: {
        formatRole(role) {
            return `<span class="inline-block px-3 py-1 text-sm font-semibold bg-purple-200 text-purple-800 rounded-full">${role}</span>`;
        },
        getStatusSeverity(status) {
            return status === "active" ? "success" : "danger";
        },
        closeModal(data) {
            this.modal.open = false;
            this.modal.component = "";

            if (data?.business_id) {
                this.fetchUpdatedBusiness();
            }
        },
        openModal(component) {
            this.modal.open = true;
            this.modal.component = component;
        },
        openEditBusiness() {
            this.currentBusiness = this.myBusiness?.business?.business;
            this.openModal("EditBusiness");
        },
        openConfirmation(title, message, callback) {
            this.confirmation.isOpen = true;
            this.confirmation.title = title;
            this.confirmation.message = message;
            this.confirmation.method = callback;
        },
        closeConfirmation() {
            this.confirmation.isOpen = false;
        },
        funcTest() {
            console.log("function working...");
        },
    },
    data() {
        return {
            modal: {
                open: false,
                component: "",
            },
            confirmation: {
                isOpen: false,
                title: "",
                message: "",
                method: null,
            },
            currentBusiness: this.myBusiness.business.business,
        };
    },
};
</script>

<template>
    <Head title="Business Information" />
    <ConfirmationModal
        :isOpen="confirmation.isOpen"
        :title="confirmation.title"
        :message="confirmation.message"
        @confirm="confirmation.method"
        @close="closeConfirmation"
    />
    <Modal :show="modal.open" @close="closeModal">
        <NewBusiness
            v-if="modal.component == 'EditBusiness'"
            @close="closeModal"
            :edit="true"
            :editData="currentBusiness"
        />
        <PaymentInformationForm
            v-if="modal.component == 'Add Payments'"
            @close="closeModal"
            @updateOrCreate="createOrUpdate"
            :paymentMethods="paymentMethodsStore.methods"
        />
    </Modal>
    <AuthenticatedLayout>
        <div class="text-2xl font-extrabold text-gray-800 mb-6">
            Business Information
        </div>
        <div class="container mx-auto p-5">
            <div class="flex flex-col gap-4">
                <!-- Business Details Section -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex flex-col">
                        <div class="flex justify-end">
                            <PrimaryButton @click="openEditBusiness">
                                Edit Business
                            </PrimaryButton>
                        </div>
                        <div class="flex flex-col lg:flex-row">
                            <div class="lg:w-5/12 w-full">
                                <img
                                    :src="
                                        myBusiness?.business?.business?.logo ||
                                        '/images/default-business-logo.png'
                                    "
                                    class="h-60 w-60 rounded-sm"
                                    alt="Business Logo"
                                />
                                <div class="flex flex-col mt-4">
                                    <h2
                                        class="text-2xl font-semibold text-gray-800"
                                    >
                                        {{
                                            myBusiness?.business?.business
                                                ?.business_name
                                        }}
                                    </h2>
                                    <p class="text-gray-600">
                                        {{
                                            myBusiness?.business?.business
                                                ?.location
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div
                                class="flex flex-col space-y-6 text-lg mt-4 lg:mt-0 lg:ml-8"
                            >
                                <div class="flex items-center space-x-2">
                                    <i class="pi pi-envelope" />
                                    <p class="font-semibold text-gray-700">
                                        Email:
                                    </p>
                                    <p>
                                        {{
                                            myBusiness?.business?.business
                                                ?.email
                                        }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="pi pi-phone" />
                                    <p class="font-semibold text-gray-700">
                                        Phone Number:
                                    </p>
                                    <p>
                                        {{
                                            myBusiness?.business?.business
                                                ?.phone_number
                                        }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="pi pi-building" />
                                    <p class="font-semibold text-gray-700">
                                        Registration Number:
                                    </p>
                                    <p>
                                        {{
                                            myBusiness?.business?.business
                                                ?.registration_number
                                        }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="pi pi-calendar" />
                                    <p class="font-semibold text-gray-700">
                                        Date Registered:
                                    </p>
                                    <p>
                                        {{
                                            myBusiness?.business?.business
                                                ?.date_registered
                                        }}
                                    </p>
                                </div>
                                <div class="flex gap-2 items-center">
                                    <p class="font-semibold text-gray-700">
                                        Role:
                                    </p>
                                    <div
                                        v-html="
                                            formatRole(
                                                myBusiness?.business?.role
                                            )
                                        "
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Details Section -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        Other Details
                    </h2>
                    <div class="flex items-center space-x-2">
                        <i class="pi pi-credit-card" />
                        <p class="font-semibold text-gray-700">
                            Business Type:
                        </p>
                        <p>
                            {{
                                myBusiness?.business?.business?.business_type
                                    ?.name
                            }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-2 mt-2">
                        <i class="pi pi-dollar" />
                        <p class="font-semibold text-gray-700">Industry:</p>
                        <p>
                            {{ myBusiness?.business?.business?.industry?.name }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-2 mt-2">
                        <i class="pi pi-dollar" />
                        <p class="font-semibold text-gray-700">Currency:</p>
                        <p>
                            {{ myBusiness?.business?.business?.currency_code }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-2 mt-2">
                        <i class="pi pi-check" />
                        <p class="font-semibold text-gray-700">Status:</p>
                        <Badge
                            :severity="
                                getStatusSeverity(
                                    myBusiness?.business?.business?.status
                                )
                            "
                            class="px-4 py-2 text-lg"
                            style="text-transform: capitalize"
                        >
                            {{ myBusiness?.business?.business?.status }}
                        </Badge>
                    </div>
                </div>

                <!-- Payment Information Section -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">
                            Payment Information
                        </h2>
                        <PrimaryButton @click="openModal('Add Payments')">
                            Add Payments
                        </PrimaryButton>
                    </div>
                    <div
                        v-if="
                            paymentMethodsStore?.paymentInformations?.length > 0
                        "
                        class="flex flex-wrap gap-4"
                    >
                        <div
                            v-for="(
                                item, index
                            ) in paymentMethodsStore?.paymentInformations"
                            :key="index"
                            class="border flex-1 md:flex-none p-4 rounded-md shadow-md bg-white"
                        >
                            <div class="flex justify-between items-center">
                                <h3 class="font-bold text-lg">
                                    {{ item.payment_type }}
                                </h3>
                                <!-- Highlight Default Payment -->
                                <span
                                    v-if="
                                        item.default === 'true' ||
                                        item.default === true
                                    "
                                    class="text-white bg-green-500 px-2 py-1 rounded-md text-sm"
                                >
                                    Default
                                </span>
                            </div>

                            <ul class="mt-2">
                                <li
                                    v-for="(
                                        detail, detailIndex
                                    ) in item.payment_details"
                                    :key="detailIndex"
                                >
                                    <strong>{{ detail.name }}:</strong>
                                    {{ detail.value }}
                                </li>
                            </ul>

                            <!-- Display "Set Default" Button -->
                            <div
                                v-if="
                                    item.default == false ||
                                    item.default == 'false'
                                "
                                class="mt-4"
                            >
                                <PrimaryButton
                                    @click="
                                        openConfirmation(
                                            'Set Payment Default',
                                            `Are you sure you want to set payment ${item.payment_type} as default`,
                                            () => {
                                                setDefault(item);
                                            }
                                        )
                                    "
                                    class="text-sm"
                                >
                                    Set Default
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>

                    <div v-else>
                        <p class="text-gray-600 text-lg">
                            No payment information found.
                        </p>
                        <p class="text-red-600 font-bold text-md mt-2">
                            Please add payment information that will be
                            displayed in the invoice and agreement
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped></style>
