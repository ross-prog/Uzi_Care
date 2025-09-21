<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PatientConsultationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\MedicineDistributionController;
use App\Http\Controllers\MonthlyInventoryReportController;
use App\Http\Middleware\RoleMiddleware;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Redirect root to login if not authenticated
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/user', [AuthController::class, 'user'])->name('user');
});

// Protected Routes (require authentication)
Route::middleware('auth')->group(function () {
    
    // Dashboard - main dashboard for most roles, redirects inventory/account managers to specialized dashboards
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/main-dashboard', [DashboardController::class, 'mainDashboard'])->name('main.dashboard');

    // Role-specific Dashboards
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    });
    
    Route::middleware(['role:inventory_manager'])->group(function () {
        Route::get('/inventory/dashboard', [DashboardController::class, 'inventory'])->name('inventory.dashboard');
    });
    
    // Remove account manager routes (no one can manage accounts)
    
    // EHR Routes - accessible to admin and nurse
    Route::middleware(['strict-role:manage-records'])->prefix('ehr')->name('ehr.')->group(function () {
        Route::get('/', [PatientConsultationController::class, 'index'])->name('index');
        Route::post('/search', [PatientConsultationController::class, 'search'])->name('search');
        Route::post('/', [PatientConsultationController::class, 'store'])->name('store');
        Route::put('/{record}', [PatientConsultationController::class, 'update'])->name('update');
        Route::get('/{record}', [PatientConsultationController::class, 'show'])->name('show');
        Route::get('/{record}/pdf', [PatientConsultationController::class, 'downloadPdf'])->name('pdf');
        Route::get('/{record}/nurse-notes', [PatientConsultationController::class, 'getNurseNotes'])->name('nurse-notes');
        Route::post('/{record}/nurse-notes', [PatientConsultationController::class, 'addNurseNote'])->name('nurse-notes.store');
        Route::get('/{record}/nurse-notes-pdf', [PatientConsultationController::class, 'downloadNurseNotesPdf'])->name('nurse-notes.pdf');
        Route::get('/{record}/audit-logs', [PatientConsultationController::class, 'getAuditLogs'])->name('audit-logs');
        Route::get('/timeline/{studentEmployeeId}', [PatientConsultationController::class, 'getPatientTimeline'])->name('timeline');
    });
    
    // (Moved inventory and distribution routes below with new strict middleware)
    
    // User Management Routes - accessible to super admin only
    Route::middleware(['strict-role:manage-accounts'])->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::patch('/{user}/approve', [UserManagementController::class, 'approve'])->name('approve');
        Route::patch('/{user}/reject', [UserManagementController::class, 'reject'])->name('reject');
        Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    });
    
    // Reports Routes - accessible to admin and nurse
    Route::middleware(['strict-role:download-reports'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/export', [ReportsController::class, 'export'])->name('export');
    });
    
    // Monthly Inventory Reports Routes - simplified access
    Route::middleware(['auth'])->prefix('monthly-reports')->name('monthly-reports.')->group(function () {
        Route::get('/', [MonthlyInventoryReportController::class, 'index'])->name('index');
        Route::post('/generate', [MonthlyInventoryReportController::class, 'generate'])->name('generate');
        Route::get('/{monthlyInventoryReport}', [MonthlyInventoryReportController::class, 'show'])->name('show');
        Route::put('/{monthlyInventoryReport}/order-requests', [MonthlyInventoryReportController::class, 'updateOrderRequests'])->name('update-order-requests');
        Route::post('/{monthlyInventoryReport}/submit', [MonthlyInventoryReportController::class, 'submit'])->name('submit');
        Route::delete('/{monthlyInventoryReport}', [MonthlyInventoryReportController::class, 'destroy'])->name('destroy');
        Route::get('/compilation/view', [MonthlyInventoryReportController::class, 'compilation'])->name('compilation');
        Route::get('/compilation/export-excel', [MonthlyInventoryReportController::class, 'exportCompilationExcel'])->name('compilation.export-excel');
    });
    
    // AI Forecasting & Statistics - accessible to Main Campus admin only
    Route::middleware(['strict-role:view-statistics'])->group(function () {
        Route::inertia('/ai-forecasting', 'AIForecasting')->name('ai.forecasting');
    });
});

