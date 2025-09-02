<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\PatientConsultationController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// AI Forecasting (placeholder)
Route::inertia('/ai-forecasting', 'AIForecasting')->name('ai.forecasting');

// EHR Routes (Patient Consultation is the new EHR system)
Route::prefix('ehr')->name('ehr.')->group(function () {
    Route::get('/', [PatientConsultationController::class, 'index'])->name('index');
    Route::post('/search', [PatientConsultationController::class, 'search'])->name('search');
    Route::post('/', [PatientConsultationController::class, 'store'])->name('store');
    Route::get('/{record}', [PatientConsultationController::class, 'show'])->name('show');
    Route::get('/{record}/pdf', [PatientConsultationController::class, 'downloadPdf'])->name('pdf');
    Route::get('/{record}/nurse-notes', [PatientConsultationController::class, 'getNurseNotes'])->name('nurse-notes');
    Route::post('/{record}/nurse-notes', [PatientConsultationController::class, 'addNurseNote'])->name('nurse-notes.store');
    Route::get('/{record}/nurse-notes-pdf', [PatientConsultationController::class, 'downloadNurseNotesPdf'])->name('nurse-notes.pdf');
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

// Reports Routes
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportsController::class, 'index'])->name('index');
    Route::get('/patient-visits', [ReportsController::class, 'patientVisits'])->name('patient-visits');
    Route::get('/inventory-report', [ReportsController::class, 'inventoryReport'])->name('inventory');
});
