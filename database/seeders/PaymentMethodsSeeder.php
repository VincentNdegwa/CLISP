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
            ["displayName" => "Cash", "icon" => "pi pi-wallet", "category" => "Simple"],
            ["displayName" => "Bank", "icon" => "pi pi-building", "category" => "Information-Required"],
            ["displayName" => "Paybill", "icon" => "pi pi-credit-card", "category" => "Information-Required"],
            ["displayName" => "Till Number", "icon" => "pi pi-wallet", "category" => "Information-Required"],
            ["displayName" => "Coupon", "icon" => "pi pi-ticket", "category" => "Simple"],
            ["displayName" => "PayPal", "icon" => "pi pi-paypal", "category" => "Information-Required"],
            ["displayName" => "Credit Card", "icon" => "pi pi-credit-card", "category" => "Information-Required"]
        ];


        foreach ($paymentMethods as $method) {
            PaymentMethod::create([
                'name' => $method['displayName'],
                'business_id' => null,
                'default' => 'true',
                'icon' => $method['icon'],
                'category' => $method['category']
            ]);
        }
    }
}