// Patient Consultation Routes (same as EHR - maintaining backward compatibility)
Route::prefix('patient-consultation')->name('patient-consultation.')->group(function () {
    Route::get('/', [PatientConsultationController::class, 'index'])->name('index');
    Route::post('/search', [PatientConsultationController::class, 'search'])->name('search');
    Route::post('/', [PatientConsultationController::class, 'store'])->name('store');
    Route::get('/{record}', [PatientConsultationController::class, 'show'])->name('show');
    Route::get('/{record}/pdf', [PatientConsultationController::class, 'downloadPdf'])->name('pdf');
    Route::get('/{record}/nurse-notes', [PatientConsultationController::class, 'getNurseNotes'])->name('nurse-notes');
    Route::post('/{record}/nurse-notes', [PatientConsultationController::class, 'addNurseNote'])->name('nurse-notes.store');
    Route::get('/{record}/nurse-notes-pdf', [PatientConsultationController::class, 'downloadNurseNotesPdf'])->name('nurse-notes.pdf');
});

// Inventory Routes - view for all, manage only for Main Campus
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::middleware(['strict-role:view-inventory'])->get('/', [InventoryController::class, 'index'])->name('index');
    Route::middleware(['strict-role:manage-inventory'])->post('/', [InventoryController::class, 'store'])->name('store');
    Route::middleware(['strict-role:manage-inventory'])->put('/{inventory}', [InventoryController::class, 'update'])->name('update');
    Route::middleware(['strict-role:manage-inventory'])->delete('/{inventory}', [InventoryController::class, 'destroy'])->name('destroy');
    Route::middleware(['strict-role:view-inventory'])->get('/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
    Route::middleware(['strict-role:view-inventory'])->get('/nearing-expiry', [InventoryController::class, 'nearingExpiry'])->name('nearing-expiry');
    
    // Report generation routes
    Route::middleware(['strict-role:generate-inventory-reports'])->get('/generate-report', [InventoryController::class, 'generateReport'])->name('generate-report');
    Route::middleware(['strict-role:generate-inventory-reports'])->post('/save-report', [InventoryController::class, 'saveReport'])->name('save-report');
});

// Medicine Routes - only Main Campus can add new medicines
Route::middleware(['strict-role:manage-inventory'])->post('/medicines', [InventoryController::class, 'storeMedicine'])->name('medicines.store');

// Medicine Distribution Routes - Main Campus distributes, others request
Route::prefix('medicine-distributions')->name('medicine-distributions.')->group(function () {
    Route::middleware(['strict-role:admin-nurse'])->get('/', [MedicineDistributionController::class, 'index'])->name('index');
    Route::middleware(['strict-role:distribute-medicines'])->get('/create', [MedicineDistributionController::class, 'create'])->name('create');
    Route::middleware(['strict-role:distribute-medicines'])->post('/', [MedicineDistributionController::class, 'store'])->name('store');
    Route::middleware(['strict-role:admin-nurse'])->get('/{distribution}', [MedicineDistributionController::class, 'show'])->name('show');
    Route::middleware(['strict-role:distribute-medicines'])->put('/{distribution}/status', [MedicineDistributionController::class, 'updateStatus'])->name('update-status');
    
    // Request routes for other campuses
    Route::middleware(['strict-role:request-distributions'])->get('/request', [MedicineDistributionController::class, 'requestForm'])->name('request');
    Route::middleware(['strict-role:request-distributions'])->post('/request', [MedicineDistributionController::class, 'submitRequest'])->name('submit-request');
});

// Reports Routes
Route::prefix('reports')->name('reports.')->group(function () {
    Route::middleware(['strict-role:download-reports'])->get('/', [ReportsController::class, 'index'])->name('index');
    Route::middleware(['strict-role:view-statistics'])->get('/statistics', [ReportsController::class, 'statistics'])->name('statistics');
    Route::middleware(['strict-role:download-reports'])->get('/patients', [ReportsController::class, 'patients'])->name('patients');
    Route::middleware(['strict-role:download-reports'])->get('/patients/export', [ReportsController::class, 'exportPatientsReport'])->name('patients.export');
    Route::middleware(['strict-role:view-statistics'])->get('/statistics/export', [ReportsController::class, 'exportStatisticalReport'])->name('statistics.export');
    Route::middleware(['strict-role:download-reports'])->get('/patient-visits', [ReportsController::class, 'patientVisits'])->name('patient-visits');
    Route::middleware(['strict-role:download-reports'])->get('/inventory-report', [ReportsController::class, 'inventoryReport'])->name('inventory');
});
