<?php

namespace App\Http\Controllers;

use App\Models\PatientConsultationRecord;
use App\Models\NurseNote;
use App\Models\Medicine;
use App\Models\Inventory;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PatientConsultationController extends Controller
{
    public function index()
    {
        // Get medicines with inventory quantities - fixed approach
        $medicines = Medicine::whereNotIn('type', ['Medical Supply', 'Medical Device'])
            ->orderBy('name')
            ->get()
            ->map(function($medicine) {
                // Calculate total available quantity across all inventory records
                $totalQuantity = Inventory::where('medicine_id', $medicine->id)->sum('quantity');
                $medicine->available_quantity = $totalQuantity ?? 0;
                return $medicine;
            });
        
        // Get equipment with inventory quantities - fixed approach
        $equipment = Medicine::whereIn('type', ['Medical Supply', 'Medical Device'])
            ->orderBy('name')
            ->get()
            ->map(function($equipmentItem) {
                // Calculate total available quantity across all inventory records
                $totalQuantity = Inventory::where('medicine_id', $equipmentItem->id)->sum('quantity');
                $equipmentItem->available_quantity = $totalQuantity ?? 0;
                return $equipmentItem;
            });
        
        return Inertia::render('EHR/Index', [
            'medicines' => $medicines,
            'equipment' => $equipment
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'student_employee_id' => 'required|string'
        ]);

        $records = PatientConsultationRecord::where('student_employee_id', $request->student_employee_id)
            ->with('nurseNotes')
            ->orderBy('consultation_date_time', 'desc')
            ->get();

        return response()->json([
            'records' => $records,
            'student_employee_id' => $request->student_employee_id
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // Basic Information
            'student_employee_id' => 'required|string',
            'consultation_date_time' => 'required|date',
            
            // Personal Information
            'last_name' => 'required|string',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'age' => 'required|integer|min:0|max:150',
            'birthdate' => 'required|date',
            'civil_status' => 'required|in:Single,Married,Divorced,Widowed,Separated',
            'sex' => 'required|in:Male,Female',
            'address' => 'required|string',
            'department_course' => 'required|string',
            'contact_no' => 'required|string',
            
            // Guardian Information
            'guardian_name' => 'nullable|string',
            'guardian_relationship' => 'nullable|string',
            'guardian_contact_no' => 'nullable|string',
            
            // Chief Complaints
            'chief_complaints' => 'required|string',
            
            // Medical History
            'has_allergy' => 'boolean',
            'allergy_specify' => 'nullable|string',
            'has_hypertension' => 'boolean',
            'has_diabetes' => 'boolean',
            'has_asthma' => 'boolean',
            'asthma_last_attack' => 'nullable|date',
            'other_medical_history' => 'nullable|string',
            
            // Vital Signs
            'vital_signs_time_1' => 'nullable|date_format:H:i',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'last_menstrual_period' => 'nullable|date',
            'blood_pressure_1' => 'nullable|string',
            'heart_rate_1' => 'nullable|integer|min:0',
            'respiratory_rate_1' => 'nullable|integer|min:0',
            'temperature_1' => 'nullable|numeric|min:0',
            'oxygen_saturation_1' => 'nullable|integer|min:0|max:100',
            
            'vital_signs_time_2' => 'nullable|date_format:H:i',
            'blood_pressure_2' => 'nullable|string',
            'heart_rate_2' => 'nullable|integer|min:0',
            'respiratory_rate_2' => 'nullable|integer|min:0',
            'temperature_2' => 'nullable|numeric|min:0',
            'oxygen_saturation_2' => 'nullable|integer|min:0|max:100',
            
            // Diagnosis and Staff
            'diagnosis' => 'required|string',
            'nurse_on_duty' => 'required|string',
            'physician_on_duty' => 'nullable|string',
            
            // Medicines and Equipment
            'medicines' => 'nullable|array',
            'equipment' => 'nullable|array',
        ]);

        // Start database transaction for inventory deduction
        DB::beginTransaction();
        
        try {
            // Check inventory availability for medicines
            if (!empty($validatedData['medicines'])) {
                foreach ($validatedData['medicines'] as $medicine) {
                    $medicineName = $medicine['name'];
                    $quantityUsed = isset($medicine['quantity']) ? $medicine['quantity'] : 1;
                    
                    // Find medicine in inventory
                    $inventoryItem = Inventory::whereHas('medicine', function($query) use ($medicineName) {
                        $query->where('name', $medicineName);
                    })->first();
                    
                    if (!$inventoryItem) {
                        throw new \Exception("Medicine '{$medicineName}' not found in inventory.");
                    }
                    
                    if ($inventoryItem->quantity < $quantityUsed) {
                        throw new \Exception("Insufficient stock for '{$medicineName}'. Available: {$inventoryItem->quantity}, Required: {$quantityUsed}");
                    }
                }
            }
            
            // Check inventory availability for equipment
            if (!empty($validatedData['equipment'])) {
                foreach ($validatedData['equipment'] as $equipment) {
                    $equipmentName = $equipment['name'];
                    $quantityUsed = isset($equipment['quantity']) ? $equipment['quantity'] : 1;
                    
                    // Find equipment in inventory
                    $inventoryItem = Inventory::whereHas('medicine', function($query) use ($equipmentName) {
                        $query->where('name', $equipmentName);
                    })->first();
                    
                    if (!$inventoryItem) {
                        throw new \Exception("Equipment '{$equipmentName}' not found in inventory.");
                    }
                    
                    if ($inventoryItem->quantity < $quantityUsed) {
                        throw new \Exception("Insufficient stock for '{$equipmentName}'. Available: {$inventoryItem->quantity}, Required: {$quantityUsed}");
                    }
                }
            }
            
            // Create the patient consultation record
            $record = PatientConsultationRecord::create($validatedData);
            
            // Deduct medicines from inventory
            if (!empty($validatedData['medicines'])) {
                foreach ($validatedData['medicines'] as $medicine) {
                    $medicineName = $medicine['name'];
                    $quantityUsed = isset($medicine['quantity']) ? $medicine['quantity'] : 1;
                    
                    $inventoryItem = Inventory::whereHas('medicine', function($query) use ($medicineName) {
                        $query->where('name', $medicineName);
                    })->first();
                    
                    $inventoryItem->decrement('quantity', $quantityUsed);
                }
            }
            
            // Deduct equipment from inventory
            if (!empty($validatedData['equipment'])) {
                foreach ($validatedData['equipment'] as $equipment) {
                    $equipmentName = $equipment['name'];
                    $quantityUsed = isset($equipment['quantity']) ? $equipment['quantity'] : 1;
                    
                    $inventoryItem = Inventory::whereHas('medicine', function($query) use ($equipmentName) {
                        $query->where('name', $equipmentName);
                    })->first();
                    
                    $inventoryItem->decrement('quantity', $quantityUsed);
                }
            }
            
            // Commit the transaction
            DB::commit();
            
            return response()->json([
                'message' => 'Patient consultation record created successfully and inventory updated',
                'record' => $record->load('nurseNotes')
            ]);
            
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollback();
            
            return response()->json([
                'message' => 'Error creating consultation record: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function update(Request $request, PatientConsultationRecord $record)
    {
        $validatedData = $request->validate([
            // Basic Information
            'student_employee_id' => 'required|string',
            'consultation_date_time' => 'required|date',
            
            // Personal Information
            'last_name' => 'required|string',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'age' => 'required|integer|min:0|max:150',
            'birthdate' => 'required|date',
            'civil_status' => 'required|in:Single,Married,Divorced,Widowed,Separated',
            'sex' => 'required|in:Male,Female',
            'address' => 'required|string',
            'department_course' => 'required|string',
            'contact_no' => 'required|string',
            
            // Guardian Information
            'guardian_name' => 'nullable|string',
            'guardian_relationship' => 'nullable|string',
            'guardian_contact_no' => 'nullable|string',
            
            // Chief Complaints
            'chief_complaints' => 'required|string',
            
            // Medical History
            'has_allergy' => 'boolean',
            'allergy_specify' => 'nullable|string',
            'has_hypertension' => 'boolean',
            'has_diabetes' => 'boolean',
            'has_asthma' => 'boolean',
            'asthma_last_attack' => 'nullable|date',
            'other_medical_history' => 'nullable|string',
            
            // Vital Signs
            'vital_signs_time_1' => 'nullable|date_format:H:i',
            'weight' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'last_menstrual_period' => 'nullable|date',
            'blood_pressure_1' => 'nullable|string',
            'heart_rate_1' => 'nullable|integer|min:0',
            'respiratory_rate_1' => 'nullable|integer|min:0',
            'temperature_1' => 'nullable|numeric|min:0',
            'oxygen_saturation_1' => 'nullable|integer|min:0|max:100',
            
            'vital_signs_time_2' => 'nullable|date_format:H:i',
            'blood_pressure_2' => 'nullable|string',
            'heart_rate_2' => 'nullable|integer|min:0',
            'respiratory_rate_2' => 'nullable|integer|min:0',
            'temperature_2' => 'nullable|numeric|min:0',
            'oxygen_saturation_2' => 'nullable|integer|min:0|max:100',
            
            // Diagnosis and Staff
            'diagnosis' => 'required|string',
            'nurse_on_duty' => 'required|string',
            'physician_on_duty' => 'nullable|string',
            
            // Medicines and Equipment
            'medicines' => 'nullable|array',
            'equipment' => 'nullable|array',
        ]);

        try {
            // Update the patient consultation record
            $record->update($validatedData);
            
            return response()->json([
                'message' => 'Patient consultation record updated successfully',
                'record' => $record->load('nurseNotes')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating consultation record: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function show(PatientConsultationRecord $record)
    {
        return response()->json($record->load('nurseNotes'));
    }

    public function downloadPdf(PatientConsultationRecord $record)
    {
        $pdf = Pdf::loadView('patient-consultation-pdf', compact('record'));
        
        return $pdf->download("consultation-record-{$record->student_employee_id}-{$record->consultation_date_time->format('Y-m-d')}.pdf");
    }

    // Nurse Notes methods
    public function addNurseNote(Request $request, PatientConsultationRecord $record)
    {
        $validatedData = $request->validate([
            'nurse_notes' => 'required|string',
            'doctor_orders' => 'nullable|string',
            'entered_by_nurse' => 'required|string',
            'relationship' => 'nullable|string',
        ]);

        $nurseNote = $record->nurseNotes()->create([
            'patient_name' => $record->full_name,
            'age' => $record->age,
            'department' => $record->department_course,
            'student_employee_id' => $record->student_employee_id,
            'contact_no' => $record->contact_no,
            'entry_date_time' => now(),
            ...$validatedData
        ]);

        return response()->json([
            'message' => 'Nurse note added successfully',
            'nurse_note' => $nurseNote
        ]);
    }

    public function getNurseNotes(PatientConsultationRecord $record)
    {
        return response()->json($record->nurseNotes()->orderBy('entry_date_time', 'desc')->get());
    }

    public function downloadNurseNotesPdf(PatientConsultationRecord $record)
    {
        $nurseNotes = $record->nurseNotes()->orderBy('entry_date_time', 'desc')->get();
        
        $pdf = Pdf::loadView('nurse-notes', compact('record', 'nurseNotes'));
        
        return $pdf->download("nurse-notes-{$record->student_employee_id}-{$record->consultation_date_time->format('Y-m-d')}.pdf");
    }

    public function getAuditLogs(PatientConsultationRecord $record)
    {
        $auditLogs = $record->auditLogs()
            ->with('auditable')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'event' => $log->event,
                    'user_name' => $log->user_name,
                    'user_role' => $log->user_role,
                    'description' => $log->formatted_description,
                    'changes' => $log->changes,
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at,
                    'metadata' => $log->metadata,
                ];
            });

        return response()->json($auditLogs);
    }

    public function getPatientTimeline($studentEmployeeId)
    {
        $consultations = PatientConsultationRecord::where('student_employee_id', $studentEmployeeId)
            ->with(['nurseNotes', 'auditLogs'])
            ->orderBy('consultation_date_time', 'desc')
            ->get()
            ->map(function($consultation) {
                return [
                    'id' => $consultation->id,
                    'consultation_date_time' => $consultation->consultation_date_time,
                    'student_employee_id' => $consultation->student_employee_id,
                    'first_name' => $consultation->first_name,
                    'middle_name' => $consultation->middle_name,
                    'last_name' => $consultation->last_name,
                    'age' => $consultation->age,
                    'department_course' => $consultation->department_course,
                    'chief_complaints' => $consultation->chief_complaints,
                    'diagnosis' => $consultation->diagnosis,
                    'nurse_on_duty' => $consultation->nurse_on_duty,
                    'physician_on_duty' => $consultation->physician_on_duty,
                    'vital_signs_time_1' => $consultation->vital_signs_time_1,
                    'blood_pressure_1' => $consultation->blood_pressure_1,
                    'heart_rate_1' => $consultation->heart_rate_1,
                    'temperature_1' => $consultation->temperature_1,
                    'medicines' => $consultation->medicines,
                    'equipment' => $consultation->equipment,
                    'nurse_notes_count' => $consultation->nurseNotes->count(),
                    'created_at' => $consultation->created_at,
                    'updated_at' => $consultation->updated_at,
                ];
            });

        return response()->json($consultations);
    }
}
