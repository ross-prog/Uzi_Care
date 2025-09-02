<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NurseNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_consultation_record_id',
        'patient_name',
        'age',
        'department',
        'student_employee_id',
        'contact_no',
        'entry_date_time',
        'nurse_notes',
        'doctor_orders',
        'entered_by_nurse',
        'relationship',
    ];

    protected $casts = [
        'entry_date_time' => 'datetime',
    ];

    // Relationships
    public function patientConsultationRecord()
    {
        return $this->belongsTo(PatientConsultationRecord::class);
    }
}
