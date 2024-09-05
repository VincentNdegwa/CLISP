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

            $requestExists = BusinessConnection::where(function ($query) {
                $query->where('connection_status', '=', 'approved')
                    ->orWhere('connection_status', '=', 'pending');
            })->where(function ($query) use ($validData) {
                $query->where('requesting_business_id', $validData['requesting_business_id'])
                    ->where('receiving_business_id', $validData['receiving_business_id']);
            })->orWhere(function ($query) use ($validData) {
                $query->where('requesting_business_id', $validData['receiving_business_id'])
                    ->where('receiving_business_id', $validData['requesting_business_id']);
            })->first();

            if ($requestExists) {
                return response()->json([
                    "error" => true,
                    "message" => "Request already exists, Reject the exist request to create a new one",
                    "data" => $requestExists
                ]);
            }
            $business_request = BusinessConnection::create($request->all());
            $request = BusinessConnection::where('id', $business_request->id)
                ->with('businessRequester', 'businessReceiver', 'userRequester', 'userReceiver')
                ->first();
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


    public function approveConnectionRequest(Request $request)
    {
        return $this->updateConnectionStatusWithUser($request, 'approved', 'Connection Request Approved');
    }

    public function rejectConnectionRequest(Request $request)
    {
        return $this->updateConnectionStatusWithUser($request, 'rejected', 'Connection Request Rejected');
    }

    public function cancelConnectionRequest(Request $request)
    {
        return $this->updateConnectionStatus($request, 'cancelled', 'Connection Request Cancelled');
    }

    public function terminateConnection(Request $request)
    {
        return $this->updateConnectionStatus($request, 'terminated', 'Connection Terminated', ['pending', 'approved']);
    }

    private function updateConnectionStatusWithUser(Request $request, $newStatus, $successMessage, $allowedStatuses = ['pending'])
    {
        $validatedData = $request->validate([
            'request_id' => 'required|exists:business_connection,id',
            'receiving_user_id' => 'required|exists:users,id',
        ]);

        $connectionRequest = BusinessConnection::where('id', $validatedData['request_id'])->first();

        if (!in_array($connectionRequest->connection_status, $allowedStatuses)) {
            return response()->json([
                'error' => true,
                'message' => 'Action not allowed on the current connection status.',
            ], 400);
        }

        $connectionRequest->connection_status = $newStatus;
        $connectionRequest->receiving_user_id = $validatedData['receiving_user_id'];
        $connectionRequest->save();

        return response()->json([
            'error' => false,
            'message' => $successMessage,
        ]);
    }

    private function updateConnectionStatus(Request $request, $newStatus, $successMessage, $allowedStatuses = ['pending'])
    {
        $validatedData = $request->validate([
            'request_id' => 'required|exists:business_connection,id',
        ]);

        $connectionRequest = BusinessConnection::where('id', $validatedData['request_id'])->first();

        if (!in_array($connectionRequest->connection_status, $allowedStatuses)) {
            return response()->json([
                'error' => true,
                'message' => 'Action not allowed on the current connection status.',
            ], 400);
        }

        $connectionRequest->connection_status = $newStatus;
        $connectionRequest->save();

        return response()->json([
            'error' => false,
            'message' => $successMessage,
        ]);
    }
}
