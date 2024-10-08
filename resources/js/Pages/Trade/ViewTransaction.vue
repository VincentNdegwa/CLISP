<script>
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PrimaryRoseButton from "@/Components/PrimaryRoseButton.vue";
import SplitButtonSelectCustom from "@/Components/SplitButtonSelectCustom.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useTransactionStore } from "@/Store/TransactionStore";
import { Head } from "@inertiajs/vue3";
import Badge from "primevue/badge";
import Tag from "primevue/tag";

export default {
    components: {
        AuthenticatedLayout,
        PrimaryButton,
        Head,
        PrimaryRoseButton,
        ConfirmationModal,
        Badge,
        SplitButtonSelectCustom,
        Tag,
    },
    data() {
        return {
            confirmation: {
                isOpen: false,
                title: "",
                message: "",
                method: null,
            },
            SelectItems: [
                {
                    label: "PDF Preview Agreement",
                    method: () => this.startAgreementPdf("pdf-view"),
                },
                {
                    label: "Print Agreement",
                    method: () => this.startAgreementPdf("print"),
                },
                {
                    label: "View Agreement",
                    method: () => this.startAgreementPdf("view"),
                },
                {
                    label: "Download Agreement",
                    method: () => this.startAgreementPdf("download"),
                },
                {
                    label: "Share Agreement",
                    method: () => this.startAgreementPdf("share"),
                },
            ],
            defaulItem: {
                label: "Print Agreement",
                method: () => this.startAgreementPdf("print"),
            },
            selectedReceiptItems: [
                {
                    label: "Print Receipt",
                    method: () => this.startReceipt("print"),
                },
            ],
            defaultSelectedReceiptItem: {
                label: "Print Receipt",
                method: () => this.startReceipt("print"),
            },
        };
    },
    props: {
        transactionId: {
            required: true,
            type: String,
        },
    },
    setup(props) {
        const transactionStore = useTransactionStore();

        onMounted: {
            transactionStore.getSingleTransaction(props.transactionId);
        }
        const statusClass = () => {
            switch (transactionStore.singleTransaction.status) {
                case "pending":
                    return "text-gray-500";
                case "processing":
                    return "text-yellow-500";
                case "completed":
                    return "text-green-500";
                case "failed":
                    return "text-red-500";
                default:
                    return "text-gray-500";
            }
        };

        const acceptTransaction = async () => {
            await transactionStore.acceptTransaction(props.transactionId);
        };

        const rejectTransaction = async (reason) => {
            await transactionStore.rejectTransaction(
                props.transactionId,
                reason
            );
        };

        const acceptAndPayTransaction = async () => {
            await transactionStore.acceptAndPayTransaction(props.transactionId);
        };

        const payTransaction = async () => {
            await transactionStore.payTransaction(props.transactionId);
        };

        const closeTransaction = async () => {
            await transactionStore.closeTransaction(props.transactionId);
        };

        return {
            transactionStore,
            statusClass,
            acceptTransaction,
            rejectTransaction,
            acceptAndPayTransaction,
            payTransaction,
            closeTransaction,
        };
    },
    methods: {
        convertCurrency(currency) {
            if (currency) {
                return (
                    Intl.NumberFormat({
                        style: "currency",
                        currency: "KES",
                    }).format(currency) || "0.0"
                );
            } else {
                ("0.0");
            }
        },
        buttonDisplay(action) {
            const transaction_type =
                this.transactionStore.singleTransaction.transaction_type;
            const transaction_status =
                this.transactionStore.singleTransaction.status;
            const isB2B = this.transactionStore.singleTransaction.isB2B;

            switch (action) {
                case "Approve_and_Pay":
                    return (
                        transaction_type == "Incoming" &&
                        transaction_status == "pending"
                    );
                case "Approve":
                    return (
                        ((transaction_type == "Incoming" && isB2B == true) ||
                            (transaction_type == "Outgoing" &&
                                isB2B == false)) &&
                        transaction_status == "pending"
                    );
                case "Cancel":
                    return (
                        (transaction_type == "Incoming" ||
                            transaction_type == "Outgoing") &&
                        (transaction_status == "pending" ||
                            transaction_status == "approved")
                    );
                case "Pay":
                    return (
                        ((transaction_type == "Incoming" && isB2B == true) ||
                            (transaction_type == "Outgoing" &&
                                isB2B == false)) &&
                        transaction_status == "approved"
                    );
                default:
                    break;
            }
        },
        startMakingRequestChanges(method) {
            switch (method) {
                case "Approve_and_Pay":
                    this.confirmation.isOpen = true;
                    this.confirmation.message =
                        "Are you sure you want to approve and pay for this transaction?";
                    this.confirmation.title = "Approve and Pay Transaction";
                    this.confirmation.method = () =>
                        this.acceptAndPayTransaction();
                    break;

                case "Approve":
                    this.confirmation.isOpen = true;
                    this.confirmation.message =
                        "Are you sure you want to approve this transaction?";
                    this.confirmation.title = "Approve Transaction";
                    this.confirmation.method = () => this.acceptTransaction();
                    break;

                case "Cancel":
                    this.confirmation.isOpen = true;
                    this.confirmation.message =
                        "Are you sure you want to cancel this transaction?";
                    this.confirmation.title = "Cancel Transaction";
                    this.confirmation.method = () => {
                        const reason = "User decided to cancel";
                        this.rejectTransaction(reason);
                    };
                    break;

                case "Pay":
                    this.confirmation.isOpen = true;
                    this.confirmation.message =
                        "Are you sure you want to complete the payment for this transaction?";
                    this.confirmation.title = "Pay Transaction";
                    this.confirmation.method = () => {
                        this.payTransaction();
                    };
                    break;

                default:
                    this.confirmation.isOpen = false;
                    this.confirmation.message = "";
                    this.confirmation.title = "";
                    this.confirmation.method = null;
                    break;
            }

            this.confirmation.isOpen = true;
        },
        confirmAction() {
            if (this.confirmation.method) {
                this.confirmation.method();
            }
            this.confirmation.isOpen = false;
        },
        cancelMakingRequest() {
            this.confirmation.isOpen = false;
            this.confirmation.message = "";
            this.confirmation.title = "";
            this.confirmation.method = null;
        },
        async startAgreementPdf(action) {
            switch (action) {
                case "print":
                    const printWindow = window.open(
                        `/transaction/view-agreement/print/${this.transactionStore.singleTransaction.id}`,
                        "_blank"
                    );
                    printWindow.addEventListener("load", () => {
                        printWindow.print();
                    });
                    break;
                case "view":
                    window.location.href = `/transaction/view-agreement/${this.transactionStore.singleTransaction.id}`;
                    break;
                case "download":
                    window.location.href = `/transaction/download-agreement/${this.transactionStore.singleTransaction.id}`;
                    break;
                case "share":
                    if (navigator.share) {
                        try {
                            await navigator.share({
                                title: "Agreement Preview",
                                text: "Check out this agreement.",
                                url: `/transaction/view-agreement/${this.transactionStore.singleTransaction.id}`,
                            });
                            console.log("Shared successfully");
                        } catch (error) {
                            console.error("Error sharing", error);
                        }
                    } else {
                        console.log(
                            "Web Share API is not supported in your browser."
                        );
                    }
                    break;
                case "pdf-view":
                    window.location.href = `/transaction/pdf-preview/${this.transactionStore.singleTransaction.id}`;
            }
        },
        async startReceipt(action) {
            switch (action) {
                case "print":
                    const printWindow = window.open(
                        `/transaction/view-receipt/print/${this.transactionStore.singleTransaction.id}`,
                        "_blank"
                    );
                    printWindow.addEventListener("load", () => {
                        printWindow.print();
                    });
                    break;
                default:
                    break;
            }
        },
        getBadgeSeverity(status) {
            switch (status) {
                case "pending":
                    return "info";
                case "approved":
                    return "primary";
                case "paid":
                    return "success";
                case "dispatched":
                    return "warning";
                case "completed":
                    return "success";
                case "canceled":
                    return "danger";
                case "return":
                    return "danger";
                default:
                    return "secondary";
            }
        },
        goBack() {
            window.history.back();
        },
    },
};
</script>

