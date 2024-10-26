<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRatesController extends Controller
{
    public function getRates()
    {
        $token = env('EXCHANGERATE_API_KEY');
        $url = "https://v6.exchangerate-api.com/v6/$token/latest/USD";

        $response = Http::get($url);

        $data = $response->json();


        if ($data['result'] === 'success') {
            foreach ($data['conversion_rates'] as $currency => $rate) {
                ExchangeRate::updateOrCreate(
                    ['base_currency' => 'USD', 'target_currency' => $currency],
                    ['rate' => $rate, 'last_update' => now()]
                );
            }
            Log::info('Exchange rates updated successfully.');
        } else {
            Log::error('Failed to fetch exchange rates: ' . json_encode($data));
        }
    }
}
