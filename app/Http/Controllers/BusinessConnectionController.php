<?php

namespace App\Http\Controllers;

use App\Models\BusinessConnection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BusinessConnectionController extends Controller
{
    public function getBusinessConnection($business_id)
    {
        // get all connections where receiving_business_id = business_id or the  requesting_business_id = business_id
        $sent = BusinessConnection::where('requesting_business_id', $business_id)
            ->with('businessRequester', 'businessReceiver', 'userRequester', 'userReceiver')
            ->get();
        $received = BusinessConnection::where('receiving_business_id', $business_id)
            ->with('businessRequester', 'businessReceiver', 'userRequester', 'userReceiver')
            ->get();

        return response()->json([
            "error" => false,
            "message" => "Connections fetched successfully",
            "sent" => $sent,
            "incoming" => $received,
            "total_connections" => count($sent) + count($received),
            "total_received" => count($received),
            "total_sent" => count($sent),
        ]);
    }

    public function sendConnectionRequest(Request $request)
    {
        try {
            $validData = $request->validate([
                'receiving_business_id' => 'required|exists:business,business_id',
                'requesting_business_id' => 'required|exists:business,business_id',
                'requesting_user_id' => 'required|exists:users,id',
            ]);
            if ($validData['receiving_business_id'] == $validData['requesting_business_id']) {
                return response()->json([
                    "error" => true,
                    "message" => "Can't create a request to your business",
                ]);
            }
            $business_request = BusinessConnection::create($request->all());
            $request = BusinessConnection::where('id', $business_request->id)
                ->with('businessRequester', 'businessReceiver', 'userRequester', 'userReceiver')
                ->get();
            return response()->json([
                'error' => false,
                'message' => "Business Request Created",
                "business_request" => $request
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage()
            ], 500); // HTTP 500 Internal Server Error
        }
    }
}
