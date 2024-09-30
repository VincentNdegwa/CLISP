<?php

namespace App\Services;

use App\Models\TransactionItem;

class LeasingWorkflow extends TransactionFlow
{
    public function giveTransactionItem($params)
    {
        // Leasing logic for giving the item to the customer
        $transactionId = $params['transaction_id'];
        $items = $params['items'];
        $transaction = $this->transaction;

        TransactionItem::where('transaction_id', $transactionId)->whereIn("item_id", $items)->update([
            'status' => 'transit'
        ]);
        $fullTransaction = $this->getFullTransaction();
        $some_pending = false;
        foreach ($fullTransaction->items as $item) {
            if ($item->status === 'pending') {
                $some_pending = true;
                $transaction->update([
                    'status' => 'partially-dispatched',
                ]);
                $fullTransaction['status'] = 'partially-dispatched';
                break;
            }
        }
        if (!$some_pending) {
            $transaction->update([
                'status' => 'dispatched',
            ]);
            $fullTransaction['status'] = 'dispatched';
        }

        return $this->createResponse(false, "Items dispatched successfully", $fullTransaction);
    }

    public function receiveTransactionItem($params){
        
    }

    public function returnTransactionItem()
    {
        // Logic for returning the leased item
    }

    public function applyLateFees()
    {
        // Logic for applying late fees in leasing
    }

    public function applyDamageFees()
    {
        // Leasing-specific damage fee logic
    }

    public function applyShippingFees()
    {
        // Leasing-specific shipping fee logic
    }
}
