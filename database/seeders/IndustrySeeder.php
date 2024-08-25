<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $industries = [
            'Retail',
            'Manufacturing',
            'Technology',
            'Healthcare',
            'Finance',
            'Hospitality',
        ];

        foreach ($industries as $industry) {
            DB::table('industries')->insert([
                'name' => $industry,
            ]);
        }
    }
}
