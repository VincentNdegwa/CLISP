<?php

namespace App\Services\Transactions\Shipping;

use App\Services\ShippableTransactionWorkflow;


class PurchaseWorkflow extends ShippableTransactionWorkflow
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
