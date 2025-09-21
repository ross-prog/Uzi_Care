<?php

namespace App\Http\Controllers;

use App\Models\PatientConsultationRecord;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Only inventory managers and account managers get specialized dashboards
        if ($user->role === 'inventory_manager') {
            return redirect()->route('inventory.dashboard');
        }
        
        if ($user->role === 'account_manager') {
            return redirect()->route('accounts.dashboard');
        }
        
        // All other roles (admin, nurse, head_nurse, super_admin) get the main dashboard
        return $this->mainDashboard();
    }

    /**
     * Main dashboard for admin, nurse, head_nurse, and super_admin roles
     */
    public function mainDashboard()
    {
        $user = Auth::user();
        $campus = $user->campus;
        
        // Get today's patient visits
        $todayVisits = PatientConsultationRecord::whereDate('consultation_date_time', Carbon::today())->count();
        
        // For non-admin roles, filter inventory by campus
        $inventoryQuery = Inventory::with('medicine');
        if ($user->role !== 'admin') {
            $inventoryQuery->where('campus', $campus);
        }
        
        // Get low stock items - campus filtered for non-admin users
        $lowStockItems = (clone $inventoryQuery)
            ->whereRaw('quantity <= low_stock_threshold')
            ->take(5)
            ->get();
        
        // Get medicines nearing expiry (next 30 days) - campus filtered for non-admin users
        $nearingExpiry = (clone $inventoryQuery)
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

        // Get actual counts for stats - campus filtered for non-admin users
        $lowStockCount = (clone $inventoryQuery)->whereRaw('quantity <= low_stock_threshold')->count();
        $nearingExpiryCount = (clone $inventoryQuery)
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>=', Carbon::now())
            ->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'todayVisits' => $todayVisits,
                'lowStockCount' => $lowStockCount,
                'nearingExpiryCount' => $nearingExpiryCount,
                'totalPatients' => PatientConsultationRecord::distinct('student_employee_id')->count(),
                'campus' => $campus,
                'userRole' => $user->role,
            ],
            'lowStockItems' => $lowStockItems,
            'nearingExpiry' => $nearingExpiry,
            'recentPatients' => $recentPatients,
        ]);
    }

    /**
     * Inventory manager dashboard with stock and distribution data
     */
    public function inventory()
    {
        $user = Auth::user();
        $campus = $user->campus;
        
        // Campus-specific inventory data
        $campusInventory = Inventory::where('campus', $campus)->with('medicine');
        
        $lowStockCount = (clone $campusInventory)->whereRaw('quantity <= low_stock_threshold')->count();
        $nearingExpiryCount = (clone $campusInventory)
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>=', Carbon::now())
            ->count();
        
        $lowStockItems = (clone $campusInventory)
            ->whereRaw('quantity <= low_stock_threshold')
            ->take(5)
            ->get();
            
        $nearingExpiry = (clone $campusInventory)
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>=', Carbon::now())
            ->take(5)
            ->get();

        return Inertia::render('InventoryDashboard', [
            'stats' => [
                'lowStockCount' => $lowStockCount,
                'nearingExpiryCount' => $nearingExpiryCount,
                'totalItems' => (clone $campusInventory)->count(),
                'campus' => $campus,
            ],
            'lowStockItems' => $lowStockItems,
            'nearingExpiry' => $nearingExpiry,
        ]);
    }

    /**
     * Account manager dashboard with user and access data
     */
    public function accounts()
    {
        $user = Auth::user();
        $campus = $user->campus;
        
        $totalUsers = User::where('is_active', true)->count();
        $activeUsers = User::where('is_active', true)->count();
        $pendingUsers = User::where('is_active', false)->count();

        $recentUsers = User::orderBy('created_at', 'desc')
            ->take(10)
            ->get(['id', 'name', 'email', 'role', 'campus', 'created_at']);

        // Get pending approvals (inactive users waiting for approval)
        $pendingApprovals = User::where('is_active', false)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get(['id', 'name', 'email', 'role', 'campus', 'created_at']);

        return Inertia::render('AccountsDashboard', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'activeUsers' => $activeUsers,
                'pendingUsers' => $pendingUsers,
                'roleCount' => 4, // admin, nurse, inventory_manager, account_manager
                'campus' => $campus,
            ],
            'recentUsers' => $recentUsers,
            'pendingApprovals' => $pendingApprovals,
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
