<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ResourceItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResourceItemController extends Controller
{
    public function create(Request $request, $business_id)
    {
        try {
            $request->validate([
                "item_name" => 'required|string|max:255',
                "category_id" => 'required|exists:resource_category,id',
                "quantity" => 'required|min:0',
                "unit" => 'required|string|max:50',
                "price" => 'required|numeric|min:0',
            ]);

            $data = $request->all();
            $data['business_id'] = $business_id;

            $resourceItem = ResourceItem::create($data);

            return response()->json([
                'error' => false,
                'message' => 'Resource item created successfully.',
                'data' => $resourceItem
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage()
            ]);
        }
    }

    public function read($business_id)
    {
        $business = Business::where('business_id', $business_id)->first();

        if (!$business) {
            return response()->json([
                'error' => true,
                'message' => 'Business not found.'
            ]);
        }

        $items = ResourceItem::where('business_id', $business_id)
            ->with('category')
            ->paginate(20);
        return response()->json([
            'error' => false,
            'message' => 'Resource items fetched successfully.',
            'data' => $items
        ]);
    }
}
