<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    /**
     * Display a listing of the suppliers.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $business = Business::where('business_id', Auth::user()->business_id)->first();
        $suppliers = Supplier::where('business_id', Auth::user()->business_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Purchasing/Suppliers/Index', [
            'suppliers' => $suppliers,
            'business' => $business,
        ]);
    }

    /**
     * Show the form for creating a new supplier.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $business = Business::where('business_id', Auth::user()->business_id)->first();
        
        return Inertia::render('Purchasing/Suppliers/Create', [
            'business' => $business,
        ]);
    }

    /**
     * Store a newly created supplier in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:suppliers,code,NULL,id,business_id,' . Auth::user()->business_id,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:100',
            'lead_time_days' => 'nullable|integer|min:0',
            'status' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'website' => 'nullable|url|max:255'
        ]);

        $validated['business_id'] = Auth::user()->business_id;
        
        Supplier::create($validated);
        
        return redirect()->route('purchasing.suppliers')
            ->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified supplier.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Inertia\Response
     */
    public function show(Supplier $supplier)
    {
        if ($supplier->business_id !== Auth::user()->business_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Load related purchase orders
        $supplier->load('purchaseOrders');
        
        return Inertia::render('Purchasing/Suppliers/Show', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Show the form for editing the specified supplier.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Inertia\Response
     */
    public function edit(Supplier $supplier)
    {
        if ($supplier->business_id !== Auth::user()->business_id) {
            abort(403, 'Unauthorized action.');
        }
        
        return Inertia::render('Purchasing/Suppliers/Edit', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Update the specified supplier in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Supplier $supplier)
    {
        if ($supplier->business_id !== Auth::user()->business_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:suppliers,code,' . $supplier->id . ',id,business_id,' . Auth::user()->business_id,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:100',
            'lead_time_days' => 'nullable|integer|min:0',
            'status' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'website' => 'nullable|url|max:255',
        ]);
        
        $supplier->update($validated);
        
        return redirect()->route('purchasing.suppliers')
            ->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified supplier from storage.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Supplier $supplier)
    {
        if ($supplier->business_id !== Auth::user()->business_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if supplier has related purchase orders before allowing deletion
        if ($supplier->purchaseOrders()->count() > 0) {
            return redirect()->route('purchasing.suppliers')
                ->with('error', 'Cannot delete supplier as they have associated purchase orders.');
        }
        
        $supplier->delete();
        
        return redirect()->route('purchasing.suppliers')
            ->with('success', 'Supplier deleted successfully.');
    }

    /**
     * API: Display a listing of the suppliers.
     */
    public function apiIndex(Request $request)
    {
        $business = $request->user()->business_id ?? $request->business_id;
        $query = Supplier::where('business_id', $business);

        // Handle filters
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('rows', 10);
        $suppliers = $query->paginate($perPage);
        
        return response()->json($suppliers);
    }

    /**
     * API: Store a newly created supplier in storage.
     */
    public function apiStore(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:100',
            'lead_time_days' => 'nullable|integer|min:0',
            'status' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'website' => 'nullable|url|max:255'
        ]);

        $business = $request->user()->business_id ?? $request->business_id;
        $validatedData['business_id'] = $business;
        
        try {
            $supplier = Supplier::create($validatedData);
            
            return response()->json([
                'message' => 'Supplier created successfully',
                'supplier' => $supplier
            ], 201);
                
        } catch (\Exception $e) {
            Log::error('Failed to create supplier: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to create supplier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Display the specified supplier.
     */
    public function apiShow(Request $request, $id)
    {
        $business = $request->user()->business_id ?? $request->business_id;
        
        $supplier = Supplier::where('business_id', $business)
            ->with(['purchaseOrders' => function ($query) {
                $query->orderBy('created_at', 'desc')
                      ->with(['items' => function ($query) {
                            $query->with('item:id,item_name');
                        }
                      ]);
            }])
            ->findOrFail($id);
            
        return response()->json($supplier);
    }

    /**
     * API: Update the specified supplier in storage.
     */
    public function apiUpdate(Request $request, $id)
    {
        $business = $request->user()->business_id ?? $request->business_id;
        
        $supplier = Supplier::where('business_id', $business)
            ->findOrFail($id);
            
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:100',
            'lead_time_days' => 'nullable|integer|min:0',
            'status' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'website' => 'nullable|url|max:255'
        ]);
        
        try {
            $supplier->update($validatedData);
            
            return response()->json([
                'message' => 'Supplier updated successfully',
                'supplier' => $supplier
            ]);
                
        } catch (\Exception $e) {
            Log::error('Failed to update supplier: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to update supplier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Remove the specified supplier from storage.
     */
    public function apiDestroy(Request $request, $id)
    {
        $business = $request->user()->business_id ?? $request->business_id;
        
        $supplier = Supplier::where('business_id', $business)
            ->findOrFail($id);
            
        // Check if supplier has purchase orders
        $hasPurchaseOrders = $supplier->purchaseOrders()->exists();
        
        if ($hasPurchaseOrders) {
            return response()->json([
                'message' => 'Cannot delete supplier with associated purchase orders'
            ], 422);
        }
        
        try {
            $supplier->delete();
            
            return response()->json([
                'message' => 'Supplier deleted successfully'
            ]);
                
        } catch (\Exception $e) {
            Log::error('Failed to delete supplier: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to delete supplier',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