<template>
    <Head title="Transaction" />
    <AlertNotification
        :open="
            transactionStore.success != null || transactionStore.error != null
        "
        :message="
            transactionStore.success != null
                ? transactionStore.success
                : '' || transactionStore.error != null
                ? transactionStore.error
                : ''
        "
        :status="transactionStore.success ? 'success' : 'error'"
    />
    <ConfirmationModal
        :isOpen="confirmation.isOpen"
        :title="confirmation.title"
        :message="confirmation.message"
        @confirm="confirmAction"
        @close="cancelMakingRequest"
    />
    <AuthenticatedLayout>
        <div class="bg-white px-4 rounded-lg">
            <div class="flex justify-between">
                <div class="flex items-center gap-2">
                    <i
                        @click="goBack"
                        class="pi pi-arrow-left p-3 bg-slate-800 text-white rounded-full shadow-lg cursor-pointer hover:bg-slate-600"
                    ></i>

                    <h1 class="text-2xl font-semibold">
                        Transaction #{{ transactionStore.singleTransaction.id }}
                    </h1>
                </div>

                <div class="flex gap-1">
                    <SplitButtonSelectCustom
                        v-if="
                            ['leasing', 'borrowing'].includes(
                                transactionStore.singleTransaction.type
                            )
                        "
                        class="flex h-fit"
                        :SelectItems="SelectItems"
                        :defaulItem="defaulItem"
                    />
                    <SplitButtonSelectCustom
                        v-else
                        class="flex h-fit"
                        :SelectItems="selectedReceiptItems"
                        :defaulItem="defaultSelectedReceiptItem"
                    />
                    <div class="flex gap-1">
                        <!--  -->
                        <!-- <PrimaryButton
                v-if="buttonDisplay('Approve_and_Pay')"
                @click="startMakingRequestChanges('Approve_and_Pay')"
                class="bg-orange-600 hover:bg-orange-500 active:bg-orange-500 focus:bg-orange-500 h-10"
                >Approve and Pay</PrimaryButton
            > -->
                        <PrimaryButton
                            v-if="buttonDisplay('Approve')"
                            @click="startMakingRequestChanges('Approve')"
                            class="bg-yellow-500 hover:bg-yellow-600 focus:bg-yellow-600 active:bg-yellow-600 h-10"
                            >Approve</PrimaryButton
                        >
                        <PrimaryRoseButton
                            v-if="buttonDisplay('Cancel')"
                            @click="startMakingRequestChanges('Cancel')"
                            class="h-10"
                            >Cancel
                        </PrimaryRoseButton>

                        <PrimaryButton
                            v-if="buttonDisplay('Pay')"
                            @click="startMakingRequestChanges('Pay')"
                            class="bg-green-600 hover:bg-green-800 active:bg-green-600 focus:bg-green-600 h-10"
                        >
                            Pay
                        </PrimaryButton>
                    </div>
                </div>
            </div>
            <div class="flex justify-between mt-5">
                <div>
                    <p>
                        <strong>Type:</strong>
                        <span class="capitalize">
                            {{ transactionStore.singleTransaction.type }}
                        </span>
                    </p>
                    <p>
                        <strong>Status:</strong>
                        <Tag
                            :severity="
                                getBadgeSeverity(
                                    transactionStore.singleTransaction.status
                                )
                            "
                            :value="transactionStore.singleTransaction.status"
                            style="text-transform: capitalize"
                        />
                    </p>
                </div>
                <div class="text-right">
                    <p>
                        <strong>Total Price:</strong>
                        {{
                            convertCurrency(
                                transactionStore.singleTransaction.totalPrice
                            )
                        }}
                    </p>
                    <p>
                        <strong>Transaction Type:</strong>
                        {{
                            transactionStore.singleTransaction.transaction_type
                        }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Initiator & Receiver Section -->
        <div class="flex flex-col md:flex-row gap-6 mt-5 mb-3">
            <!-- Initiator Card -->
            <div class="bg-gray-50 p-4 rounded-lg shadow-sm flex-grow">
                <span
                    class="flex items-center gap-3 mb-3 w-full border border-t-0 border-s-0 border-e-0"
                >
                    <h2 class="text-lg font-medium">Initiator</h2>
                </span>
                <p>
                    <strong>Business Name:</strong>
                    {{
                        transactionStore.singleTransaction.initiator
                            ?.business_name
                    }}
                </p>
                <p>
                    <strong>Business Email:</strong>
                    {{ transactionStore.singleTransaction.initiator?.email }}
                </p>
                <p>
                    <strong>Phone Number:</strong>
                    {{
                        transactionStore.singleTransaction.initiator
                            ?.phone_number
                    }}
                </p>
            </div>

            <!-- Receiver Card -->
            <div
                class="bg-gray-50 p-4 rounded-lg shadow-sm flex-grow"
                v-if="transactionStore.singleTransaction.receiver_business"
            >
                <span
                    class="flex items-center gap-3 mb-3 w-full border border-t-0 border-s-0 border-e-0"
                >
                    <h2 class="text-lg font-medium">Receiver</h2>
                    <Badge severity="warn" size="small">Business</Badge>
                </span>
                <p>
                    <strong>Business Name:</strong>
                    {{
                        transactionStore.singleTransaction.receiver_business
                            ?.business_name
                    }}
                </p>

                <p>
                    <strong>Business Email:</strong>
                    {{
                        transactionStore.singleTransaction.receiver_business
                            ?.email
                    }}
                </p>

                <p>
                    <strong>Phone Number:</strong>
                    {{
                        transactionStore.singleTransaction.receiver_business
                            ?.phone_number
                    }}
                </p>
            </div>

            <div
                class="bg-gray-50 p-4 rounded-lg shadow-sm flex-grow"
                v-if="transactionStore.singleTransaction.receiver_customer"
            >
                <span
                    class="flex items-center gap-3 mb-3 w-full border border-t-0 border-s-0 border-e-0"
                >
                    <h2 class="text-lg font-medium">Receiver</h2>
                    <Badge severity="info" size="small">Customer</Badge>
                </span>
                <p>
                    <strong>Full Name:</strong>
                    {{
                        transactionStore.singleTransaction.receiver_customer
                            ?.full_names
                    }}
                </p>

                <p>
                    <strong>Customer Email:</strong>
                    {{
                        transactionStore.singleTransaction.receiver_customer
                            ?.email
                    }}
                </p>

                <p>
                    <strong>Phone Number:</strong>
                    {{
                        transactionStore.singleTransaction.receiver_customer
                            ?.phone_number
                    }}
                </p>
            </div>
        </div>

        <!-- Details Section -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-8">
            <h2 class="text-lg font-medium mb-4">Details</h2>
            <p>
                <strong>Shipping Fees:</strong> ${{
                    transactionStore.singleTransaction.details?.shipping_fees
                }}
            </p>
        </div>

        <!-- Items Section -->
        <div class="p-4 max-h-[40vh] h-fit rounded-lg shadow-sm mb-8">
            <h2 class="text-lg font-medium mb-4">Items</h2>
            <div class="relative max-h-[35vh] h-fit overflow-scroll">
                <table class="w-full table-auto text-left overflow-x-auto">
                    <thead class="border-b sticky top-0 w-full bg-slate-600">
                        <tr>
                            <th class="px-4 py-2">Item Name</th>
                            <th class="px-4 py-2">Quantity</th>
                            <th class="px-4 py-2">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in transactionStore.singleTransaction
                                .items"
                            :key="item.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-2">
                                {{ item.item?.item_name }}
                            </td>
                            <td class="px-4 py-2">{{ item.quantity }}</td>
                            <td class="px-4 py-2">
                                {{ convertCurrency(item.price) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
    </AuthenticatedLayout>
</template>

<style scoped>
/* Minimalist status styles */
.status-pending {
    color: #f97316;
}

.status-completed {
    color: #22c55e;
}

.bg-white {
    background-color: #ffffff;
}

.bg-gray-50 {
    background-color: #f9fafb;
}

.shadow-sm {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
</style>
