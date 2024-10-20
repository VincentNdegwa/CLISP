<template>
    <div>
        <h2>Pay with Credit or Debit Card</h2>
        <form @submit.prevent="handleSubmit">
            <!-- Card Element where users enter their card details -->
            <div id="card-element" class="my-4"></div>
            <div id="card-errors" class="text-red-500"></div>

            <!-- Button to submit payment -->
            <button
                type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                Pay {{ formatCurrency(amount) }}
            </button>
        </form>
    </div>
</template>

<script>
import { loadStripe } from "@stripe/stripe-js";

export default {
    props: {
        amount: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            stripe: null,
            cardElement: null,
        };
    },
    async mounted() {
        // Load Stripe and initialize Elements
        this.stripe = await loadStripe("your-public-key-here"); // Use your actual Stripe public key

        const elements = this.stripe.elements();
        this.cardElement = elements.create("card");
        this.cardElement.mount("#card-element");

        // Handle validation errors
        this.cardElement.on("change", (event) => {
            const cardErrors = document.getElementById("card-errors");
            if (event.error) {
                cardErrors.textContent = event.error.message;
            } else {
                cardErrors.textContent = "";
            }
        });
    },
    methods: {
        formatCurrency(amount) {
            return (amount / 100).toFixed(2); // Stripe works with cents
        },
        async handleSubmit() {
            // Call your backend to create a PaymentIntent
            const response = await fetch("/create-payment-intent", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ amount: this.amount }),
            });

            const { clientSecret } = await response.json();

            // Confirm the payment using Stripe.js
            const { paymentIntent, error } =
                await this.stripe.confirmCardPayment(clientSecret, {
                    payment_method: {
                        card: this.cardElement,
                    },
                });

            if (error) {
                console.error("Payment failed:", error.message);
                alert(`Payment failed: ${error.message}`);
            } else if (paymentIntent.status === "succeeded") {
                console.log("Payment successful:", paymentIntent);
                alert("Payment successful!");
                // You can now handle the post-payment actions like updating the database, etc.
            }
        },
    },
};
</script>

<style scoped>
#card-element {
    border: 1px solid #e1e1e1;
    padding: 10px;
    border-radius: 4px;
}
</style>
