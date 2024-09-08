<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                "type" => 'required|string',
                "status" => 'required|string',
                "initiator_id" => 'required|exists:business,business_id',
                "receiver_business_id" => 'nullable|exists:business,business_id',
                "receiver_customer_id" => 'nullable|exists:customer,id',
                "transaction_items" => 'required|array',
                "transaction_items.*.item_id" => 'required|exists:items,id',
                "transaction_items.*.quantity" => 'required|numeric|min:0',
                "transaction_items.*.price" => 'required|numeric|min:0',
                "lease_start_date" => 'nullable|date',
                "lease_end_date" => 'nullable|date|after_or_equal:lease_start_date',
                "transaction_details" => 'nullable|array',
                "transaction_details.due_date" => 'nullable|date',
                "transaction_details.return_date" => 'nullable|date|after_or_equal:transaction_details.due_date',
                "transaction_details.late_fees" => 'nullable|numeric|min:0',
                "transaction_details.damage_fees" => 'nullable|numeric|min:0',
                "transaction_details.shipping_fees" => 'nullable|numeric|min:0',
            ]);

            $new_transaction = Transaction::create($request->only([
                'type',
                'status',
                'initiator_id',
                'receiver_business_id',
                'receiver_customer_id',
                'lease_start_date',
                'lease_end_date'
            ]));

            foreach ($request->input('transaction_items') as $item) {
                TransactionItem::create([
                    'transaction_id' => $new_transaction->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            if ($transaction_details = $request->input('transaction_details')) {
                $transaction_details['transaction_id'] = $new_transaction->id;
                TransactionDetail::create($transaction_details);
            }

            DB::commit();

            $actual_transaction = Transaction::where('id', $new_transaction->id)
                ->with('details', 'initiator', 'receiver_business', 'receiver_customer', 'items')
                ->first();

            return response()->json([
                "error" => false,
                "message" => "Transaction created successfully",
                "data" => $actual_transaction,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage()
            ]);
        }
    }

    public function getTransaction($business_id, Request $request)
    {
        try {
            $validatedData = $request->validate([
                "incoming" => 'required|boolean',
                "status" => 'nullable|string',
                "type" => 'required|string',
                "initiator_id" => 'nullable|exists:business,business_id',
                "receiver_business_id" => 'nullable|exists:business,business_id',
                "items_count" => "nullable|integer"
            ]);

            $transactionsQuery = Transaction::with('details', 'initiator', 'receiver_business', 'receiver_customer', 'items');
            if ($request->input('status')) {
                $transactionsQuery->when($request->input('status'), function ($query, $status) {
                    $query->where('status', $status);
                });
            }

            if ($validatedData['incoming']) {
                $transactionsQuery->where('receiver_business_id', $business_id);
            } else {
                $transactionsQuery->where('initiator_id', $business_id);
            }

            $itemsCount = $validatedData['items_count'] ?? 20;
            $transactions = $transactionsQuery
                ->where('type', $validatedData['type'])
                ->where('deleted', false)
                ->paginate($itemsCount);

            return response()->json([
                "error" => false,
                "message" => "Transactions retrieved successfully",
                "data" => $transactions,
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

    public function updateTransaction($transaction_id, Request $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validate([
                "type" => 'nullable|string',
                "status" => 'nullable|string',
                "initiator_id" => 'nullable|exists:business,business_id',
                "receiver_business_id" => 'nullable|exists:business,business_id',
                "receiver_customer_id" => 'nullable|exists:customer,id',
                "transaction_items" => 'nullable|array',
                "transaction_items.*.item_id" => 'required_with:transaction_items|exists:items,id',
                "transaction_items.*.quantity" => 'required_with:transaction_items|numeric|min:0',
                "transaction_items.*.price" => 'required_with:transaction_items|numeric|min:0',
                "lease_start_date" => 'nullable|date',
                "lease_end_date" => 'nullable|date|after_or_equal:lease_start_date',
                "transaction_details" => 'nullable|array',
                "transaction_details.due_date" => 'nullable|date',
                "transaction_details.return_date" => 'nullable|date|after_or_equal:transaction_details.due_date',
                "transaction_details.late_fees" => 'nullable|numeric|min:0',
                "transaction_details.damage_fees" => 'nullable|numeric|min:0',
                "transaction_details.shipping_fees" => 'nullable|numeric|min:0',
            ]);

            $transaction = Transaction::findOrFail($transaction_id);

            $transaction->update([
                "type" => $validatedData['type'] ?? $transaction->type,
                "status" => $validatedData['status'] ?? $transaction->status,
                "initiator_id" => $validatedData['initiator_id'] ?? $transaction->initiator_id,
                "receiver_business_id" => $validatedData['receiver_business_id'] ?? $transaction->receiver_business_id,
                "receiver_customer_id" => $validatedData['receiver_customer_id'] ?? $transaction->receiver_customer_id,
                "lease_start_date" => $validatedData['lease_start_date'] ?? $transaction->lease_start_date,
                "lease_end_date" => $validatedData['lease_end_date'] ?? $transaction->lease_end_date,
            ]);

            if (isset($validatedData['transaction_items'])) {
                TransactionItem::where('transaction_id', $transaction_id)->delete();

                foreach ($validatedData['transaction_items'] as $item) {
                    TransactionItem::create([
                        "transaction_id" => $transaction->id,
                        "item_id" => $item['item_id'],
                        "quantity" => $item['quantity'],
                        "price" => $item['price'],
                    ]);
                }
            }

            if (isset($validatedData['transaction_details'])) {
                $transactionDetail = TransactionDetail::updateOrCreate(
                    ['transaction_id' => $transaction->id],
                    $validatedData['transaction_details']
                );
            }

            DB::commit();

            $updatedTransaction = Transaction::where('id', $transaction->id)
                ->with('details', 'initiator', 'receiver_business', 'receiver_customer', 'items')
                ->first();

            return response()->json([
                "error" => false,
                "message" => "Transaction updated successfully",
                "data" => $updatedTransaction,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
    public function deleteTransaction($transaction_id)
    {
        try {
            $transaction = Transaction::findOrFail($transaction_id);

            $transaction->update([
                'deleted' => true
            ]);

            return response()->json([
                'error' => false,
                'message' => 'Transaction deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Transaction not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }
}