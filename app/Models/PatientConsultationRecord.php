<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientConsultationRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        // Basic Information
        'student_employee_id',
        'consultation_date_time',
        
        // Personal Information
        'full_name',
        'last_name',
        'first_name',
        'middle_name',
        'age',
        'birthdate',
        'civil_status',
        'sex',
        'address',
        'department',
        'campus',
        'department_course',
        'contact_no',
        
        // Guardian Information
        'guardian_name',
        'guardian_relationship',
        'guardian_contact_no',
        
        // Chief Complaints
        'chief_complaints',
        
        // Medical History
        'has_allergy',
        'allergy_specify',
        'has_hypertension',
        'has_diabetes',
        'has_asthma',
        'asthma_last_attack',
        'other_medical_history',
        
        // Vital Signs (Time 1)
        'vital_signs_time_1',
        'weight',
        'height',
        'bmi',
        'last_menstrual_period',
        'blood_pressure_1',
        'heart_rate_1',
        'respiratory_rate_1',
        'temperature_1',
        'oxygen_saturation_1',
        
        // Vital Signs (Time 2)
        'vital_signs_time_2',
        'blood_pressure_2',
        'heart_rate_2',
        'respiratory_rate_2',
        'temperature_2',
        'oxygen_saturation_2',
        
        // Diagnosis and Staff
        'diagnosis',
        'nurse_on_duty',
        'physician_on_duty',
        
        // Medicines and Equipment
        'medicines',
        'equipment',
    ];

    protected $casts = [
        'consultation_date_time' => 'datetime',
        'birthdate' => 'date',
        'asthma_last_attack' => 'date',
        'last_menstrual_period' => 'date',
        'vital_signs_time_1' => 'datetime:H:i',
        'vital_signs_time_2' => 'datetime:H:i',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
        'bmi' => 'decimal:2',
        'temperature_1' => 'decimal:1',
        'temperature_2' => 'decimal:1',
        'has_allergy' => 'boolean',
        'has_hypertension' => 'boolean',
        'has_diabetes' => 'boolean',
        'has_asthma' => 'boolean',
        'medicines' => 'array',
        'equipment' => 'array',
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
                'Patient consultation record created'
            );
        });

        static::updated(function ($model) {
            AuditLog::logEvent(
                $model,
                'updated',
                $model->getOriginal(),
                $model->toArray(),
                'Patient consultation record updated'
            );
        });

        static::deleted(function ($model) {
            AuditLog::logEvent(
                $model,
                'deleted',
                $model->toArray(),
                null,
                'Patient consultation record deleted'
            );
        });
    }

    // Relationships
    public function nurseNotes()
    {
        return $this->hasMany(NurseNote::class);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable')->orderBy('created_at', 'desc');
    }

    // Accessor for full name
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->last_name);
    }

    // Accessor for BMI calculation
    public function calculateBmi()
    {
        if ($this->height && $this->weight) {
            $heightInMeters = $this->height / 100;
            return round($this->weight / ($heightInMeters * $heightInMeters), 2);
        }
        return null;
    }

    // Mutator to automatically calculate BMI when weight or height changes
    public function setWeightAttribute($value)
    {
        $this->attributes['weight'] = $value;
        if ($value && $this->height) {
            $this->attributes['bmi'] = $this->calculateBmi();
        }
    }

    public function setHeightAttribute($value)
    {
        $this->attributes['height'] = $value;
        if ($value && $this->weight) {
            $this->attributes['bmi'] = $this->calculateBmi();
        }
    }
}
