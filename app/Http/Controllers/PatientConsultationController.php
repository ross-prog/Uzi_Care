<?php

namespace App\Http\Controllers;

use App\Models\PatientConsultationRecord;
use App\Models\NurseNote;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class PatientConsultationController extends Controller
{
    public function index()
    {
        $medicines = Medicine::whereNotIn('type', ['Medical Supply', 'Medical Device'])->orderBy('name')->get();
        $equipment = Medicine::whereIn('type', ['Medical Supply', 'Medical Device'])->orderBy('name')->get();
        
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

        $record = PatientConsultationRecord::create($validatedData);

        return response()->json([
            'message' => 'Patient consultation record created successfully',
            'record' => $record->load('nurseNotes')
        ]);
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
}
