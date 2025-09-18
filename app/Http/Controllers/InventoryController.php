<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $type = $request->get('type', 'all'); // all, medicines, supplies
        $search = $request->get('search', '');

        $query = Inventory::with('medicine');

        // Filter by type
        if ($type === 'medicines') {
            $query->whereHas('medicine', function($q) {
                $q->where('type', '!=', 'Equipment')
                  ->where('type', '!=', 'Supply')
                  ->where('type', '!=', 'Medical Supply');
            });
        } elseif ($type === 'supplies') {
            $query->whereHas('medicine', function($q) {
                $q->whereIn('type', ['Equipment', 'Supply', 'Medical Supply']);
            });
        }

        // Search functionality
        if ($search) {
            $query->whereHas('medicine', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('type', 'LIKE', "%{$search}%");
            })->orWhere('batch_number', 'LIKE', "%{$search}%")
              ->orWhere('supplier', 'LIKE', "%{$search}%");
        }

        $inventory = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $medicines = Medicine::orderBy('name')->get();
        $supplies = Medicine::whereIn('type', ['Equipment', 'Supply', 'Medical Supply'])
            ->orderBy('name')->get();

        // Get summary stats
        $stats = [
            'total_items' => Inventory::count(),
            'medicines_count' => Inventory::whereHas('medicine', function($q) {
                $q->where('type', '!=', 'Equipment')
                  ->where('type', '!=', 'Supply')
                  ->where('type', '!=', 'Medical Supply');
            })->count(),
            'supplies_count' => Inventory::whereHas('medicine', function($q) {
                $q->whereIn('type', ['Equipment', 'Supply', 'Medical Supply']);
            })->count(),
            'low_stock_count' => Inventory::whereRaw('quantity <= low_stock_threshold')->count(),
            'expiring_soon_count' => Inventory::where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>=', now())->count(),
        ];

        return Inertia::render('Inventory/Index', [
            'inventory' => $inventory,
            'medicines' => $medicines,
            'supplies' => $supplies,
            'stats' => $stats,
            'filters' => [
                'type' => $type,
                'search' => $search,
                'per_page' => $perPage,
            ]
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

    public function storeMedicine(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'dosage_strength' => 'nullable|string|max:100',
            'form' => 'nullable|string|max:100',
        ]);

        $medicine = Medicine::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'unit' => $request->unit,
            'dosage_strength' => $request->dosage_strength,
            'form' => $request->form,
        ]);

        return response()->json($medicine, 201);
    }
}
