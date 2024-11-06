<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\NonShippableTransactionWorkflow;
use Illuminate\Validation\ValidationException;

class PaymentsController extends Controller
{
    public function createResponse(bool $error, string $message, $data = null, $errors = null)
    {
        return response()->json([
            'error' => $error,
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function createPayment(Request $request)
    {
        try {
            $validatedData = $request->validate([
                "payer_name" => 'nullable|string',
                "payer_email" => 'nullable|string|email',
                "payment_method" => "required|string",
                "payment_reference" => 'nullable|string',
                "paid_amount" => 'required|numeric|min:0',
                "transaction_id" => 'required|integer|exists:transactions,id',
                "remaining_balance" => 'nullable|numeric|min:0',
                "payer_id" => 'required|integer',
                "payee_business" => 'required|integer|exists:business,business_id',
                "currency_code" => "required|string|size:3",
                "business_id" => "required|exists:business,business_id",
                "isB2B" => "required|boolean"
            ]);

            $payment = Payment::create([
                "payer_name" => $validatedData['payer_name'] ?? null,
                "payer_email" => $validatedData['payer_email'] ?? null,
                "payment_method" => $validatedData['payment_method'],
                "payment_reference" => $validatedData['payment_reference'] ?? null,
                "paid_amount" => $validatedData['paid_amount'],
                "transaction_id" => $validatedData['transaction_id'],
                "remaining_balance" => $validatedData['remaining_balance'] ?? 0,
                "payer_id" => $validatedData['payer_id'],
                "payee_business" => $validatedData['payee_business'],
                "currency_code" => $validatedData['currency_code'],
                "isB2B" => $validatedData["isB2B"],
            ]);

            $transactionFlow = new NonShippableTransactionWorkflow($validatedData['business_id'], $validatedData['transaction_id']);
            $transactionFlow->payTransaction($validatedData['payment_method']);
            return $this->createResponse(false, 'Payment created successfully', [
                "payment" => $payment,
                "transaction" => $transactionFlow->getFullTransaction()
            ]);
        } catch (ValidationException $e) {
            return $this->createResponse(true, 'Validation error', null, $e->errors());
        } catch (\Exception $e) {
            return $this->createResponse(true, 'Failed to create payment', null, $e->getMessage());
        }
    }
}
