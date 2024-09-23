<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LogisticController extends Controller
{
    public function getLogistics($business_id, Request $request)
    {

        try {
            $validatedData = $request->validate([
                "incoming" => 'required|string',
                "status" => 'nullable|string',
                "isB2B" => 'required|string',
                "type" => 'nullable|string',
                "items_count" => "nullable|integer",
                "page" => 'integer',
            ]);

            $transactionsQuery = Transaction::with('details', 'initiator:business_id,business_name', 'receiver_business:business_id,business_name', 'receiver_customer', 'items');

            if ($validatedData['isB2B'] === 'business') {
                // B2B transactions: where receiver_business exists and receiver_customer is null
                $transactionsQuery->whereNotNull('receiver_business_id')
                    ->whereNull('receiver_customer_id');
            } elseif ($validatedData['isB2B'] === 'customer') {
                // B2C transactions: where receiver_customer exists and receiver_business is null
                $transactionsQuery->whereNotNull('receiver_customer_id')
                    ->whereNull('receiver_business_id');
            }
            if ($request->input('status') != 'all') {
                $transactionsQuery->when($request->input('status'), function ($query, $status) {
                    $query->where('status', $status);
                });
            }

            if ($validatedData['incoming'] == 'incoming') {
                $transactionsQuery->where('receiver_business_id', $business_id);
            } else if ($validatedData['incoming'] == 'outgoing') {
                $transactionsQuery->where('initiator_id', $business_id);
            } else {
                $transactionsQuery->where(function ($query) use ($business_id) {
                    $query->where('initiator_id', $business_id)
                        ->orWhere('receiver_business_id', $business_id);
                });
            }


            $itemsCount = $validatedData['items_count'] ?? 20;
            if (isset($validatedData['type']) || $validatedData['type'] != null) {
                $transactionsQuery
                    ->where('type', $validatedData['type']);
            } else {
                $transactionsQuery->whereIn("type", ['leasing', 'borrowing']);
            }
            $transactions = $transactionsQuery

                ->orderBy('created_at', 'DESC')
                ->paginate($itemsCount, ["*"], 'page', $request->input('page', 1));

            foreach ($transactions as $transaction) {
                $transaction->totalPrice = $transaction->items->sum(function ($item) {
                    return $item->quantity * $item->price;
                });

                if ($transaction->initiator && $transaction->initiator->business_id == $business_id) {
                    $transaction->transaction_type = 'Outgoing';
                }

                if ($transaction->receiver_business && $transaction->receiver_business->business_id == $business_id) {
                    $transaction->transaction_type = "Incoming";
                }
            }
            return response()->json([
                "error" => false,
                "message" => "Logistic retrieved successfully",
                "data" => $transactions
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}
