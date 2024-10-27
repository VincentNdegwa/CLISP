<?php

namespace App\Services;

use App\Http\Controllers\TransactionController;
use App\Models\ItemBusiness;
use App\Models\ResourceItem;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionItemHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class TransactionFlow
{
    protected $transactionId;
    public $transaction;
    public $businesId;

    public function __construct(string $business_id, string $transactionId)
    {
        $this->transactionId = $transactionId;
        $this->transaction = Transaction::find($transactionId);
        $this->businesId = $business_id;
    }

    public function createResponse(bool $error, string $message, $data = null, $errors = null)
    {
        return response()->json([
            'error' => $error,
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function getFullTransaction()
    {
        try {
            $business_id = $this->businesId;
            $transaction_id = $this->transactionId;

            $transactionController = new TransactionController();
            $transaction = $transactionController->getFullTransaction($business_id, $transaction_id);

            return $transaction;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function acceptTransaction()
    {
        try {
            $this->transaction->status = 'approved';
            $this->transaction->save();

            TransactionItem::where('transaction_id', $this->transactionId)->update([
                'status' => 'pending'
            ]);


            return $this->createResponse(false, 'Transaction approved successfully.', $this->transaction);
        } catch (\Exception $e) {
            return $this->createResponse(true, 'Failed to accept transaction.', null, $e->getMessage());
        }
    }

    public function rejectTransaction($transactionData)
    {
        try {
            DB::beginTransaction();
            $this->transaction->status = 'canceled';
            $this->transaction->save();
            TransactionItem::where('transaction_id', $this->transactionId)->update([
                'status' => 'cancelled'
            ]);
            if (isset($transactionData['items'])) {
                foreach ($transactionData['items'] as $item) {
                    $intiatorBusinessItem = ItemBusiness::where('business_id', $this->transaction->initiator->business_id)
                        ->where('item_id', $item['item_id'])->first();
                    $intiatorBusinessItem->update([
                        'quantity' => $intiatorBusinessItem->quantity += $item['quantity_ship'],
                    ]);
                    TransactionItemHistory::create([
                        'item_business_id' => $intiatorBusinessItem->id,
                        'transaction_type' => $this->transaction->type,
                        'quantity' => $item['quantity_ship'],
                        'transaction_time' => now(),
                    ]);
                }
            }

            DB::commit();
            return $this->createResponse(false, 'Transaction cancelled successfully.', $this->getFullTransaction());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->createResponse(true, 'Failed to reject transaction.', null, $e->getMessage());
        }
    }

    public function payTransaction($mode)
    {
        try {

            $this->transaction->status = 'paid';
            $this->transaction->save();
            $fullTransaction = $this->getFullTransaction();
            return $this->createResponse(false, 'Sucessfully Paid Transaction',  $fullTransaction);
        } catch (\Exception $e) {
            return $this->createResponse(true, 'Failed to complete payment.', null, $e->getMessage());
        }
    }

    public function closeTransaction()
    {
        try {
            $this->transaction->status = 'completed';
            $this->transaction->save();
            return $this->createResponse(false, 'Transaction closed successfully.', $this->transaction);
        } catch (\Exception $e) {
            return $this->createResponse(true, 'Failed to close transaction.', null, $e->getMessage());
        }
    }


    // Abstract methods to be implemented by child classes for specific workflows
    abstract public function giveTransactionItem($transactionData);
    abstract public function receiveTransactionItem($transactionData);
    abstract public function returnTransactionItem($transactionData);

    abstract public function applyLateFees();
    abstract public function applyDamageFees();
    abstract public function applyShippingFees();
}
