<?php

namespace App\Services;

use App\Models\ItemBusiness;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;

class BorrowingWorkflow extends TransactionFlow
{
    public function giveTransactionItem($params)
    {
        // Borrowing-specific logic for giving the item
        DB::beginTransaction();
        try {

            $fullTransaction = $this->getFullTransaction();
            $transactionId = $params['transaction_id'];
            $items = $params['items'];
            $itemIds = [];
            foreach ($items as $item) {

                if ($item['quantity'] == $item['quantity_ship']) {
                    $itemIds[] = $item['item_id'];
                }
            }
            $transaction = $this->transaction;

            TransactionItem::where('transaction_id', $transactionId)->whereIn("item_id", $itemIds)->update([
                'status' => 'transit'
            ]);
            $some_pending = false;
            $fullTransaction = $this->getFullTransaction();

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
            DB::commit();
            return $this->createResponse(false, "Items dispatched successfully", $fullTransaction);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->createResponse(true, "Failed to dispatch items", null, $th->getMessage());
        }
    }

    public function receiveTransactionItem($params)
    {
        $transactionId = $params['transaction_id'];
        $items = $params['items'];
        $itemIds = [];
        $fullTransaction = $this->getFullTransaction();

        foreach ($items as $item) {
            $intiatorBusinessItem = ItemBusiness::where('business_id', $fullTransaction->initiator->business_id)
                ->where('item_id', $item['item_id'])->first();
            $intiatorBusinessItem->update([
                'quantity' => $intiatorBusinessItem->quantity - $item['quantity_ship'],
            ]);
            $receiverBusinessItemExists = ItemBusiness::where('item_id', $item['item_id'])
                ->where('business_id', $fullTransaction->receiver_business->business_id)->first();
            if (isset($receiverBusinessItemExists)) {
                $receiverBusinessItemExists->quantity += $item['quantity_ship'];
                $receiverBusinessItemExists->save();
            } else {

                ItemBusiness::create([
                    'item_id' => $item['item_id'],
                    'business_id' => $fullTransaction->receiver_business->business_id,
                    'source' => 'Borrowed',
                    'quantity' => $item['quantity_ship']
                ]);
            }
            $itemIds[] = $item['item_id'];
        }
        $transaction = $this->transaction;

        $fullTransaction = $this->getFullTransaction();
        TransactionItem::where('transaction_id', $transactionId)->whereIn("item_id", $itemIds)->update([
            'status' => 'received'
        ]);
        foreach ($fullTransaction->items as $item) {
            $item->status = 'received';
        }

        $fullTransaction['status'] = 'completed';
        $transaction->update([
            'status' => 'completed',
        ]);
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
