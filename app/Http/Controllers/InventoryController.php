<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $type = $request->get('type', 'all'); // all, medicines, supplies
        $search = $request->get('search', '');
        $view = $request->get('view', 'grouped'); // grouped, detailed

        $user = Auth::user();
        $query = Inventory::with('medicine');

        // Filter by campus (only show inventory for user's campus unless admin)
        if ($user->role !== 'admin') {
            $query->where('campus', $user->campus);
        }

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
              ->orWhere('distributor', 'LIKE', "%{$search}%");
        }

        if ($view === 'grouped') {
            // Group by medicine and calculate totals
            $inventoryData = $query->get()->groupBy('medicine_id')->map(function($batches, $medicineId) {
                $medicine = $batches->first()->medicine;
                $totalQuantity = $batches->sum('quantity');
                $batchCount = $batches->count();
                $lowStockBatches = $batches->filter(function($batch) {
                    return $batch->quantity <= $batch->low_stock_threshold;
                })->count();
                $expiringBatches = $batches->filter(function($batch) {
                    return $batch->expiry_date <= now()->addDays(30);
                })->count();
                $earliestExpiry = $batches->min('expiry_date');
                $latestAdded = $batches->max('date_added');

                return [
                    'medicine_id' => $medicineId,
                    'medicine' => $medicine,
                    'total_quantity' => $totalQuantity,
                    'batch_count' => $batchCount,
                    'low_stock_batches' => $lowStockBatches,
                    'expiring_batches' => $expiringBatches,
                    'earliest_expiry' => $earliestExpiry,
                    'latest_added' => $latestAdded,
                    'batches' => $batches->values(),
                ];
            })->values();

            // Paginate grouped results manually
            $currentPage = $request->get('page', 1);
            $total = $inventoryData->count();
            $offset = ($currentPage - 1) * $perPage;
            $items = $inventoryData->slice($offset, $perPage)->values();

            $inventory = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'pageName' => 'page',
                ]
            );
            $inventory->withQueryString();
        } else {
            // Original detailed view
            $inventory = $query->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        }

        $medicines = Medicine::orderBy('name')->get();
        $supplies = Medicine::whereIn('type', ['Equipment', 'Supply', 'Medical Supply'])
            ->orderBy('name')->get();

        // Get summary stats (campus-filtered for non-admin users)
        $statsQuery = Auth::user()->role === 'admin' ? 
            Inventory::query() : 
            Inventory::where('campus', Auth::user()->campus);

        $stats = [
            'total_items' => $statsQuery->count(),
            'medicines_count' => (clone $statsQuery)->whereHas('medicine', function($q) {
                $q->where('type', '!=', 'Equipment')
                  ->where('type', '!=', 'Supply')
                  ->where('type', '!=', 'Medical Supply');
            })->count(),
            'supplies_count' => (clone $statsQuery)->whereHas('medicine', function($q) {
                $q->whereIn('type', ['Equipment', 'Supply', 'Medical Supply']);
            })->count(),
            'low_stock_count' => (clone $statsQuery)->whereRaw('quantity <= low_stock_threshold')->count(),
            'expiring_soon_count' => (clone $statsQuery)->where('expiry_date', '<=', now()->addDays(30))
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
                'view' => $view,
            ],
            'canGenerateReport' => Auth::user()->canGenerateInventoryReports(),
            'canCompileReports' => Auth::user()->canCompileReports(),
            'canManageInventory' => Auth::user()->isAdmin(),
        ]);
    }

    /**
     * Generate inventory report for current campus
     */
    public function generateReport()
    {
        $user = Auth::user();
        
        if (!$user->canGenerateInventoryReports()) {
            abort(403, 'Unauthorized to generate inventory reports');
        }

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Check if report already exists for this month
        $existingReport = \App\Models\MonthlyInventoryReport::where('campus', $user->campus)
            ->where('report_month', $currentMonth)
            ->where('report_year', $currentYear)
            ->first();

        // Get current inventory data for user's campus (including both medicines and supplies)
        $inventoryData = Inventory::where('campus', $user->campus)
            ->with('medicine')
            ->get()
            ->groupBy('medicine.name')
            ->map(function ($batches, $medicineName) {
                $totalQuantity = $batches->sum('quantity');
                $lowestThreshold = $batches->min('low_stock_threshold');
                $earliestExpiry = $batches->min('expiry_date');
                $batchCount = $batches->count();
                $medicineType = $batches->first()->medicine->type;
                
                // Determine if this is a supply or medicine
                $isSupply = in_array($medicineType, ['Equipment', 'Supply', 'Medical Supply']);
                
                return [
                    'medicine_name' => $medicineName,
                    'medicine_type' => $medicineType,
                    'is_supply' => $isSupply,
                    'total_quantity' => $totalQuantity,
                    'batch_count' => $batchCount,
                    'low_stock_threshold' => $lowestThreshold,
                    'is_low_stock' => $totalQuantity <= $lowestThreshold,
                    'earliest_expiry' => $earliestExpiry,
                    'is_expiring_soon' => \Carbon\Carbon::parse($earliestExpiry)->lte(now()->addDays(30)),
                    'batches' => $batches->map(function ($batch) {
                        return [
                            'batch_number' => $batch->batch_number,
                            'quantity' => $batch->quantity,
                            'expiry_date' => $batch->expiry_date,
                            'distributor' => $batch->distributor,
                            'date_added' => $batch->date_added,
                        ];
                    })->toArray(),
                ];
            })
            ->values()
            ->toArray();

        return Inertia::render('Inventory/GenerateReport', [
            'inventoryData' => $inventoryData,
            'campus' => $user->campus,
            'reportDate' => now()->format('Y-m-d'),
            'existingReport' => $existingReport,
            'reportMonth' => now()->format('F Y'),
        ]);
    }

    public function saveReport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->canGenerateInventoryReports()) {
            abort(403, 'Unauthorized to save inventory reports');
        }

        // Log the incoming request data
        Log::info('saveReport called with data:', [
            'user_id' => $user->id,
            'campus' => $user->campus,
            'has_inventory_data' => $request->has('inventory_data'),
            'has_order_requests' => $request->has('order_requests'),
            'inventory_data_count' => $request->has('inventory_data') ? count($request->get('inventory_data', [])) : 0,
            'order_requests_count' => $request->has('order_requests') ? count($request->get('order_requests', [])) : 0,
            'raw_request' => $request->all(),
        ]);

        try {
            $request->validate([
                'inventory_data' => 'required|array',
                'order_requests' => 'nullable|array',
                'order_requests.*.medicine_name' => 'sometimes|required|string',
                'order_requests.*.quantity_to_order' => 'sometimes|required|integer|min:0',
            ], [
                'inventory_data.required' => 'Inventory data is required',
                'inventory_data.array' => 'Inventory data must be an array',
                'order_requests.array' => 'Order requests must be an array',
                'order_requests.*.medicine_name.required' => 'Medicine name is required for order requests',
                'order_requests.*.quantity_to_order.required' => 'Quantity to order is required',
                'order_requests.*.quantity_to_order.integer' => 'Quantity to order must be an integer',
                'order_requests.*.quantity_to_order.min' => 'Quantity to order cannot be negative',
            ]);
            
            Log::info('Validation passed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        }

        // Convert order requests to quantity_to_order format (key-value mapping)
        $quantityToOrder = [];
        foreach ($request->order_requests ?? [] as $order) {
            $quantityToOrder[$order['medicine_name']] = $order['quantity_to_order'] ?? 0;
        }

        Log::info('All order requests (including zeros):', [
            'total_count' => count($request->get('order_requests', [])),
            'quantity_to_order' => $quantityToOrder,
        ]);

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Check if report already exists for this campus and month
        $existingReport = \App\Models\MonthlyInventoryReport::where('campus', $user->campus)
            ->where('report_month', $currentMonth)
            ->where('report_year', $currentYear)
            ->first();

        if ($existingReport) {
            // Update existing report instead of creating new one
            $existingReport->update([
                'inventory_data' => $request->inventory_data,
                'quantity_to_order' => $quantityToOrder,
                'status' => $request->get('status', 'draft'),
                'generated_by' => $user->id,
                'updated_at' => now(),
            ]);

            Log::info('Updated existing report:', [
                'report_id' => $existingReport->id,
                'status' => $existingReport->status,
            ]);

            return redirect()->route('monthly-reports.show', $existingReport)
                ->with('success', 'Inventory report updated successfully');
        }

        // Create new report if none exists
        $report = \App\Models\MonthlyInventoryReport::create([
            'campus' => $user->campus,
            'report_month' => $currentMonth,
            'report_year' => $currentYear,
            'generated_by' => $user->id,
            'inventory_data' => $request->inventory_data,
            'quantity_to_order' => $quantityToOrder,
            'status' => $request->get('status', 'draft'),
        ]);

        Log::info('Created new report:', [
            'report_id' => $report->id,
            'status' => $report->status,
        ]);

        return redirect()->route('monthly-reports.show', $report)
            ->with('success', 'Inventory report saved successfully');
    }

    public function store(Request $request)
    {
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'quantity' => 'required|integer|min:0',
            'expiry_date' => 'required|date',
            'batch_number' => 'nullable|string',
            'distributor' => 'nullable|string',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        $data = $request->all();
        $data['campus'] = Auth::user()->campus;
        $data['date_added'] = now();
        
        $inventory = Inventory::create($data);

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
            'distributor' => 'nullable|string',
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
