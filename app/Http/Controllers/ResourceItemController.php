<?php

namespace App\Http\Controllers;

use App\Models\ResourceItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResourceItemController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validate = $request->validate([
                "business_id" => 'required|exists:businesses,id',
                "item_name" => 'required|string|max:255',
                "category_id" => 'required|exists:resource_category,id',
                "quantity" => 'required|min:0',
                "unit" => 'required|string|max:50',
                "price" => 'required|numeric|min:0',
            ]);

            $resourceItem = ResourceItem::create($request->all());

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
}
