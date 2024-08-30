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
                "date_added" => 'required'
            ]);

            $data = $request->all();
            $data['business_id'] = $business_id;

            $resourceItem = ResourceItem::create($data);
            $item = ResourceItem::where('id', $resourceItem->id)->with('category')->first();

            return response()->json([
                'error' => false,
                'message' => 'Resource item created successfully.',
                'data' => $item
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

    public function read($business_id, Request $request)
    {
        $business = Business::where('business_id', $business_id)->first();

        if (!$business) {
            return response()->json([
                'error' => true,
                'message' => 'Business not found.'
            ]);
        }

        $search_text = $request->query('search');
        $category_id = $request->query('category');

        $query = ResourceItem::where('business_id', $business_id)->with('category');

        if ($search_text) {
            $query->where('item_name', 'like', '%' . $search_text . '%')
                ->orWhere('description', 'like', '%' . $search_text . '%');
        }
        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        $items = $query->paginate(20);

        return response()->json([
            'error' => false,
            'message' => 'Resource items fetched successfully.',
            'data' => $items
        ]);
    }
}
