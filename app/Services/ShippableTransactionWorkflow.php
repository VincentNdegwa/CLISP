<?php


namespace App\Services;

use App\Services\TransactionFlow;
use App\Models\ItemBusiness;
use App\Models\TransactionItem;
use App\Models\TransactionItemHistory;
use Illuminate\Support\Facades\DB;

class ShippableTransactionWorkflow extends TransactionFlow
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
                $intiatorBusinessItem = ItemBusiness::where('business_id', $fullTransaction->initiator->business_id)
                    ->where('item_id', $item['item_id'])->first();


                $intiatorBusinessItem->update([
                    'quantity' => $intiatorBusinessItem->quantity - $item['quantity_ship'],
                ]);

                TransactionItemHistory::create([
                    'item_business_id' => $intiatorBusinessItem->id,
                    'transaction_type' => $fullTransaction->type,
                    'quantity' => -$item['quantity_ship'],
                    'transaction_time' => now(),
                ]);

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
        DB::beginTransaction();
        try {
            $transactionId = $params['transaction_id'];
            $items = $params['items'];
            $itemIds = [];
            $fullTransaction = $this->getFullTransaction();

            foreach ($items as $item) {
                $receiverBusinessItemExists = null;
                $receiverBusinessItemExists = ItemBusiness::where('item_id', $item['item_id'])
                    ->where('business_id', $fullTransaction->receiver_business->business_id)->first();
                if (isset($receiverBusinessItemExists)) {
                    $receiverBusinessItemExists->quantity += $item['quantity_ship'];
                    $receiverBusinessItemExists->save();
                } else {

                    $receiverBusinessItemExists = ItemBusiness::create([
                        'item_id' => $item['item_id'],
                        'business_id' => $fullTransaction->receiver_business->business_id,
                        'source' => 'Borrowed',
                        'quantity' => $item['quantity_ship']
                    ]);
                }

                TransactionItemHistory::create([
                    'item_business_id' => $receiverBusinessItemExists->id,
                    'transaction_type' => $fullTransaction->type,
                    'quantity' => $item['quantity_ship'],
                    'transaction_time' => now(),
                ]);
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

            DB::commit();
            return $this->createResponse(false, 'Successfully Received Items', $fullTransaction);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->createResponse(true, 'Failed to Receive Items', $th->getMessage());
        }
    }

    public function returnTransactionItem($params)
    {

        try {
            $fullTransaction = $this->getFullTransaction();

            DB::beginTransaction();
            $transactionId = $params['transaction_id'];
            $items = $params['items'];
            $itemIds = [];
            foreach ($items as $item) {

                $receiverBusinessItemExists = ItemBusiness::where('item_id', $item['item_id'])
                    ->where('business_id', $fullTransaction->receiver_business->business_id)->first();
                if (isset($receiverBusinessItemExists)) {
                    $receiverBusinessItemExists->quantity -= $item['quantity_ship'];
                    $receiverBusinessItemExists->save();

                    TransactionItemHistory::create([
                        'item_business_id' => $receiverBusinessItemExists->id,
                        'transaction_type' => $fullTransaction->type,
                        'quantity' => -$item['quantity_ship'],
                        'transaction_time' => now(),
                    ]);
                }

                $intiatorBusinessItem = ItemBusiness::where('business_id', $fullTransaction->initiator->business_id)
                    ->where('item_id', $item['item_id'])->first();
                $intiatorBusinessItem->update([
                    'quantity' => $intiatorBusinessItem->quantity += $item['quantity_ship'],
                ]);
                TransactionItemHistory::create([
                    'item_business_id' => $intiatorBusinessItem->id,
                    'transaction_type' => $fullTransaction->type,
                    'quantity' => $item['quantity_ship'],
                    'transaction_time' => now(),
                ]);
                
                if ($item['quantity'] == $item['quantity_ship']) {
                    $itemIds[] = $item['item_id'];
                }

                $itemIds[] = $item['item_id'];
            }

            $transaction = $this->transaction;
            $transaction->status = 'return';
            $transaction->save();

            TransactionItem::whereIn('item_id', $itemIds)->update([
                'status' => 'returned'
            ]);
            DB::commit();
            $fullTransaction = $this->getFullTransaction();

            return $this->createResponse(false, "Items returned successfully", $fullTransaction);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->createResponse(true, 'Failed to return items', $th->getMessage());
        }
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
