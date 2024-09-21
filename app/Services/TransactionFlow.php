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

    protected function createResponse(bool $error, string $message, $data = null, $errors = null)
    {
        return response()->json([
            'error' => $error,
            'message' => $message,
            'errors' => $errors,
            'data' => $data,
        ]);
    }

    public function acceptTransaction()
    {
        try {
            $this->transaction->status = 'approved';
            $this->transaction->save();
            return $this->createResponse(false, 'Transaction approved successfully.', $this->transaction);
        } catch (\Exception $e) {
            return $this->createResponse(true, 'Failed to accept transaction.', null, $e->getMessage());
        }
    }

    public function rejectTransaction(string $reason)
    {
        try {
            $this->transaction->status = 'canceled';
            // $this->transaction->rejection_reason = $reason;
            $this->transaction->save();
            return $this->createResponse(false, 'Transaction cancelled successfully.', $this->transaction);
        } catch (\Exception $e) {
            return $this->createResponse(true, 'Failed to reject transaction.', null, $e->getMessage());
        }
    }

    public function payTransaction()
    {
        try {
            $this->transaction->status = 'paid';
            $this->transaction->save();
            return $this->createResponse(false, 'Payment completed successfully.', $this->transaction);
        } catch (\Exception $e) {
            return $this->createResponse(true, 'Failed to complete payment.', null, $e->getMessage());
        }
    }

    public function closeTransaction()
    {
        try {
            $this->transaction->status = 'completed';
            $this->transaction->save();
            return $this->createResponse(false, 'Transaction closed successfully.', $this->transaction);
        } catch (\Exception $e) {
            return $this->createResponse(true, 'Failed to close transaction.', null, $e->getMessage());
        }
    }

    // Abstract methods to be implemented by child classes for specific workflows
    abstract public function giveTransactionItem();
    abstract public function returnTransactionItem();
    abstract public function applyLateFees();
    abstract public function applyDamageFees();
    abstract public function applyShippingFees();
}
