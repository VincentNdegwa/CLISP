<?php

namespace App\Http\Controllers;

use App\Models\ResourceItem;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionItem;
use App\Services\BorrowingWorkflow;
use App\Services\LeasingWorkflow;
use App\Services\NormalSaleWorkflow;
use App\Services\PurchaseWorkflow;
use App\Services\TransactionFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function create($business_id, Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                "type" => 'required|string',
                "status" => 'required|string',
                "initiator_id" => 'required|exists:business,business_id',
                "receiver_business_id" => 'nullable|exists:business,business_id',
                "receiver_customer_id" => 'nullable|exists:customers,id',
                "transaction_items" => 'required|array',
                "transaction_items.*.item_id" => 'required|exists:resource_item,id',
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
                $transactionModel = ResourceItem::find($item['item_id']);
                $newQuantity = $transactionModel->quantity - $item['quantity'];
                $transactionModel->update([
                    'quantity' => $newQuantity,
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

            $actual_transaction->totalPrice = $actual_transaction->items->sum(function ($item) {
                return $item->quantity * $item->price;
            });
            if ($actual_transaction->initiator && $actual_transaction->initiator->business_id == $business_id) {
                $actual_transaction->transaction_type = 'Outgoing';
            }

            if ($actual_transaction->receiver_business && $actual_transaction->receiver_business->business_id == $business_id) {
                $actual_transaction->transaction_type = "Incoming";
            }

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
                "incoming" => 'string',
                "status" => 'nullable|string',
                "isB2B" => 'required|boolean',
                "type" => 'required|string',
                "items_count" => "nullable|integer",
                "page" => 'integer',
            ]);

            $transactionsQuery = Transaction::with('details', 'initiator:business_id,business_name', 'receiver_business:business_id,business_name', 'receiver_customer', 'items');

            if ($validatedData['isB2B'] === true) {
                // B2B transactions: where receiver_business exists and receiver_customer is null
                $transactionsQuery->whereNotNull('receiver_business_id')
                    ->whereNull('receiver_customer_id');
            } elseif ($validatedData['isB2B'] === false) {
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

            $transactions = $transactionsQuery
                ->where('type', $validatedData['type'])
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
                "message" => "Transactions retrieved successfully",
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

    public function updateTransaction($business_id, $transaction_id, Request $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validate([
                "type" => 'nullable|string',
                "status" => 'nullable|string',
                "initiator_id" => 'nullable|exists:business,business_id',
                "receiver_business_id" => 'nullable|exists:business,business_id',
                "receiver_customer_id" => 'nullable|exists:customers,id',
                "transaction_items" => 'nullable|array',
                "transaction_items.*.item_id" => 'required_with:transaction_items|exists:resource_item,id',
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
            $updatedTransaction->totalPrice = $updatedTransaction->items->sum(function ($item) {
                return $item->quantity * $item->price;
            });
            if ($updatedTransaction->initiator && $updatedTransaction->initiator->business_id == $business_id) {
                $updatedTransaction->transaction_type = 'Outgoing';
            }

            if ($updatedTransaction->receiver_business && $updatedTransaction->receiver_business->business_id == $business_id) {
                $updatedTransaction->transaction_type = "Incoming";
            }

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
    public function deleteTransaction($business_id, $transaction_id)
    {
        try {
            $to_delete = Transaction::find($transaction_id)->delete();

            if ($to_delete) {
                $transaction = Transaction::find($transaction_id);

                return response()->json([
                    'error' => false,
                    'message' => 'Transaction idDeleted successfully',
                    'trans' => $transaction
                ], 200);
            } else {
                return response()->json([
                    "error" => true,
                    "message" => "No transaction was updated or found"
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    public function viewTransaction($business_id, $transaction_id)
    {
        try {
            $transaction = Transaction::with('details', 'initiator:business_id,business_name,email,phone_number,location', 'receiver_business:business_id,business_name,email,phone_number,location', 'receiver_customer')
                ->with([
                    'items' => function ($query) {
                        $query->with('item');
                    }
                ])
                ->where(function ($query) use ($business_id) {
                    $query->where('initiator_id', $business_id)
                        ->orWhere('receiver_business_id', $business_id);
                })
                ->where('id', $transaction_id)
                ->first();

            $transaction->totalPrice = $transaction->items->sum(function ($item) {
                return $item->quantity * $item->price;
            });
            if ($transaction->initiator && $transaction->initiator->business_id == $business_id) {
                $transaction->transaction_type = 'Outgoing';
            }

            if ($transaction->receiver_business && $transaction->receiver_business->business_id == $business_id) {
                $transaction->transaction_type = "Incoming";
            }

            if ($transaction->receiver_business != null && $transaction->receiver_customer == null) {
                $transaction->isB2B = true;
            } else if ($transaction->receiver_business == null && $transaction->receiver_customer != null) {
                $transaction->isB2B = false;
            }

            return response()->json([
                "error" => false,
                "data" => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage(),
                "data" => []
            ]);
        }
    }

    public function acceptTransaction($business_id, $transaction, Request $request)
    {
        $transaction_type = $request->input('type');
        $workflow = $this->getWorkflow($transaction, $transaction_type);
        return $workflow->acceptTransaction();
    }

    public function rejectTransaction($business_id, $transaction, Request $request)
    {
        $transaction_type = $request->input('type');
        $workflow = $this->getWorkflow($transaction, $transaction_type);
        return $workflow->rejectTransaction("reason");
    }
    public function payTransaction($business_id, $transaction, Request $request)
    {
        $transaction_type = $request->input('type');
        $workflow = $this->getWorkflow($transaction, $transaction_type);
        return $workflow->payTransaction();
    }
    public function acceptAndPayTransaction($business_id, $transaction, Request $request)
    {
        $transaction_type = $request->input('type');
        $workflow = $this->getWorkflow($transaction, $transaction_type);
        return $workflow->payTransaction();
    }
    public function closeTransaction($business_id, $transaction, Request $request)
    {
        $transaction_type = $request->input('type');
        $workflow = $this->getWorkflow($transaction, $transaction_type);
        return $workflow->closeTransaction();
    }

    private function getWorkflow($transaction, $transaction_type): TransactionFlow
    {
        $workflow = new NormalSaleWorkflow($transaction);
        $transaction_type = $transaction_type;

        switch ($transaction_type) {
            case 'leasing':
                $workflow = new LeasingWorkflow($transaction);
                break;
            case 'purchase':
                $workflow = new PurchaseWorkflow($transaction);
                break;
            case 'borrowing':
                $workflow = new BorrowingWorkflow($transaction);
                break;
            default:
                $workflow = new NormalSaleWorkflow($transaction);
                break;
        }
        return $workflow;
    }

    public function retrieveTransaction($transactionId, $isAgreemet)
    {
        $transactionsQuery = Transaction::where('id', $transactionId);

        if ($isAgreemet) {
            $transactionsQuery->whereIn('type', ['leasing', 'borrowing']);
        } else {
            $transactionsQuery->whereIn('type', ['purchase', 'sale']);
        }


        $transaction = $transactionsQuery->with([
            'initiator:business_id,business_name,email,phone_number,location',
            'receiver_business:business_id,business_name,email,phone_number,location',
            'receiver_customer',
            'details',
            'items' => function ($query) {
                $query->with('item');
            }
        ])->first();
        $imagePath = public_path('images/CLISP-logo.png');

        // Read the image file and encode it to base64
        $imageData = base64_encode(file_get_contents($imagePath));

        // Create the base64 image source
        $imageBase64 = 'data:image/png;base64,' . $imageData;
        $transaction->logo = $imageBase64;


        return $transaction;
    }

    public function downloadAgreement($transactionId)
    {
        $transaction = $this->retrieveTransaction($transactionId, true);


        $pdf = Pdf::loadView('agreement', compact('transaction'));
        if (!$transaction) {
            return redirect()->route('not-found');
        }
        return $pdf->download('agreement.pdf');
    }

    public function previewAgreement($transactionId)
    {
        $transaction = $this->retrieveTransaction($transactionId, true);
        if (!$transaction) {
            return redirect()->route('not-found');
        }

        return view('PreviewAgreement', compact('transaction'));
    }

    public function printPreviewAgreement($transactionId)
    {
        $transaction = $this->retrieveTransaction($transactionId, true);
        if (!$transaction) {
            return redirect()->route('not-found');
        }
        $pdf = Pdf::loadView('agreement', compact('transaction'));

        // return $pdf->stream('agreement.pdf');

        return view('agreement', compact('transaction'));
    }

    public function pdfPreviewAgreement($transactionId)
    {
        $transaction = $this->retrieveTransaction($transactionId, true);
        if (!$transaction) {
            return redirect()->route('not-found');
        }
        $pdf = Pdf::loadView('agreement', compact('transaction'));

        return $pdf->stream('agreement.pdf');
    }

    public function printPreviewReceipt($transactionId)
    {
        $transaction = $this->retrieveTransaction($transactionId, false);
        // if (!$transaction) {
        //     return redirect()->route('not-found');
        // }
        $pdf = Pdf::loadView('receipt', compact('transaction'));

        // return $pdf->stream('agreement.pdf');

        return view('receipt', compact('transaction'));
    }
}
