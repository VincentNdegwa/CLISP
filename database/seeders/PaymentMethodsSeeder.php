<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            ['displayName' => 'Cash', 'icon' => 'pi pi-wallet'],
            ['displayName' => 'Bank', 'icon' => 'pi pi-building'],
            ['displayName' => 'Paybill', 'icon' => 'pi pi-credit-card'],
            ['displayName' => 'Till Number', 'icon' => 'pi pi-wallet'],
            ['displayName' => 'Coupon', 'icon' => 'pi pi-ticket'],
            ['displayName' => 'PayPal', 'icon' => 'pi pi-paypal'],
            ['displayName' => 'Credit Card', 'icon' => 'pi pi-credit-card'],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create([
                'name' => $method['displayName'],
                'business_id' => null,
                'default' => 'true',
                'icon' => $method['icon'],
            ]);
        }
    }
}
