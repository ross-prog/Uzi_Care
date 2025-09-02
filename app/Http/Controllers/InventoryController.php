<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        $inventory = Inventory::with('medicine')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $medicines = Medicine::orderBy('name')->get();

        return Inertia::render('Inventory/Index', [
            'inventory' => $inventory,
            'medicines' => $medicines
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'quantity' => 'required|integer|min:0',
            'expiry_date' => 'required|date',
            'batch_number' => 'nullable|string',
            'supplier' => 'nullable|string',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        $inventory = Inventory::create($request->all());

        return response()->json([
            'message' => 'Inventory item added successfully',
            'item' => $inventory->load('medicine')
        ]);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
            'expiry_date' => 'required|date',
            'batch_number' => 'nullable|string',
            'supplier' => 'nullable|string',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        $inventory->update($request->all());

        return response()->json([
            'message' => 'Inventory item updated successfully',
            'item' => $inventory->load('medicine')
        ]);
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return response()->json([
            'message' => 'Inventory item deleted successfully'
        ]);
    }

    public function lowStock()
    {
        $lowStockItems = Inventory::with('medicine')
            ->whereRaw('quantity <= low_stock_threshold')
            ->get();

        return response()->json($lowStockItems);
    }

    public function nearingExpiry()
    {
        $nearingExpiry = Inventory::with('medicine')
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>=', now())
            ->get();

        return response()->json($nearingExpiry);
    }
}
