<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ResourceCategory;
use App\Models\ResourceItem;
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

    public function read(Request $request, $business_id)
    {
        try {
            $rows = (int)$request->query('rows', 20);
            $page = (int)$request->query('page', 1);

            if ($rows <= 0 || $page <= 0) {
                return response()->json([
                    'error' => true,
                    'message' => 'Invalid pagination parameters.'
                ], 400);
            }

            $business = Business::where('business_id', $business_id)->first();

            if (!$business) {
                return response()->json([
                    'error' => true,
                    'message' => 'Business not found.'
                ]);
            }

            $items = ResourceCategory::where('business_id', $business_id)
                ->paginate($rows, ['*'], 'page', $page);

            return response()->json([
                'error' => false,
                'message' => 'Resource categories retrieved successfully.',
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while fetching resource categories.'
            ], 500);
        }
    }


    public function openItem($id)
    {


        return Inertia::render('Inventory/ViewResource', [
            'itemId' => $id
        ]);
    }
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|exists:resource_category,id',
                "name" => 'required|string|max:255',

            ]);
            $item = ResourceCategory::where('id', $request->input('id'))->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
            ]);
            return response()->json([
                'error' => false,
                'message' => 'Resource category updated successfully.',
                'data' => $item
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

    public function delete($id)
    {
        try {
            $item = ResourceCategory::find($id);
            if ($item) {
                $item->delete();
                return response()->json([
                    'error' => false,
                    'message' => 'Resource category deleted successfully.'
                ]);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Resource category not found.'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage()
            ]);
        }
    }
}
