<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ResourceCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ResourceCategoryController extends Controller
{

    public function view()
    {
        return Inertia::render("Inventory/ResourceCategory", []);
    }
    public function create(Request $request, $business_id)
    {
        try {
            $request->validate([
                "name" => 'required'
            ]);
            $data = $request->all();
            $data['business_id'] = $business_id;

            $category = ResourceCategory::create($data);
            $new_category = ResourceCategory::where('id', $category->id)->first();
            return response()->json([
                'error' => false,
                'message' => 'Category created!',
                'data' => $new_category
            ]);
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

        $items = ResourceCategory::where('business_id', $business_id)
            ->paginate(20);
        return response()->json([
            'error' => false,
            'message' => 'Resource category fetched successfully.',
            'data' => $items
        ]);
    }
}
