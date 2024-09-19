<?php

namespace App\Services;

class PurchaseWorkflow extends TransactionFlow
{
    public function giveTransactionItem()
    {
        // Logic specific to purchase transactions
        // e.g., Mark items as delivered
    }

    public function returnTransactionItem()
    {
        // Purchase usually doesn't involve returning items, so maybe this could throw an exception
        throw new \Exception('Items in purchase cannot be returned.');
    }

    public function applyLateFees()
    {
        // Purchase might not need late fees, so no implementation needed
    }

    public function applyDamageFees()
    {
        // Purchase-specific damage fee logic
    }

    public function applyShippingFees()
    {
        // Purchase-specific shipping fee logic
    }
}
