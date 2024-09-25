<?php

namespace App\Services;

use App\Models\TransactionItem;

class BorrowingWorkflow extends TransactionFlow
{
    public function giveTransactionItem($params)
    {
        // Borrowing-specific logic for giving the item

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

    public function returnTransactionItem()
    {
        // Logic for returning the borrowed item
    }

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
