<?php

namespace App\Http\Controllers;

use App\Models\MonthlyInventoryReport;
use App\Models\Inventory;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MonthlyInventoryCompilationExport;

class MonthlyInventoryReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Only admin and nurse can access reports
        if (!in_array($user->role, ['admin', 'nurse'])) {
            abort(403, 'Unauthorized to view reports');
        }
        
        $campus = $request->get('campus', $user->campus);
        
        // Admin can view all campuses, others only their own
        if ($user->role !== 'admin') {
            $campus = $user->campus;
        }

        $query = MonthlyInventoryReport::with(['generatedBy']);
        
        if ($campus) {
            $query->forCampus($campus);
        }

        $reports = $query->orderBy('report_year', 'desc')
            ->orderBy('report_month', 'desc')
            ->paginate(15);

        return Inertia::render('Reports/MonthlyInventory/Index', [
            'reports' => $reports,
            'currentCampus' => $campus,
            'canGenerateReport' => in_array($user->role, ['admin', 'nurse']),
            'isAdmin' => $user->role === 'admin',
        ]);
    }

    public function generate(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->role, ['admin', 'nurse'])) {
            abort(403, 'Unauthorized to generate reports');
        }

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2024|max:2030',
        ]);

        $month = $request->month;
        $year = $request->year;
        $campus = $user->campus;

        // Check if report already exists
        $existingReport = MonthlyInventoryReport::where([
            'campus' => $campus,
            'report_month' => $month,
            'report_year' => $year,
        ])->first();

        if ($existingReport) {
            return redirect()->route('monthly-reports.show', $existingReport)
                ->with('message', 'Report already exists for this period.');
        }

        // Get all medicines with their current inventory for this campus
        $medicines = Medicine::all();
        $inventoryData = [];
        $quantityToOrder = [];

        foreach ($medicines as $medicine) {
            // Calculate total quantity for this medicine across all batches/records at this campus
            $totalQuantity = Inventory::where('medicine_id', $medicine->id)
                ->where('campus', $campus)
                ->sum('quantity');

            $inventoryData[] = [
                'medicine_name' => $medicine->name,
                'current_stock' => $totalQuantity,
            ];

            // Initialize quantity to order as 0
            $quantityToOrder[$medicine->name] = 0;
        }

        // Create the report
        $report = MonthlyInventoryReport::create([
            'campus' => $campus,
            'report_month' => $month,
            'report_year' => $year,
            'inventory_data' => $inventoryData,
            'quantity_to_order' => $quantityToOrder,
            'status' => 'draft',
            'generated_by' => $user->id,
            'generated_at' => now(),
        ]);

        return redirect()->route('monthly-reports.show', $report)
            ->with('success', 'Monthly inventory report generated successfully.');
    }

    public function show(MonthlyInventoryReport $monthlyInventoryReport)
    {
        $user = Auth::user();
        
        // Check access permissions
        if ($user->role !== 'admin' && $user->campus !== $monthlyInventoryReport->campus) {
            abort(403, 'Unauthorized to view this report');
        }

        return Inertia::render('Reports/MonthlyInventory/Show', [
            'report' => $monthlyInventoryReport->load(['generatedBy']),
            'canEdit' => $this->canEditReport($user, $monthlyInventoryReport),
        ]);
    }

    public function updateOrderRequests(Request $request, MonthlyInventoryReport $monthlyInventoryReport)
    {
        $user = Auth::user();
        
        if (!$this->canEditReport($user, $monthlyInventoryReport)) {
            abort(403, 'Cannot edit this report');
        }

        // Debug: Log what we receive
        Log::info('updateOrderRequests called', [
            'report_id' => $monthlyInventoryReport->id,
            'user_id' => $user->id,
            'inventory_data_count' => count($request->inventory_data ?? []),
            'sample_data' => $request->inventory_data[0] ?? null
        ]);

        $request->validate([
            'inventory_data' => 'required|array',
            'inventory_data.*.medicine_name' => 'required|string',
            'inventory_data.*.current_stock' => 'required|integer|min:0',
            'inventory_data.*.quantity_to_order' => 'required|integer|min:0',
        ]);

        // Separate current stock data from quantity to order data
        $inventoryData = [];
        $quantityToOrder = [];

        foreach ($request->inventory_data as $item) {
            $inventoryData[] = [
                'medicine_name' => $item['medicine_name'],
                'current_stock' => $item['current_stock'],
            ];
            $quantityToOrder[$item['medicine_name']] = $item['quantity_to_order'];
        }

        $monthlyInventoryReport->update([
            'inventory_data' => $inventoryData,
            'quantity_to_order' => $quantityToOrder,
        ]);

        // Debug: Verify data was saved
        $monthlyInventoryReport->refresh();
        Log::info('updateOrderRequests saved data', [
            'report_id' => $monthlyInventoryReport->id,
            'quantity_to_order_count' => count($monthlyInventoryReport->quantity_to_order ?? []),
            'sample_orders' => array_slice($monthlyInventoryReport->quantity_to_order ?? [], 0, 3, true)
        ]);

        return redirect()->back()->with('success', 'Order requests updated successfully.');
    }

    public function submit(Request $request, MonthlyInventoryReport $monthlyInventoryReport)
    {
        $user = Auth::user();
        
        if (!$this->canEditReport($user, $monthlyInventoryReport)) {
            abort(403, 'Cannot submit this report');
        }

        // Debug: Check data before submission
        Log::info('submit called - data before update', [
            'report_id' => $monthlyInventoryReport->id,
            'current_status' => $monthlyInventoryReport->status,
            'current_submitted_at' => $monthlyInventoryReport->submitted_at,
            'quantity_to_order_count' => count($monthlyInventoryReport->quantity_to_order ?? [])
        ]);

        // Only update the status and submission timestamp
        // Don't overwrite the inventory data or quantity_to_order that was saved by updateOrderRequests
        $monthlyInventoryReport->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        // Debug: Check data after submission
        $monthlyInventoryReport->refresh();
        Log::info('submit completed - data after update', [
            'report_id' => $monthlyInventoryReport->id,
            'new_status' => $monthlyInventoryReport->status,
            'new_submitted_at' => $monthlyInventoryReport->submitted_at ? $monthlyInventoryReport->submitted_at->format('Y-m-d H:i:s') : 'NULL',
            'quantity_to_order_count' => count($monthlyInventoryReport->quantity_to_order ?? [])
        ]);

        return redirect()->back()->with('success', 'Report submitted successfully.');
    }

    public function compilation(Request $request)
    {
        $user = Auth::user();
        
        // Only admin can view compilation
        if ($user->role !== 'admin') {
            abort(403, 'Unauthorized to view compilation');
        }

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        // Get all submitted reports for the specified period
        $reports = MonthlyInventoryReport::where([
            'report_month' => $month,
            'report_year' => $year,
            'status' => 'submitted'
        ])->with('generatedBy')->get();

        // Organize data by campus
        $campusData = [];
        $allMedicines = [];

        foreach ($reports as $report) {
            $campus = $report->campus;
            
            // Combine inventory_data with quantity_to_order for complete data
            $combinedData = [];
            foreach ($report->inventory_data as $item) {
                $medicineName = $item['medicine_name'];
                $quantityToOrder = $report->quantity_to_order[$medicineName] ?? 0;
                
                // Handle different data structures for backward compatibility
                $currentStock = 0;
                if (isset($item['current_stock'])) {
                    // New format
                    $currentStock = $item['current_stock'];
                } elseif (isset($item['total_quantity'])) {
                    // Old format
                    $currentStock = $item['total_quantity'];
                }
                
                $combinedData[] = [
                    'medicine_name' => $medicineName,
                    'current_stock' => $currentStock,
                    'quantity_to_order' => $quantityToOrder
                ];
            }
            
            $campusData[$campus] = [
                'generated_at' => $report->generated_at->format('F j, Y'),
                'inventory_data' => $combinedData
            ];

            // Collect all unique medicine names
            foreach ($report->inventory_data as $item) {
                if (!in_array($item['medicine_name'], $allMedicines)) {
                    $allMedicines[] = $item['medicine_name'];
                }
            }
        }

        sort($allMedicines);

        return Inertia::render('Reports/MonthlyInventory/Compilation', [
            'month' => $month,
            'year' => $year,
            'campusData' => $campusData,
            'allMedicines' => $allMedicines,
        ]);
    }

    /**
     * Export compilation as Excel/XLSX with rich formatting
     */
    public function exportCompilationExcel(Request $request)
    {
        $user = Auth::user();
        
        // Only admin can export compilation
        if ($user->role !== 'admin') {
            abort(403, 'Unauthorized to export compilation');
        }

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        // Get all submitted reports for the specified period
        $reports = MonthlyInventoryReport::where([
            'report_month' => $month,
            'report_year' => $year,
            'status' => 'submitted'
        ])->with('generatedBy')->get();

        // Organize data by campus (same logic as other export methods)
        $campusData = [];
        $allMedicines = [];

        foreach ($reports as $report) {
            $campus = $report->campus;
            
            $combinedData = [];
            foreach ($report->inventory_data as $item) {
                $medicineName = $item['medicine_name'];
                $quantityToOrder = $report->quantity_to_order[$medicineName] ?? 0;
                
                $combinedData[] = [
                    'medicine_name' => $medicineName,
                    'current_stock' => $item['total_quantity'], // Fixed: use total_quantity instead of current_stock
                    'quantity_to_order' => $quantityToOrder
                ];
                
                if (!in_array($medicineName, $allMedicines)) {
                    $allMedicines[] = $medicineName;
                }
            }
            
            $campusData[$campus] = ['inventory_data' => $combinedData];
        }

        sort($allMedicines);

        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $reportPeriod = "$monthName $year";

        // Use the Excel export class with merged headers
        $export = new MonthlyInventoryCompilationExport($campusData, $allMedicines, $reportPeriod);
        
        $filename = "Monthly_Inventory_Compilation_{$monthName}_{$year}.xlsx";

        // Use Laravel Excel with proper merged headers and styling
        return Excel::download($export, $filename);
    }

    public function destroy(MonthlyInventoryReport $monthlyInventoryReport)
    {
        $user = Auth::user();
        
        // Check if user has permission to delete this report
        if (!$this->canEditReport($user, $monthlyInventoryReport)) {
            abort(403, 'Unauthorized to delete this report');
        }
        
        // Only allow deleting draft reports
        if ($monthlyInventoryReport->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft reports can be deleted.');
        }
        
        try {
            $reportPeriod = date('F Y', mktime(0, 0, 0, $monthlyInventoryReport->report_month, 1, $monthlyInventoryReport->report_year));
            $campus = $monthlyInventoryReport->campus;
            
            $monthlyInventoryReport->delete();
            
            Log::info('Monthly inventory report deleted', [
                'report_id' => $monthlyInventoryReport->id,
                'period' => $reportPeriod,
                'campus' => $campus,
                'deleted_by' => $user->id
            ]);
            
            return redirect()->route('monthly-reports.index')->with('success', 
                "Draft report for {$reportPeriod} ({$campus}) has been successfully deleted.");
        } catch (\Exception $e) {
            Log::error('Failed to delete monthly inventory report', [
                'report_id' => $monthlyInventoryReport->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return redirect()->back()->with('error', 'Failed to delete the report. Please try again.');
        }
    }

    private function canEditReport($user, $report)
    {
        return ($user->campus === $report->campus && 
                in_array($user->role, ['admin', 'nurse']) && 
                $report->status === 'draft');
    }
}