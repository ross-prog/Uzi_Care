<?php

namespace App\Http\Controllers;

use App\Models\PatientConsultationRecord;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get today's patient visits
        $todayVisits = PatientConsultationRecord::whereDate('consultation_date_time', Carbon::today())->count();
        
        // Get low stock items - fixed to ensure proper counting
        $lowStockItems = Inventory::with('medicine')
            ->whereRaw('quantity <= low_stock_threshold')
            ->take(5)
            ->get();
        
        // Get medicines nearing expiry (next 30 days) - fixed to ensure proper counting
        $nearingExpiry = Inventory::with('medicine')
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>=', Carbon::now())
            ->take(5)
            ->get();
        
        // Recent patients (last 10)
        $recentPatients = PatientConsultationRecord::orderBy('consultation_date_time', 'desc')
            ->take(10)
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'first_name' => $patient->first_name,
                    'middle_name' => $patient->middle_name,
                    'last_name' => $patient->last_name,
                    'full_name' => $patient->getFullNameAttribute(),
                    'student_employee_id' => $patient->student_employee_id,
                    'department_course' => $patient->department_course,
                    'consultation_date_time' => $patient->consultation_date_time,
                    'chief_complaints' => $patient->chief_complaints,
                    'diagnosis' => $patient->diagnosis,
                ];
            });

        // Get actual counts for stats
        $lowStockCount = Inventory::whereRaw('quantity <= low_stock_threshold')->count();
        $nearingExpiryCount = Inventory::where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>=', Carbon::now())
            ->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'todayVisits' => $todayVisits,
                'lowStockCount' => $lowStockCount,
                'nearingExpiryCount' => $nearingExpiryCount,
                'totalPatients' => PatientConsultationRecord::distinct('student_employee_id')->count(),
            ],
            'lowStockItems' => $lowStockItems,
            'nearingExpiry' => $nearingExpiry,
            'recentPatients' => $recentPatients,
        ]);
    }

    /**
     * Admin dashboard with additional system metrics
     */
    public function admin()
    {
        // Get all dashboard data plus admin-specific metrics
        $todayVisits = PatientConsultationRecord::whereDate('consultation_date_time', Carbon::today())->count();
        $lowStockCount = Inventory::whereRaw('quantity <= low_stock_threshold')->count();
        $nearingExpiryCount = Inventory::where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>=', Carbon::now())
            ->count();

        // Admin-specific metrics
        $totalUsers = User::where('is_active', true)->count();
        $usersByRole = User::where('is_active', true)
            ->selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role');

        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'name', 'email', 'role', 'department', 'last_login_at', 'created_at']);

        $weeklyConsultations = PatientConsultationRecord::where('consultation_date_time', '>=', Carbon::now()->subWeek())
            ->selectRaw('DATE(consultation_date_time) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('AdminDashboard', [
            'stats' => [
                'todayVisits' => $todayVisits,
                'lowStockCount' => $lowStockCount,
                'nearingExpiryCount' => $nearingExpiryCount,
                'totalPatients' => PatientConsultationRecord::distinct('student_employee_id')->count(),
                'totalUsers' => $totalUsers,
                'usersByRole' => $usersByRole,
            ],
            'recentUsers' => $recentUsers,
            'weeklyConsultations' => $weeklyConsultations,
        ]);
    }
}
