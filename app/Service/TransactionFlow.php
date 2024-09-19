<?php

namespace App\Services;

use App\Models\Transaction;

abstract class TransactionFlow
{
    protected $transactionId;
    public $transaction;

    public function __construct(string $transactionId)
    {
        $this->transactionId = $transactionId;
        $this->transaction = Transaction::find($transactionId);
    }

    // Common methods that might be reused or extended
    public function acceptTransaction()
    {
        // Generic logic for accepting a transaction
        $this->transaction->status = 'accepted';
        $this->transaction->save();
    }

    public function rejectTransaction(string $reason)
    {
        // Generic logic for rejecting a transaction with a reason
        $this->transaction->status = 'rejected';
        $this->transaction->rejection_reason = $reason;
        $this->transaction->save();
    }

    public function payTransaction()
    {
        // Generic logic for handling payment
        $this->transaction->status = 'paid';
        $this->transaction->save();
    }

    public function closeTransaction()
    {
        // Generic logic for closing a transaction
        $this->transaction->status = 'closed';
        $this->transaction->save();
    }

    // Abstract methods to be implemented by child classes for specific workflows
    abstract public function giveTransactionItem();

    abstract public function returnTransactionItem();

    abstract public function applyLateFees();

    abstract public function applyDamageFees();

    abstract public function applyShippingFees();
}
