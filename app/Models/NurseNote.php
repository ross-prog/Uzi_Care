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

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            AuditLog::logEvent(
                $model,
                'created',
                null,
                $model->toArray(),
                'Nurse note created for consultation'
            );
        });

        static::updated(function ($model) {
            AuditLog::logEvent(
                $model,
                'updated',
                $model->getOriginal(),
                $model->toArray(),
                'Nurse note updated'
            );
        });

        static::deleted(function ($model) {
            AuditLog::logEvent(
                $model,
                'deleted',
                $model->toArray(),
                null,
                'Nurse note deleted'
            );
        });
    }

    // Relationships
    public function patientConsultationRecord()
    {
        return $this->belongsTo(PatientConsultationRecord::class);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->orderBy('created_at', 'desc');
    }

    // Accessor to get the consultation type based on content
    public function getConsultationTypeAttribute()
    {
        if ($this->doctor_orders && !empty(trim($this->doctor_orders))) {
            return 'doctor-order';
        }
        
        if ($this->nurse_notes && !empty(trim($this->nurse_notes))) {
            return 'nurse-note';
        }
        
        return 'consultation';
    }

    // Accessor to get a formatted display name for the consultation
    public function getFormattedNameAttribute()
    {
        return $this->patient_name ?: 'Unknown Patient';
    }
}
