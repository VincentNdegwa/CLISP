<?php

namespace App\Http\Controllers;

use App\Models\ResourceCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResourceCategoryController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validate = $request->validate([
                "business_id" => 'required|exists:business,business_id',
                "name" => 'required'
            ]);
            $category = ResourceCategory::create($request->all());
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
}
