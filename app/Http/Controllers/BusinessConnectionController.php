<?php

namespace App\Http\Controllers;

use App\Models\BusinessConnection;
use Illuminate\Http\Request;

class BusinessConnectionController extends Controller
{
    public function getBusinessConnection($business_id)
    {
        // get all connections where receiving_business_id = business_id or the  requesting_business_id = business_id
        $sent = BusinessConnection::where('requesting_business_id', $business_id)->get();
        $received = BusinessConnection::where('receiving_business_id', $business_id)->get();

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
}
