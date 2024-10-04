<?php

namespace App\Services\Transactions\NonShipping;

use App\Services\TransactionFlow;

class NormalSaleWorkflow extends TransactionFlow
{

    public function giveTransactionItem($transactionData)
    {
        // Borrowing-specific logic for giving the item
    }
    public function receiveTransactionItem($params) {}

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
