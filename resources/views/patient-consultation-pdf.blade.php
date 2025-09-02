<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Consultation Record - {{ $record->student_employee_id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .form-number {
            font-size: 10px;
            text-align: right;
            margin-bottom: 10px;
        }

        .section {
            margin-bottom: 15px;
        }

        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 8px;
            border-bottom: 1px solid #666;
            padding-bottom: 2px;
        }

        .field-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .field {
            display: table-cell;
            padding: 2px 10px 2px 0;
            vertical-align: top;
        }

        .field-label {
            font-weight: bold;
            display: inline-block;
            margin-right: 5px;
        }

        .field-value {
            border-bottom: 1px solid #333;
            min-height: 18px;
            display: inline-block;
            min-width: 100px;
            padding: 0 3px;
        }

        .checkbox {
            display: inline-block;
            margin-right: 10px;
        }

        .checkbox input {
            margin-right: 3px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
        }

        .table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .vital-signs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .vital-signs-table th,
        .vital-signs-table td {
            border: 1px solid #333;
            padding: 4px;
            text-align: center;
        }

        .vital-signs-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .text-area {
            border: 1px solid #333;
            min-height: 60px;
            padding: 5px;
            margin-top: 5px;
        }

        .two-column {
            display: table;
            width: 100%;
        }

        .column {
            display: table-cell;
            width: 50%;
            padding-right: 10px;
        }

        .signature-section {
            margin-top: 30px;
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 10px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 40px;
            margin-bottom: 5px;
        }

        .medicines-equipment {
            display: table;
            width: 100%;
        }

        .medicines-section,
        .equipment-section {
            display: table-cell;
            width: 50%;
            padding-right: 10px;
        }

        .equipment-section {
            padding-right: 0;
            padding-left: 10px;
        }

        .list-item {
            padding: 3px 0;
            border-bottom: 1px dotted #ccc;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="form-number">FO-UHS-032</div>

    <div class="header">
        <h1>UNIVERSITY HEALTH SERVICES</h1>
        <h2>PATIENT CONSULTATION RECORD</h2>
    </div>

    <!-- Basic Information -->
    <div class="section">
        <div class="field-row">
            <div class="field">
                <span class="field-label">Student/Employee ID:</span>
                <span class="field-value">{{ $record->student_employee_id }}</span>
            </div>
            <div class="field">
                <span class="field-label">Date & Time:</span>
                <span class="field-value">{{ \Carbon\Carbon::parse($record->consultation_date_time)->format('m/d/Y g:i A') }}</span>
            </div>
        </div>
    </div>

    <!-- Personal Information -->
    <div class="section">
        <div class="section-title">PERSONAL INFORMATION</div>
        <div class="field-row">
            <div class="field">
                <span class="field-label">Last Name:</span>
                <span class="field-value">{{ $record->last_name }}</span>
            </div>
            <div class="field">
                <span class="field-label">First Name:</span>
                <span class="field-value">{{ $record->first_name }}</span>
            </div>
            <div class="field">
                <span class="field-label">Middle Name:</span>
                <span class="field-value">{{ $record->middle_name }}</span>
            </div>
        </div>
        <div class="field-row">
            <div class="field">
                <span class="field-label">Age:</span>
                <span class="field-value">{{ $record->age }}</span>
            </div>
            <div class="field">
                <span class="field-label">Birthdate:</span>
                <span class="field-value">{{ \Carbon\Carbon::parse($record->birthdate)->format('m/d/Y') }}</span>
            </div>
            <div class="field">
                <span class="field-label">Civil Status:</span>
                <span class="field-value">{{ $record->civil_status }}</span>
            </div>
            <div class="field">
                <span class="field-label">Sex:</span>
                <span class="field-value">{{ $record->sex }}</span>
            </div>
        </div>
        <div class="field-row">
            <div class="field">
                <span class="field-label">Address:</span>
                <span class="field-value">{{ $record->address }}</span>
            </div>
        </div>
        <div class="field-row">
            <div class="field">
                <span class="field-label">Department/Course:</span>
                <span class="field-value">{{ $record->department_course }}</span>
            </div>
            <div class="field">
                <span class="field-label">Contact No.:</span>
                <span class="field-value">{{ $record->contact_no }}</span>
            </div>
        </div>
    </div>

    <!-- Guardian Information -->
    @if($record->guardian_name)
    <div class="section">
        <div class="section-title">GUARDIAN INFORMATION</div>
        <div class="field-row">
            <div class="field">
                <span class="field-label">Name of Guardian:</span>
                <span class="field-value">{{ $record->guardian_name }}</span>
            </div>
            <div class="field">
                <span class="field-label">Relationship:</span>
                <span class="field-value">{{ $record->guardian_relationship }}</span>
            </div>
            <div class="field">
                <span class="field-label">Contact No.:</span>
                <span class="field-value">{{ $record->guardian_contact_no }}</span>
            </div>
        </div>
    </div>
    @endif

    <!-- Chief Complaints -->
    <div class="section">
        <div class="section-title">CHIEF COMPLAINTS / REASONS FOR CONSULTATION</div>
        <div class="text-area">{{ $record->chief_complaints }}</div>
    </div>

    <!-- Medical History -->
    <div class="section">
        <div class="section-title">MEDICAL HISTORY</div>
        <div class="field-row">
            <div class="checkbox">
                <input type="checkbox" {{ $record->has_allergy ? 'checked' : '' }}> Allergy
                @if($record->has_allergy && $record->allergy_specify)
                    (Specify: {{ $record->allergy_specify }})
                @endif
            </div>
            <div class="checkbox">
                <input type="checkbox" {{ $record->has_hypertension ? 'checked' : '' }}> Hypertension
            </div>
            <div class="checkbox">
                <input type="checkbox" {{ $record->has_diabetes ? 'checked' : '' }}> Diabetes
            </div>
            <div class="checkbox">
                <input type="checkbox" {{ $record->has_asthma ? 'checked' : '' }}> Asthma
                @if($record->has_asthma && $record->asthma_last_attack)
                    (Last Attack: {{ \Carbon\Carbon::parse($record->asthma_last_attack)->format('m/d/Y') }})
                @endif
            </div>
        </div>
        @if($record->other_medical_history)
        <div class="field-row">
            <span class="field-label">Others:</span>
            <span class="field-value">{{ $record->other_medical_history }}</span>
        </div>
        @endif
    </div>

    <!-- Vital Signs -->
    <div class="section">
        <div class="section-title">VITAL SIGNS</div>
        
        <!-- Basic Measurements -->
        <div class="field-row">
            <div class="field">
                <span class="field-label">Weight (kg):</span>
                <span class="field-value">{{ $record->weight }}</span>
            </div>
            <div class="field">
                <span class="field-label">Height (cm):</span>
                <span class="field-value">{{ $record->height }}</span>
            </div>
            <div class="field">
                <span class="field-label">BMI:</span>
                <span class="field-value">{{ $record->bmi }}</span>
            </div>
            @if($record->sex === 'Female' && $record->last_menstrual_period)
            <div class="field">
                <span class="field-label">LMP:</span>
                <span class="field-value">{{ \Carbon\Carbon::parse($record->last_menstrual_period)->format('m/d/Y') }}</span>
            </div>
            @endif
        </div>

        <!-- Vital Signs Table -->
        <table class="vital-signs-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Blood Pressure</th>
                    <th>Heart Rate</th>
                    <th>Respiratory Rate</th>
                    <th>Temperature (°C)</th>
                    <th>O2 Saturation (%)</th>
                </tr>
            </thead>
            <tbody>
                @if($record->vital_signs_time_1)
                <tr>
                    <td>{{ $record->vital_signs_time_1 }}</td>
                    <td>{{ $record->blood_pressure_1 }}</td>
                    <td>{{ $record->heart_rate_1 }}</td>
                    <td>{{ $record->respiratory_rate_1 }}</td>
                    <td>{{ $record->temperature_1 }}</td>
                    <td>{{ $record->oxygen_saturation_1 }}</td>
                </tr>
                @endif
                @if($record->vital_signs_time_2)
                <tr>
                    <td>{{ $record->vital_signs_time_2 }}</td>
                    <td>{{ $record->blood_pressure_2 }}</td>
                    <td>{{ $record->heart_rate_2 }}</td>
                    <td>{{ $record->respiratory_rate_2 }}</td>
                    <td>{{ $record->temperature_2 }}</td>
                    <td>{{ $record->oxygen_saturation_2 }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Diagnosis -->
    <div class="section">
        <div class="section-title">DIAGNOSIS</div>
        <div class="text-area">{{ $record->diagnosis }}</div>
    </div>

    <!-- Medicines and Equipment -->
    <div class="section">
        <div class="medicines-equipment">
            <!-- Medicines -->
            <div class="medicines-section">
                <div class="section-title">MEDICINES PRESCRIBED</div>
                @if($record->medicines && count($record->medicines) > 0)
                    @foreach($record->medicines as $medicine)
                    <div class="list-item">
                        <strong>{{ $medicine['name'] }}</strong><br>
                        <small>Dosage: {{ $medicine['dosage'] ?? 'N/A' }} | Frequency: {{ $medicine['frequency'] ?? 'N/A' }} | Duration: {{ $medicine['duration'] ?? 'N/A' }}</small>
                    </div>
                    @endforeach
                @else
                    <div class="list-item">No medicines prescribed</div>
                @endif
            </div>

            <!-- Equipment -->
            <div class="equipment-section">
                <div class="section-title">MEDICAL EQUIPMENT/SUPPLIES USED</div>
                @if($record->equipment && count($record->equipment) > 0)
                    @foreach($record->equipment as $equipment)
                    <div class="list-item">
                        <strong>{{ $equipment['name'] }}</strong><br>
                        <small>Purpose: {{ $equipment['purpose'] ?? 'N/A' }}</small>
                    </div>
                    @endforeach
                @else
                    <div class="list-item">No equipment used</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Staff Information -->
    <div class="section">
        <div class="section-title">STAFF INFORMATION</div>
        <div class="field-row">
            <div class="field">
                <span class="field-label">Nurse on Duty:</span>
                <span class="field-value">{{ $record->nurse_on_duty }}</span>
            </div>
            @if($record->physician_on_duty)
            <div class="field">
                <span class="field-label">Physician on Duty:</span>
                <span class="field-value">{{ $record->physician_on_duty }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Nurse Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div>Physician Signature</div>
        </div>
    </div>

    <div style="text-align: center; margin-top: 20px; font-size: 10px; color: #666;">
        Generated on {{ now()->format('F d, Y \a\t g:i A') }}
    </div>
</body>

</html>
