<?php

namespace App\Http\Controllers;

use App\Models\PatientConsultationRecord;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    public function patientVisits(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonth());
        $endDate = $request->input('end_date', Carbon::now());

        $visits = PatientConsultationRecord::whereBetween('consultation_date_time', [$startDate, $endDate])
            ->selectRaw('DATE(consultation_date_time) as visit_date, COUNT(*) as count')
            ->groupBy('visit_date')
            ->orderBy('visit_date')
            ->get();

        // For visit types, we can group by diagnosis or create simple categories
        $visitTypes = PatientConsultationRecord::whereBetween('consultation_date_time', [$startDate, $endDate])
            ->selectRaw('
                CASE 
                    WHEN chief_complaints LIKE "%emergency%" OR chief_complaints LIKE "%urgent%" THEN "Emergency"
                    WHEN chief_complaints LIKE "%follow%" OR chief_complaints LIKE "%check%" THEN "Follow-up"
                    WHEN chief_complaints LIKE "%consultation%" THEN "Consultation"
                    ELSE "General"
                END as visit_type, COUNT(*) as count
            ')
            ->groupBy('visit_type')
            ->get();

        return response()->json([
            'visits' => $visits,
            'visitTypes' => $visitTypes
        ]);
    }

    public function inventoryReport()
    {
        $lowStock = Inventory::with('medicine')
            ->whereRaw('quantity <= low_stock_threshold')
            ->get();

        $nearingExpiry = Inventory::with('medicine')
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>=', Carbon::now())
            ->get();

        $totalValue = (float) Inventory::sum(DB::raw('quantity * cost_per_unit')) ?: 0;

        return response()->json([
            'lowStock' => $lowStock,
            'nearingExpiry' => $nearingExpiry,
            'totalValue' => $totalValue
        ]);
    }
}
