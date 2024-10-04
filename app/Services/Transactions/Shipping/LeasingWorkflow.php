<?php

namespace App\Services\Transactions\Shipping;

use App\Services\ShippableTransactionWorkflow;

class LeasingWorkflow extends ShippableTransactionWorkflow
{
    public function giveTransactionItem($params)

    {
        return parent::giveTransactionItem($params);
    }

    public function receiveTransactionItem($params)
    {
        return parent::receiveTransactionItem($params);
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
