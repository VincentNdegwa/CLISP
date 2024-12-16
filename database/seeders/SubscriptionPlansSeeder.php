<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Basic Plans
        SubscriptionPlan::create([
            'product_id' => 'pro_01jf0tt00bceps4bf5fd4s6p9f',
            'price_id' => 'pri_01jf5secxp5zmt8cafyha4yp7k',
            'name' => 'Basic',
            'description' => 'Daily Basic Subscription',
            'price' => 1.50,
            'currency' => 'USD',
            'billing_cycle' => 'daily',
            'features' => json_encode([
                'Tax category' => 'Standard digital goods',
                'Created' => 'Dec 13, 2024',
            ]),
        ]);

        SubscriptionPlan::create([
            'product_id' => 'pro_01jf0tt00bceps4bf5fd4s6p9f',
            'price_id' => 'pri_01jf34aypscqx85c28vn3nd2s8',
            'name' => 'Basic',
            'description' => 'Annual Basic Subscription',
            'price' => 200.00,
            'currency' => 'USD',
            'billing_cycle' => 'annually',
            'features' => json_encode([
                'Tax category' => 'Standard digital goods',
                'Created' => 'Dec 13, 2024',
            ]),
        ]);

        SubscriptionPlan::create([
            'product_id' => 'pro_01jf0tt00bceps4bf5fd4s6p9f',
            'price_id' => 'pri_01jf0v01jhed7prk0snspzhttn',
            'name' => 'Basic',
            'description' => 'Monthly Basic Subscription',
            'price' => 30.00,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'features' => json_encode([
                'Tax category' => 'Standard digital goods',
                'Created' => 'Dec 13, 2024',
            ]),
        ]);

        // Pro Plans
        SubscriptionPlan::create([
            'product_id' => 'pro_02jf0tt00bceps4bf5fd4s6p9g',
            'price_id' => 'pri_01jf7ryej74kvzzsb1nmbk5kdp',
            'name' => 'Pro',
            'description' => 'Yearly Pro Subscription',
            'price' => 500.00,
            'currency' => 'USD',
            'billing_cycle' => 'annually',
            'features' => json_encode([
                'Tax category' => 'Standard digital goods',
                'Created' => 'Dec 13, 2024',
            ]),
        ]);

        SubscriptionPlan::create([
            'product_id' => 'pro_02jf0tt00bceps4bf5fd4s6p9g',
            'price_id' => 'pri_01jf7reyx1gm1ddb8zxfhmgtbx',
            'name' => 'Pro',
            'description' => 'Monthly Pro Subscription',
            'price' => 50.00,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'features' => json_encode([
                'Tax category' => 'Standard digital goods',
                'Created' => 'Dec 13, 2024',
            ]),
        ]);

        // Premium Plans
        SubscriptionPlan::create([
            'product_id' => 'pro_03jf0tt00bceps4bf5fd4s6p9h',
            'price_id' => 'pri_01jf7sb4wk4avdzq8wq9tfrf8d',
            'name' => 'Premium',
            'description' => 'Yearly Premium Subscription',
            'price' => 800.00,
            'currency' => 'USD',
            'billing_cycle' => 'annually',
            'features' => json_encode([
                'Tax category' => 'Standard digital goods',
                'Created' => 'Dec 13, 2024',
            ]),
        ]);

        SubscriptionPlan::create([
            'product_id' => 'pro_03jf0tt00bceps4bf5fd4s6p9h',
            'price_id' => 'pri_01jf7s8k4jk0488vh5sz2c50x6',
            'name' => 'Premium',
            'description' => 'Monthly Premium Subscription',
            'price' => 100.00,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'features' => json_encode([
                'Tax category' => 'Standard digital goods',
                'Created' => 'Dec 13, 2024',
            ]),
        ]);
    }
}
