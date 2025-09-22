<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\NurseNote;
use App\Models\PatientConsultationRecord;

class NurseNoteController extends Controller
{
    /**
     * Get nurse notes for a specific consultation record
     */
    public function index($consultationRecordId)
    {
        $nurseNotes = NurseNote::where('patient_consultation_record_id', $consultationRecordId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($nurseNotes);
    }

    /**
     * Store a new nurse note
     */
    public function store(Request $request)
    {
        try {
            // Log the incoming request for debugging
            Log::info('Nurse note store request:', $request->all());
            
            $request->validate([
                'patient_consultation_record_id' => 'required|integer',
                'nurse_notes' => 'required|string',
                'entered_by_nurse' => 'required|string',
                'doctor_orders' => 'nullable|string',
                'relationship' => 'nullable|string',
            ]);

            // Get the patient consultation record to fetch patient details
            $consultationRecord = PatientConsultationRecord::findOrFail($request->patient_consultation_record_id);

            // Construct full name from first, middle, last names
            $patientName = trim($consultationRecord->first_name . ' ' . 
                              ($consultationRecord->middle_name ? $consultationRecord->middle_name . ' ' : '') . 
                              $consultationRecord->last_name);

            $nurseNote = NurseNote::create([
                'patient_consultation_record_id' => $request->patient_consultation_record_id,
                'patient_name' => $patientName,
                'age' => $consultationRecord->age,
                'department' => $consultationRecord->department_course,
                'student_employee_id' => $consultationRecord->student_employee_id,
                'contact_no' => $consultationRecord->contact_no,
                'nurse_notes' => $request->nurse_notes,
                'doctor_orders' => $request->doctor_orders,
                'entered_by_nurse' => $request->entered_by_nurse,
                'relationship' => $request->relationship,
                'entry_date_time' => now(),
            ]);

            return response()->json($nurseNote, 201);
        } catch (\Exception $e) {
            Log::error('Nurse note store error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
