<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ItemBusiness;
use App\Models\ResourceItem;
use App\Models\TransactionItemHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResourceItemController extends Controller
{
    public function create(Request $request, $business_id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                "item_name" => 'required|string|max:255',
                "category_id" => 'required|exists:resource_category,id',
                "quantity" => 'required|min:0|numeric',
                "unit" => 'required|string|max:50',
                "price" => 'required|numeric|min:0',
                "date_added" => 'required',
                "unit" => 'string'
            ]);

            $data = $request->all();
            $data['business_id'] = $business_id;
            unset($data['quantity']);

            $resourceItem = ResourceItem::create($data);
            $itemBusiness = ItemBusiness::create([
                'business_id' => $business_id,
                'item_id' => $resourceItem->id,
                'source' => 'Owned',
                'quantity' => $request->input('quantity'),
            ]);

            TransactionItemHistory::create([
                'item_business_id' => $itemBusiness->id,
                'transaction_type' => 'Stock In',
                'quantity' => $request->input('quantity'),
                'transaction_time' => now(),
            ]);

            $item = ResourceItem::where('id', $resourceItem->id)->with('category')->first();
            $item->quantity = $request->input('quantity');
            DB::commit();
            return response()->json([
                'error' => false,
                'message' => 'Resource item created successfully.',
                'data' => $item
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

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

        $newData = ResourceItem::whereHas('itemsBusiness', function ($query) use ($business_id) {
            $query->where('business_id', $business_id);
        })
            ->where(function ($query) use ($search_text, $category_id) {
                if ($search_text) {
                    $query->where('item_name', 'like', '%' . $search_text . '%')
                        ->orWhere('description', 'like', '%' . $search_text . '%');
                }
                if ($category_id) {
                    $query->where('category_id', $category_id);
                }
            })
            ->with([
                'category',
                'itemsBusiness' => function ($query) use ($business_id) {
                    $query->where('business_id', $business_id)->select('item_id', 'quantity');
                }
            ])
            ->paginate(20);

        $newData->getCollection()->transform(function ($item) {
            if (isset($item->itemsBusiness[0])) {
                $item->quantity = $item->itemsBusiness[0]->quantity;
            } else {
                $item->quantity = 0;
            }
            unset($item->itemsBusiness);
            return $item;
        });




        return response()->json([
            'error' => false,
            'message' => 'Resource items fetched successfully.',
            'data' => $newData ?? [],
        ]);






        return response()->json([
            'error' => false,
            'message' => 'Resource items fetched successfully.',
            'data' => $items,

        ]);
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|exists:resource_item,id',
                "item_name" => 'required|string|max:255',
                "category_id" => 'required|exists:resource_category,id',
                "quantity" => 'required|min:0',
                "unit" => 'required|string|max:50',
                "price" => 'required|numeric|min:0',
            ]);
            $item = ResourceItem::where('id', $request->input('id'))->update([
                'item_name' => $request->input('item_name'),
                'description' => $request->input('description'),
                'category_id' => $request->input('category_id'),
                'quantity' => $request->input('quantity'),
                'unit' => $request->input('unit'),
                'price' => $request->input('price'),
                'item_image' => $request->input('item_image'),
            ]);
            return response()->json([
                'error' => false,
                'message' => 'Resource item updated successfully.',
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
            $item = ResourceItem::find($id);

            if (!$item) {
                return response()->json([
                    'error' => true,
                    'message' => 'Resource item not found.',
                ]);
            }

            $item->delete();

            return response()->json([
                'error' => false,
                'message' => 'Resource item deleted successfully.',
                'data' => $item
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An error occurred while deleting the resource item.',
                'details' => $e->getMessage()
            ]);
        }
    }
    public function getSingleResource($business_id, $itemId)
    {
        try {
            $businessItem = ItemBusiness::where('business_id', $business_id)
                ->with([
                    'items' => function ($query) {
                        $query->with('category');
                    },
                    'business'
                ])
                ->where('item_id', $itemId)
                ->first();
            return response()->json([
                'error' => false,
                'message' => "",
                'data' => $businessItem
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => true,
                'message' => $th->getMessage(),
            ]);
        }
    }
}
