<?php

namespace App\Http\Controllers;

use App\Models\PatientConsultationRecord;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get today's patient visits
        $todayVisits = PatientConsultationRecord::whereDate('consultation_date_time', Carbon::today())->count();
        
        // Get low stock items
        $lowStockItems = Inventory::with('medicine')
            ->whereRaw('quantity <= low_stock_threshold')
            ->take(5)
            ->get();
        
        // Get medicines nearing expiry (next 30 days)
        $nearingExpiry = Inventory::with('medicine')
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>=', Carbon::now())
            ->take(5)
            ->get();
        
        // Recent patients (last 10)
        $recentPatients = PatientConsultationRecord::orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'todayVisits' => $todayVisits,
                'lowStockCount' => $lowStockItems->count(),
                'nearingExpiryCount' => $nearingExpiry->count(),
                'totalPatients' => PatientConsultationRecord::distinct('student_employee_id')->count(),
            ],
            'lowStockItems' => $lowStockItems,
            'nearingExpiry' => $nearingExpiry,
            'recentPatients' => $recentPatients,
        ]);
    }
}
