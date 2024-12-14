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
        // Basic Annual Subscription
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

        // Basic Monthly Subscription
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
    }
}
