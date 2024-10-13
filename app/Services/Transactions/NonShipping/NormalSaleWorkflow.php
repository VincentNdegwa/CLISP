<?php

namespace App\Services\Transactions\NonShipping;

use App\Services\NonShippableTransactionWorkflow;
use App\Services\TransactionFlow;

class NormalSaleWorkflow extends NonShippableTransactionWorkflow
{

    public function giveTransactionItem($params)
    {
        return parent::giveTransactionItem($params);
    }

    public function receiveTransactionItem($params)
    {
        return parent::receiveTransactionItem($params);
    }

    public function returnTransactionItem($params)
    {
        return parent::returnTransactionItem($params);
    }

    public function applyLateFees()
    {
        return parent::applyLateFees();
    }

    public function applyDamageFees()
    {
        return parent::applyDamageFees();
    }

    public function applyShippingFees()
    {
        return parent::applyShippingFees();
    }
}
