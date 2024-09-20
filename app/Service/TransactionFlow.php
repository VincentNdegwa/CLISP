<?php

namespace App\Services;

use App\Models\Transaction;
use Exception;

abstract class TransactionFlow
{
    protected $transactionId;
    public $transaction;

    public function __construct(string $transactionId)
    {
        $this->transactionId = $transactionId;
        $this->transaction = Transaction::find($transactionId);
    }

    public function acceptTransaction()
    {
        try {
            $this->transaction->status = 'pending_payments';
            $this->transaction->save();
            return [
                'error' => false,
                'message' => 'Transaction accepted successfully.',
                'errors' => null,
                'data' => $this->transaction
            ];
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => 'An unexpected error occurred while accepting the transaction.',
                'errors' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function acceptAndPayTransaction()
    {
        try {
            $this->acceptTransaction();
            $this->payTransaction();
            return [
                'error' => false,
                'message' => 'Transaction accepted and paid successfully.',
                'errors' => null,
                'data' => $this->transaction
            ];
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => 'An unexpected error occurred while accepting and paying the transaction.',
                'errors' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function rejectTransaction(string $reason)
    {
        try {
            $this->transaction->status = 'canceled';
            $this->transaction->rejection_reason = $reason;
            $this->transaction->save();
            return [
                'error' => false,
                'message' => 'Transaction rejected successfully.',
                'errors' => null,
                'data' => $this->transaction
            ];
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => 'An unexpected error occurred while rejecting the transaction.',
                'errors' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function payTransaction()
    {
        try {
            $this->transaction->status = 'payment_complete';
            $this->transaction->save();
            return [
                'error' => false,
                'message' => 'Payment processed successfully.',
                'errors' => null,
                'data' => $this->transaction
            ];
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => 'An unexpected error occurred while processing the payment.',
                'errors' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    public function closeTransaction()
    {
        try {
            $this->transaction->status = 'completed';
            $this->transaction->save();
            return [
                'error' => false,
                'message' => 'Transaction closed successfully.',
                'errors' => null,
                'data' => $this->transaction
            ];
        } catch (Exception $e) {
            return [
                'error' => true,
                'message' => 'An unexpected error occurred while closing the transaction.',
                'errors' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    // Abstract methods to be implemented by child classes
    abstract public function giveTransactionItem();

    abstract public function returnTransactionItem();

    abstract public function applyLateFees();

    abstract public function applyDamageFees();

    abstract public function applyShippingFees();
}
