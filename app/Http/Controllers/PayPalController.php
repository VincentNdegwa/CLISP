<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class PayPalController extends Controller
{
    private $client;
    private $clientId;
    private $clientSecret;
    private $apiBaseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->clientId = env('PAYPAL_CLIENT_ID');
        $this->clientSecret = env('PAYPAL_SECRET_ID');
        $this->apiBaseUrl = env('PAYPAL_ENVIRONMENT') == 'sandbox' ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    private function getAccessToken()
    {
        try {
            $response = $this->client->request('POST', "$this->apiBaseUrl/v1/oauth2/token", [
                'auth' => [$this->clientId, $this->clientSecret],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            return $body['access_token'];
        } catch (RequestException $e) {
            return response()->json(['error' => 'Unable to get access token'], 500);
        }
    }

    public function createOrder(Request $request)
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return response()->json(['error' => 'Unable to obtain access token'], 500);
        }

        try {
            Log::info('PayPal Create Order Request:', $request->all());

            $response = $this->client->request('POST', "$this->apiBaseUrl/v2/checkout/orders", [
                'headers' => [
                    'Authorization' => "Bearer $accessToken",
                    'Content-Type' => 'application/json',
                ],
                'json' => $request->all(),
            ]);

            $orderData = json_decode($response->getBody(), true);

            Log::info('PayPal Create Order Response:', $orderData);

            return response()->json($orderData);
        } catch (RequestException $e) {
            Log::error('PayPal Create Order Error:', [
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? json_decode($e->getResponse()->getBody(), true) : null,
            ]);

            return response()->json(['error' => true, 'message' => 'Unable to create order', "data" => $e->getMessage()], 500);
        }
    }


    public function captureOrder($orderId)
    {
        $accessToken = $this->getAccessToken();

        try {
            $response = $this->client->request('POST', "$this->apiBaseUrl/v2/checkout/orders/$orderId/capture", [
                'headers' => [
                    'Authorization' => "Bearer $accessToken",
                    'Content-Type' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            return response()->json(['error' => 'Unable to capture order'], 500);
        }
    }
}
