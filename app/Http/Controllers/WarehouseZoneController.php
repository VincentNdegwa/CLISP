<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WarehouseZoneController extends Controller
{
    /**
     * Display a listing of warehouse zones.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = WarehouseZone::with('warehouse');
        
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('zone_type') && !empty($request->zone_type)) {
            $query->where('zone_type', $request->zone_type);
        }
        
        if ($request->has('temperature_controlled')) {
            $query->where('temperature_controlled', $request->boolean('temperature_controlled'));
        }
        
        $perPage = $request->get('rows', 10);
        $zones = $query->paginate($perPage);
        
        return response()->json($zones);
    }

    /**
     * Store a newly created warehouse zone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $zoneTypes = ['storage', 'picking', 'packing', 'receiving', 'shipping', 'returns', 'quarantine'];
        $statusOptions = ['active', 'inactive', 'maintenance'];
        
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:warehouse_zones,code',
            'description' => 'nullable|string',
            'zone_type' => [
                'required',
                Rule::in($zoneTypes),
            ],
            'temperature_controlled' => 'boolean',
            'min_temperature' => 'nullable|required_if:temperature_controlled,true|numeric',
            'max_temperature' => 'nullable|required_if:temperature_controlled,true|numeric|gte:min_temperature',
            'temperature_unit' => 'nullable|required_if:temperature_controlled,true|string|in:C,F',
            'status' => [
                'nullable',
                Rule::in($statusOptions),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate a unique code if none is provided
        if (!$request->filled('code')) {
            $request->merge([
                'code' => $this->generateUniqueZoneCode($request->warehouse_id)
            ]);
        }
        
        // Set default values if not provided
        if (!$request->filled('status')) {
            $request->merge(['status' => 'active']);
        }

        $zone = WarehouseZone::create($request->all());

        return response()->json([
            'message' => 'Warehouse zone created successfully',
            'zone' => $zone
        ], 201);
    }

    /**
     * Display the specified warehouse zone.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $zone = WarehouseZone::with('warehouse', 'binLocations')->findOrFail($id);
        
        return response()->json($zone);
    }

    /**
     * Update the specified warehouse zone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $zone = WarehouseZone::findOrFail($id);
        
        $zoneTypes = ['storage', 'picking', 'packing', 'receiving', 'shipping', 'returns', 'quarantine'];
        $statusOptions = ['active', 'inactive', 'maintenance'];
        
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'name' => 'sometimes|required|string|max:255',
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('warehouse_zones')->ignore($zone->id),
            ],
            'description' => 'nullable|string',
            'zone_type' => [
                'sometimes',
                'required',
                Rule::in($zoneTypes),
            ],
            'temperature_controlled' => 'boolean',
            'min_temperature' => 'nullable|required_if:temperature_controlled,true|numeric',
            'max_temperature' => 'nullable|required_if:temperature_controlled,true|numeric|gte:min_temperature',
            'temperature_unit' => 'nullable|required_if:temperature_controlled,true|string|in:C,F',
            'status' => [
                'nullable',
                Rule::in($statusOptions),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $zone->update($request->all());

        return response()->json([
            'message' => 'Warehouse zone updated successfully',
            'zone' => $zone->fresh()
        ]);
    }

    /**
     * Remove the specified warehouse zone.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $zone = WarehouseZone::findOrFail($id);
        
        // Check if zone has bin locations
        if ($zone->binLocations()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete zone with associated bin locations',
                'errors' => ['zone' => 'This zone has associated bin locations']
            ], 422);
        }
        
        $zone->delete();
        
        return response()->json([
            'message' => 'Warehouse zone deleted successfully'
        ]);
    }
    
 
    public function getZoneTypes()
    {
        $zoneTypes = [
            ['value' => 'storage', 'label' => 'Storage'],
            ['value' => 'picking', 'label' => 'Picking'],
            ['value' => 'packing', 'label' => 'Packing'],
            ['value' => 'receiving', 'label' => 'Receiving'],
            ['value' => 'shipping', 'label' => 'Shipping'],
            ['value' => 'returns', 'label' => 'Returns'],
            ['value' => 'quarantine', 'label' => 'Quarantine'],
        ];
        
        return response()->json($zoneTypes);
    }
    
 
    public function getZonesByWarehouse($warehouseId)
    {
        $warehouse = Warehouse::findOrFail($warehouseId);
        
        $zones = WarehouseZone::where('warehouse_id', $warehouseId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
            
        return response()->json($zones);
    }
    
  
    private function generateUniqueZoneCode($warehouseId)
    {
        $warehouse = Warehouse::find($warehouseId);
        $warehouseCode = $warehouse ? substr($warehouse->code, 0, 3) : 'WH';
        
        $count = WarehouseZone::where('warehouse_id', $warehouseId)->count() + 1;
        $formattedCount = str_pad($count, 3, '0', STR_PAD_LEFT);
        
        $code = $warehouseCode . '-ZONE-' . $formattedCount;
        
        while (WarehouseZone::where('code', $code)->exists()) {
            $count++;
            $formattedCount = str_pad($count, 3, '0', STR_PAD_LEFT);
            $code = $warehouseCode . '-ZONE-' . $formattedCount;
        }
        
        return $code;
    }
}