<?php

namespace Database\Seeders;

use App\Models\StockAdjustmentReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockAdjustmentReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultReasons = [
            [
                'name' => 'Inventory Count',
                'description' => 'Adjustment based on physical inventory count',
                'business_id' => 0,
                'is_active' => true,
                'type' => 'both',
            ],
            [
                'name' => 'Purchase Return',
                'description' => 'Items returned by customer',
                'business_id' => 0,
                'is_active' => true,
                'type' => 'increase',
            ],
            [
                'name' => 'Donation Received',
                'description' => 'Items received as donation',
                'business_id' => 0,
                'is_active' => true,
                'type' => 'increase',
            ],
            [
                'name' => 'Found Items',
                'description' => 'Previously lost items found',
                'business_id' => 0,
                'is_active' => true,
                'type' => 'increase',
            ],
            
            // Decrease reasons
            [
                'name' => 'Damaged Goods',
                'description' => 'Items damaged in storage or handling',
                'business_id' => 0,
                'is_active' => true,
                'type' => 'decrease',
            ],
            [
                'name' => 'Expired Items',
                'description' => 'Items that have passed their expiration date',
                'business_id' => 0,
                'is_active' => true,
                'type' => 'decrease',
            ],
            [
                'name' => 'Lost Items',
                'description' => 'Items that cannot be located',
                'business_id' => 0,
                'is_active' => true,
                'type' => 'decrease',
            ],
            [
                'name' => 'Theft',
                'description' => 'Items stolen from inventory',
                'business_id' => 0,
                'is_active' => true,
                'type' => 'decrease',
            ],
            [
                'name' => 'Quality Control Rejection',
                'description' => 'Items that failed quality control checks',
                'business_id' => 0,
                'is_active' => true,
                'type' => 'decrease',
            ],
            [
                'name' => 'Donation Given',
                'description' => 'Items donated to charity or other organizations',
                'business_id' => 0,
                'is_active' => true,
                'type' => 'decrease',
            ],
            [
                'name' => 'Sample Use',
                'description' => 'Items used as samples for customers or testing',
                'business_id' => 0,
                'is_active' => true,
                'type' => 'decrease',
            ],
            [
                'name' => 'Internal Use',
                'description' => 'Items used internally by the business',
                'business_id' => 0,
                'is_active' => true,
                'type' => 'decrease',
            ],
            [
                'name' => 'System Correction',
                'description' => 'Adjustment to correct system errors',
                'business_id' => 0,
                'is_active' => true,
                'type' => 'both',
            ],
        ];

        // Insert all default reasons
        foreach ($defaultReasons as $reason) {
            StockAdjustmentReason::updateOrCreate(
                [
                    'name' => $reason['name'],
                    'business_id' => 0
                ],
                $reason
            );
        }
    }
}
