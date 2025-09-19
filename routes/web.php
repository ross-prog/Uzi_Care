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
    
    // Dashboard - accessible to all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin Dashboard
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');
    });

    // EHR Routes - accessible to admin and nurse
    Route::middleware(['role:admin,nurse'])->prefix('ehr')->name('ehr.')->group(function () {
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
    
    // Inventory Routes - accessible to admin, nurse, and inventory_manager
    Route::middleware(['role:admin,nurse,inventory_manager'])->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        // Inventory management (add/edit/delete) only for admin and inventory_manager
        Route::middleware(['role:admin,inventory_manager'])->group(function () {
            Route::post('/', [InventoryController::class, 'store'])->name('store');
            Route::put('/{item}', [InventoryController::class, 'update'])->name('update');
            Route::delete('/{item}', [InventoryController::class, 'destroy'])->name('destroy');
        });
    });
    
    // Medicine Distribution Routes - accessible to admin and inventory_manager
    Route::middleware(['role:admin,inventory_manager'])->prefix('medicine-distributions')->name('medicine-distributions.')->group(function () {
        Route::get('/', [MedicineDistributionController::class, 'index'])->name('index');
        Route::get('/create', [MedicineDistributionController::class, 'create'])->name('create');
        Route::post('/', [MedicineDistributionController::class, 'store'])->name('store');
        Route::get('/{medicineDistribution}', [MedicineDistributionController::class, 'show'])->name('show');
        Route::put('/{medicineDistribution}/status', [MedicineDistributionController::class, 'updateStatus'])->name('update-status');
        
        // Notification routes
        Route::post('/notifications/mark-read', [MedicineDistributionController::class, 'markNotificationRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [MedicineDistributionController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');
    });
    
    // User Management Routes - accessible to admin and account_manager
    Route::middleware(['role:admin,account_manager'])->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    });
    
    // Reports Routes - accessible to admin and nurse
    Route::middleware(['role:admin,nurse'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/export', [ReportsController::class, 'export'])->name('export');
    });
    
    // AI Forecasting - accessible to admin only
    Route::middleware(['role:admin'])->group(function () {
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

// Inventory Routes
Route::prefix('inventory')->name('inventory.')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::post('/', [InventoryController::class, 'store'])->name('store');
    Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update');
    Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->name('destroy');
    Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
    Route::get('/nearing-expiry', [InventoryController::class, 'nearingExpiry'])->name('nearing-expiry');
});

// Medicine Routes
Route::post('/medicines', [InventoryController::class, 'storeMedicine'])->name('medicines.store');

// Reports Routes
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportsController::class, 'index'])->name('index');
    Route::get('/statistics', [ReportsController::class, 'statistics'])->name('statistics');
    Route::get('/patients', [ReportsController::class, 'patients'])->name('patients');
    Route::get('/patients/export', [ReportsController::class, 'exportPatientsReport'])->name('patients.export');
    Route::get('/statistics/export', [ReportsController::class, 'exportStatisticalReport'])->name('statistics.export');
    Route::get('/patient-visits', [ReportsController::class, 'patientVisits'])->name('patient-visits');
    Route::get('/inventory-report', [ReportsController::class, 'inventoryReport'])->name('inventory');
});
