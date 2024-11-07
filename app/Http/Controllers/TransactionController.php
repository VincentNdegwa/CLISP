<?php

namespace App\Http\Controllers;

use App\Mail\RequestApprovalMail;
use App\Models\ExchangeRate;
use App\Models\ItemBusiness;
use App\Models\ResourceItem;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionItem;
use App\Models\TransactionItemHistory;
use App\Services\Transactions\NonShipping\NormalSaleWorkflow;
use App\Services\Transactions\Shipping\BorrowingWorkflow;
use App\Services\Transactions\Shipping\LeasingWorkflow;
use App\Services\Transactions\Shipping\PurchaseWorkflow;
use App\Services\TransactionFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
            }

            if ($transaction_details = $request->input('transaction_details')) {
                $transaction_details['transaction_id'] = $new_transaction->id;
                TransactionDetail::create($transaction_details);
            }

            DB::commit();
            $transaction = $this->getFullTransaction($business_id, $new_transaction->id);

            if ($transaction->receiver_business && isset($transaction->receiver_business)) {

                Mail::to($transaction->receiver_business->email)
                    ->queue(new RequestApprovalMail($transaction, $transaction->totalPrice, $transaction->id));
            }

            return response()->json([
                "error" => false,
                "message" => "Transaction created successfully",
                "data" => $transaction,
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

    public function modifyTransaction($transaction, $business_id)
    {


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
        $from_code = $transaction->initiator->currency_code;
        $to_code = ($transaction->transaction_type === "Incoming" && $transaction->isB2B)
            ? $transaction->receiver_business->currency_code
            : $from_code;

        $setItemPrices = function ($item) use ($from_code, $to_code) {
            $item->unConvertedUnitPrice = (float)$item->price;

            $item->price = ($from_code !== $to_code)
                ? (float)round($this->convertCurrency($item->price, $from_code, $to_code), 2)
                : (float)$item->price;

            $item->usdPrice = ($to_code === 'USD')
                ? (float)$item->price
                : (float)round($this->convertCurrency($item->price, $to_code, 'USD'), 2);
        };

        $transaction->items->each($setItemPrices);

        $transaction->totalPrice = $transaction->items->sum(fn($item) => $item->quantity * $item->price);
        $transaction->totalUsdPrice = $transaction->items->sum(fn($item) => $item->quantity * $item->usdPrice);


        return $transaction;
    }
    public function convertCurrency($from_amount, $from_currency_code, $to_currency_code)
    {
        $rates = ExchangeRate::whereIn('target_currency', [$from_currency_code, $to_currency_code])->get();

        $from_rate = null;
        $to_rate = null;

        foreach ($rates as $rate) {
            if ($rate->target_currency === $from_currency_code) {
                $from_rate = $rate->rate;
            } elseif ($rate->target_currency === $to_currency_code) {
                $to_rate = $rate->rate;
            }
        }
        if ($from_rate === null || $to_rate === null) {
            throw new Exception('Exchange rate not found for one or both currencies.');
        }
        $base_amount = $from_amount / $from_rate;
        $converted_amount = $base_amount * $to_rate;
        return $converted_amount;
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

            $transactionsQuery = Transaction::with([
                'details',
                'initiator:business_id,business_name,email,phone_number,location,currency_code',
                'receiver_business:business_id,business_name,email,phone_number,location,currency_code',
                'receiver_customer',
                'items' => function ($query) {
                    $query->with('item:id,item_name,description,item_image');
                }
            ]);

            if ($validatedData['isB2B'] === true) {
                $transactionsQuery->whereNotNull('receiver_business_id')
                    ->whereNull('receiver_customer_id');
            } elseif ($validatedData['isB2B'] === false) {
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
                $transaction = $this->modifyTransaction($transaction, $business_id);
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


            $updatedTransaction = $this->getFullTransaction($business_id, $transaction->id);

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
            $transaction = $this->getFullTransaction($business_id, $transaction_id);
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


    public function getFullTransaction($business_id, $transaction_id)
    {
        try {
            $transaction = Transaction::with([
                'details',
                'initiator:business_id,business_name,email,phone_number,location,currency_code',
                'receiver_business:business_id,business_name,email,phone_number,location,currency_code',
                'receiver_customer',
                'items' => function ($query) {
                    $query->with('item:id,item_name,description,item_image');
                }
            ])->where(function ($query) use ($business_id) {
                $query->where('initiator_id', $business_id)
                    ->orWhere('receiver_business_id', $business_id);
            })
                ->where('id', $transaction_id)
                ->first();

            $transaction = $this->modifyTransaction($transaction, $business_id);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }

        return $transaction;
    }

    public function acceptTransaction($business_id, $transaction, Request $request)
    {
        $transaction_type = $request->input('type');
        $workflow = $this->getWorkflow($business_id, $transaction, $transaction_type);
        return $workflow->acceptTransaction();
    }

    public function rejectTransaction($business_id, $transaction, Request $request)
    {
        $transaction_type = $request->input('type');
        $workflow = $this->getWorkflow($business_id, $transaction, $transaction_type);
        return $workflow->rejectTransaction("reason");
    }
    public function payTransaction($business_id, $transaction, Request $request)
    {
        $transaction_type = $request->input('type');
        if ($transaction_type == null) {
            $trans = Transaction::find($transaction);
            if ($trans == null) {
                return response()->json(['error' => 'Transaction not found'], 404);
            }
            $transaction_type = $trans->type;
        }
        $workflow = $this->getWorkflow($business_id, $transaction, $transaction_type);
        if ($workflow == null) {
            return response()->json(['error' => 'Workflow not found'], 404);
        }

        return $workflow->payTransaction($request->input('mode'));
    }

    public function acceptAndPayTransaction($business_id, $transaction, Request $request)
    {
        $transaction_type = $request->input('type');
        $workflow = $this->getWorkflow($business_id, $transaction, $transaction_type);
        return $workflow->payTransaction($request->input('mode'));
    }
    public function closeTransaction($business_id, $transaction, Request $request)
    {
        $transaction_type = $request->input('type');
        $workflow = $this->getWorkflow($business_id, $transaction, $transaction_type);
        return $workflow->closeTransaction();
    }

    private function getWorkflow($business_id, $transaction, $transaction_type): TransactionFlow
    {
        $workflow = new NormalSaleWorkflow($business_id, $transaction);
        $actualTransaction = $workflow->getFullTransaction();
        $transaction_type = $transaction_type;

        $isB2B = $actualTransaction->isB2B;

        if (!$isB2B) {
            return $workflow;
        } else {
            switch ($transaction_type) {
                case 'leasing':
                    $workflow = new LeasingWorkflow($business_id, $transaction);
                    break;
                case 'purchase':
                    $workflow = new PurchaseWorkflow($business_id, $transaction);
                    break;
                case 'borrowing':
                    $workflow = new BorrowingWorkflow($business_id, $transaction);
                    break;
                default:
                    $workflow = new NormalSaleWorkflow($business_id, $transaction);
                    break;
            }
            return $workflow;
        }
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
            'initiator:business_id,business_name,email,phone_number,location,currency_code',
            'receiver_business:business_id,business_name,email,phone_number,location,currency_code',
            'receiver_customer',
            'details',
            'items' => function ($query) {
                $query->with('item');
            }
        ])->first();

        $imagePath = public_path('images/default-business-logo.png');

        $imageData = base64_encode(file_get_contents($imagePath));

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
        if (!$transaction) {
            return redirect()->route('not-found');
        }
        $pdf = Pdf::loadView('receipt', compact('transaction'));
        return view('receipt', compact('transaction'));
    }
}
