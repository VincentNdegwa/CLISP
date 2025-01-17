<?php

namespace App\Http\Controllers;

use App\Models\PaymentInformation;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class BusinessPaymentsController extends Controller
{
    public function getPaymentMethods(Request $request, $business_id)
    {
        $methods = PaymentMethod::where(function ($query) use ($business_id) {
            $query->where("business_id", $business_id)
                ->orWhere("default", 'true');
        });

        if ($request->has('category')) {
            $methods->where('category', $request->category);
        }

        if ($request->has('search')) {
            $methods->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status')) {
            $methods->where('status', $request->status);
        }


        $methods = $methods->get();

        return response()->json($methods);
    }
    public function createOrUpdatePaymentInformation($business_id, Request $request)
    {
        $data = $request->all();

        if (isset($data['payment_details']) && is_array($data['payment_details']) && !empty($data['payment_details'])) {
            $data['payment_details'] = json_encode($data['payment_details']);
            $method = PaymentInformation::firstOrNew([
                'business_id' => $business_id,
                'payment_type' => $request->input('payment_type'),
            ]);

            $method->fill($data);
            $method->save();

            if (!PaymentInformation::where('business_id', $business_id)->where('default', 'true')->exists()) {
                $method->update(['default' => 'true']);
            }

            $method->refresh();
            $method->payment_details = json_decode($method->payment_details);

            return response()->json([
                'error' => false,
                'message' => 'Payment information saved successfully.',
                'data' => $method,
            ]);
        } else {
            $method = PaymentInformation::where([
                'business_id' => $business_id,
                'payment_type' => $request->input('payment_type'),
            ])->first();

            $returnData = $method;
            $method->delete();
            $returnData->payment_details = [];

            return response()->json([
                'error' => false,
                'message' => 'Payment information deleted successfully.',
                'data' => $returnData
            ]);
        }
    }


    public function getPaymentInformation($business_id)
    {
        $method = PaymentInformation::where('business_id', $business_id)->get();
        if ($method) {
            $method->map(function ($payment) {
                $payment->payment_details = json_decode($payment->payment_details);
                return $payment;
            });
        }
        return response()->json($method);
    }

    public function fetchSinglePaymentInformation($business_id, Request $request)
    {
        $payment = PaymentInformation::where('business_id', $business_id)->where('payment_type', $request->input('payment_type'))->first();
        if ($payment) {
            $payment->payment_details = json_decode($payment->payment_details);
        }
        return response()->json($payment);
    }
    public function setDefault($business_id, $payment_id)
    {
        PaymentInformation::where('business_id', $business_id)->update(['default' => 'false']);
        PaymentInformation::where('business_id', $business_id)->where('id', $payment_id)->update(['default' => 'true']);
        return response()->json(['error' => false, 'message' => 'Payment method set as default successfully.', 'data' => $this->fetchPayment($business_id, $payment_id)]);
    }

    private function fetchPayment($business_id, $payment_id)
    {
        $payment = PaymentInformation::where('business_id', $business_id)->where('id', $payment_id)->first();
        if ($payment) {
            $payment->payment_details = json_decode($payment->payment_details);
        }
        return $payment;
    }
}
