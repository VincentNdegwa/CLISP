<?php

namespace App\Services;

use App\Models\ItemBusiness;
use App\Models\TransactionItem;
use App\Models\TransactionItemHistory;
use App\Services\TransactionFlow;
use Illuminate\Support\Facades\DB;

class NonShippableTransactionWorkflow extends TransactionFlow
{
    public function acceptTransaction()
    {
        try {
            DB::beginTransaction();
            $this->transaction->status = "approved";;
            $this->transaction->save();

            //transaction items

            TransactionItem::where('transaction_id', $this->transactionId)->update([
                'status' => 'pending'
            ]);

            // update business quantity
            foreach ($this->transaction->items as $item) {
                $intiatorBusinessItem = ItemBusiness::where('business_id', $this->transaction->initiator->business_id)
                    ->where('item_id', $item['item_id'])->first();


                $intiatorBusinessItem->update([
                    'quantity' => $intiatorBusinessItem->quantity - $item['quantity'],
                ]);

                TransactionItemHistory::create([
                    'item_business_id' => $intiatorBusinessItem->id,
                    'transaction_type' => $this->transaction->type,
                    'quantity' => -$item['quantity'],
                    'transaction_time' => now(),
                ]);
            }

            DB::commit();

            return $this->createResponse(false, 'Transaction Approved Successfully.', $this->getFullTransaction());
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->createResponse(true, $th->getMessage());
        }
    }
    public function payTransaction()
    {
        try {
            DB::beginTransaction();

            $this->transaction->status = 'paid';
            $this->transaction->save();


            return $this->createResponse(false, 'Payment completed successfully.', $this->getFullTransaction());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->createResponse(true, 'Failed to complete payment.', null, $e->getMessage());
        }
    }

    public function rejectTransaction($params)
    {
        try {
            DB::beginTransaction();
            $this->transaction->status = 'canceled';
            $this->transaction->save();
            TransactionItem::where('transaction_id', $this->transactionId)->update([
                'status' => 'cancelled'
            ]);

            // update business quantity
            foreach ($this->transaction->items as $item) {
                $intiatorBusinessItem = ItemBusiness::where('business_id', $this->transaction->initiator->business_id)
                    ->where('item_id', $item['item_id'])->first();


                $intiatorBusinessItem->update([
                    'quantity' => $intiatorBusinessItem->quantity + $item['quantity'],
                ]);

                TransactionItemHistory::create([
                    'item_business_id' => $intiatorBusinessItem->id,
                    'transaction_type' => 'returned to stock',
                    'quantity' => $item['quantity'],
                    'transaction_time' => now(),
                ]);
            }
            DB::commit();
            return $this->createResponse(false, 'Payment Cancelled successfully.', $this->getFullTransaction());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->createResponse(true, 'Failed to cancell transaction.', $e->getMessage());
        }
        return $params;
    }
    public function giveTransactionItem($params)
    {
        return $params;
    }

    public function receiveTransactionItem($params) {}

    public function returnTransactionItem($params) {}

    public function applyLateFees()
    {
        // Borrowing-specific late fees
    }

    public function applyDamageFees()
    {
        // Borrowing-specific damage fee logic
    }

    public function applyShippingFees()
    {
        // Borrowing-specific shipping fee logic
    }
}
