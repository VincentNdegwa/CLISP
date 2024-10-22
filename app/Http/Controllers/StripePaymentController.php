<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Account;
use Stripe\PaymentLink;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripePaymentController extends Controller
{
    function setStripeApiKey()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
    }
    public function createResponse(bool $error, string $message, $data = null, $errors = null)
    {
        return response()->json([
            'error' => $error,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ]);
    }

    public function createStripeAccountForBusiness($email)
    {
        $this->setStripeApiKey();

        try {
            $account = Account::create([
                'type' => "express",
                'email' => $email,
                'country' => "US",
                'business_type' => "individual",
            ]);

            return $this->createResponse(false, 'Stripe account created successfully.', $account->id);
        } catch (\Exception $e) {
            return $this->createResponse(true, 'Failed to create Stripe account.', null, $e->getMessage());
        }
    }

    public function createPaymentLink($items, $payeeBusiness, $payerDetails)
    {
        $this->setStripeApiKey();

        if (!is_array($items)) {
            return $this->createResponse(true, "Items should be an array.");
        }

        $list_items = [];

        foreach ($items as $item) {
            $list_items[] = [
                'price_data' => [
                    'currency' => 'USD',
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                    'unit_amount' => $item['price'] * 100,
                ],
                'quantity' => $item['quantity'],
            ];
        }

        if (!isset($payeeBusiness->business_stripe_id)) {
            return $this->createResponse(true, "Invalid payee business data.");
        }
        if (!isset($payerDetails->email)) {
            return $this->createResponse(true, "Invalid payer details.");
        }

        try {

            $stripeClient = new StripeClient(env('STRIPE_SECRET'));
            // $payeeBusiness->business_stripe_id

            $response = $stripeClient->checkout->sessions->create([
                'mode' => 'payment',
                'customer_email' => $payerDetails->email,
                'line_items' => $list_items,
                'success_url' => route('success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('cancel'),
                'payment_intent_data' => [
                    'transfer_data' => [
                        'destination' => "acct_1QC60pGptDGJ881g",
                    ],
                ],
            ]);


            return $this->createResponse(false, 'Payment link created successfully.', $response);
        } catch (\Exception $e) {
            return $this->createResponse(true, 'Failed to create payment link.', null, $e->getMessage());
        }
    }
}
