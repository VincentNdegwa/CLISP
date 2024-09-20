<?php

namespace App\Services;

class BorrowingWorkflow extends TransactionFlow
{
    public function giveTransactionItem()
    {
        // Borrowing-specific logic for giving the item
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
