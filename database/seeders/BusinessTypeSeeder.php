<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businessTypes = [
            'Sole Proprietorship',
            'Partnership',
            'Limited Liability Company (LLC)',
            'Corporation',
            'Non-Profit Organization',

        ];

        foreach ($businessTypes as $types) {
            DB::table("business_types")->insert([
                "name" => $types
            ]);
        }
    }
}
