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
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            background: white;
            padding: 0.3in;
            margin: 0;
        }

        .form-container {
            width: 7.5in;
            min-height: 18in;
            margin: 0 auto;
            background: white;
            page-break-inside: avoid;
            page-break-after: avoid;
        }

        .university-header {
            display: flex;
            align-items: flex-start;
            padding: 15px 20px;
            border-bottom: 2px solid #000;
            background: white;
            position: relative;
        }

        .logo-section {
            position: absolute;
            left: 20px;
            top: 15px;
        }

        .logo-placeholder {
            width: 60px;
            height: 60px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            text-align: center;
            background: #f0f0f0;
        }

        .university-name {
            flex: 1;
            text-align: center;
            padding: 0 100px;
        }

        .university-name h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .department-info {
            position: absolute;
            right: 20px;
            top: 15px;
            text-align: right;
            font-size: 10px;
            width: 300px;
        }

        .department-info .main-dept {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .privacy-notice {
            margin-top: 70px;
            padding: 10px 20px;
            font-size: 9px;
            text-align: center;
            font-style: italic;
            border-bottom: 1px solid #000;
            background: #f9f9f9;
        }

        .form-title {
            text-align: center;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            border-bottom: 2px solid #000;
        }

        .section-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        .section-header {
            background-color: #90C695;
            font-weight: bold;
            text-align: center;
            padding: 8px;
            font-size: 11px;
            border: 1px solid #000;
        }

        .section-table td,
        .section-table th {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }

        .field-label {
            font-weight: bold;
            white-space: nowrap;
            background-color: #f5f5f5;
        }

        .field-value {
            min-height: 20px;
            background: white;
        }

        .complaints-section {
            height: 80px;
        }

        .medical-history-section {
            width: 100%;
            border-collapse: collapse;
        }

        .medical-history-section td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
        }

        .checkbox-row input[type="checkbox"] {
            margin-right: 5px;
            transform: scale(1.2);
        }

        .vital-signs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .vital-signs-table th,
        .vital-signs-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-size: 10px;
        }

        .vital-signs-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .diagnosis-section {
            height: 120px;
            padding: 8px;
            vertical-align: top;
        }

        .staff-section {
            height: 100px;
        }

        .form-footer {
            text-align: center;
            padding: 10px;
            font-size: 10px;
            border-top: 1px solid #000;
        }

        /* Specific column widths */
        .narrow-col {
            width: 15%;
        }

        .medium-col {
            width: 25%;
        }

        .wide-col {
            width: 35%;
        }

        .full-col {
            width: 100%;
        }

        /* Page configuration for PDF generation */
        @page {
            size: 8.5in 14in;
            margin: 0.2in;
        }

        /* Page break for printing */
        @media print {
            @page {
                size: 8.5in 20in;
                margin: 0.2in;
            }
            
            body {
                padding: 0;
                margin: 0;
            }
            
            .form-container {
                width: 100%;
                min-height: 100%;
                page-break-inside: avoid;
                page-break-after: avoid;
            }
            
            .section {
                page-break-inside: avoid;
            }
        }

        .date-time-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
        }

        .id-field, .datetime-field {
            display: flex;
            align-items: center;
        }

        .full-width-field {
            width: 100%;
        }

    </style>
</head>

<body>
    <div class="form-container">
        <!-- University Header -->
        <div class="university-header">
            <div class="logo-section">
                <div class="logo-placeholder">
                    UZ LOGO
                </div>
            </div>
            <div class="department-info">
                <div class="main-dept">SAFETY, HEALTH, AND ENVIRONMENT DEPARTMENT</div>
                <div>HEALTH SERVICES – MAIN CAMPUS</div>
                <div>Ground Floor, Edificio Academico Building,</div>
                <div>Don Toribio Street, Tetuan, Zamboanga City 7000</div>
                <div>(63)(62) 991-1135 Local 202</div>
            </div>
        </div>

        <!-- Privacy Notice -->
        <div class="privacy-notice">
            "As a personal Information Collector, Universidad de Zamboanga (UZ) respects your privacy, rights, and freedom. The University is committed to protect and respect your personal information. Your personal information will be handled with the utmost confidentiality."
        </div>

        <!-- Form Title -->
        <div class="form-title">
            PATIENT CONSULTATION RECORD
        </div>

        <!-- Patient Information Section -->
        <table class="section-table">
            <tr>
                <th class="section-header" colspan="6">PATIENT INFORMATION</th>
            </tr>
            <tr>
                <td class="field-label narrow-col">Student/Employee ID No:</td>
                <td class="field-value medium-col">{{ $record->student_employee_id }}</td>
                <td class="field-label narrow-col">Date and Time:</td>
                <td class="field-value wide-col" colspan="3">{{ \Carbon\Carbon::parse($record->consultation_date_time)->format('m/d/Y g:i A') }}</td>
            </tr>
            <tr>
                <td class="field-label">Last Name:</td>
                <td class="field-value">{{ $record->last_name }}</td>
                <td class="field-label">First Name:</td>
                <td class="field-value">{{ $record->first_name }}</td>
                <td class="field-label">Middle Name:</td>
                <td class="field-value">{{ $record->middle_name }}</td>
            </tr>
            <tr>
                <td class="field-label">Age:</td>
                <td class="field-value">{{ $record->age }}</td>
                <td class="field-label">Birthdate:</td>
                <td class="field-value">{{ \Carbon\Carbon::parse($record->birthdate)->format('m/d/Y') }}</td>
                <td class="field-label">Civil Status:</td>
                <td class="field-value">{{ $record->civil_status }}</td>
            </tr>
            <tr>
                <td class="field-label">Sex:</td>
                <td class="field-value">{{ $record->sex }}</td>
                <td class="field-label">Address:</td>
                <td class="field-value" colspan="3">{{ $record->address }}</td>
            </tr>
            <tr>
                <td class="field-label">Department/Course:</td>
                <td class="field-value" colspan="2">{{ $record->department_course }}</td>
                <td class="field-label">Contact No.:</td>
                <td class="field-value" colspan="2">{{ $record->contact_no }}</td>
            </tr>
            @if($record->guardian_name)
            <tr>
                <td class="field-label">Name of Guardian:</td>
                <td class="field-value" colspan="2">{{ $record->guardian_name }}</td>
                <td class="field-label">Relationship:</td>
                <td class="field-value">{{ $record->guardian_relationship }}</td>
                <td class="field-value">{{ $record->guardian_contact_no }}</td>
            </tr>
            @endif
            <tr>
                <td class="field-label" colspan="6">Chief complaints/Reasons for Consultation:</td>
            </tr>
            <tr>
                <td class="field-value complaints-section" colspan="6">{{ $record->chief_complaints }}</td>
            </tr>
        </table>

        <!-- Medical History Section -->
        <table class="section-table">
            <tr>
                <th class="section-header" colspan="4">MEDICAL HISTORY</th>
            </tr>
            <tr>
                <td style="width: 25%;">
                    <div class="checkbox-row">
                        <input type="checkbox" {{ $record->has_allergy ? 'checked' : '' }}>
                        <span>Allergy:</span>
                    </div>
                    @if($record->has_allergy && $record->allergy_specify)
                    <div style="margin-top: 5px; font-size: 9px;">
                        Specify: {{ $record->allergy_specify }}
                    </div>
                    @endif
                </td>
                <td style="width: 25%;">
                    <div class="checkbox-row">
                        <input type="checkbox" {{ $record->has_hypertension ? 'checked' : '' }}>
                        <span>Hypertension</span>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="checkbox-row">
                        <input type="checkbox" {{ $record->has_diabetes ? 'checked' : '' }}>
                        <span>Diabetes</span>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="checkbox-row">
                        <input type="checkbox" {{ $record->has_asthma ? 'checked' : '' }}>
                        <span>Asthma:</span>
                    </div>
                    @if($record->has_asthma && $record->asthma_last_attack)
                    <div style="margin-top: 5px; font-size: 9px;">
                        Last Attack: {{ \Carbon\Carbon::parse($record->asthma_last_attack)->format('m/d/Y') }}
                    </div>
                    @endif
                </td>
            </tr>
            @if($record->other_medical_history)
            <tr>
                <td colspan="4">
                    <strong>Others:</strong> {{ $record->other_medical_history }}
                </td>
            </tr>
            @endif
        </table>

        <!-- Vital Signs Section -->
        <table class="section-table">
            <tr>
                <th class="section-header" colspan="6">VITAL SIGNS</th>
            </tr>
            <tr>
                <td rowspan="6" style="width: 15%; vertical-align: top;">
                    <div style="margin-bottom: 10px;">
                        <strong>WEIGHT:</strong><br>
                        {{ $record->weight }} kg
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>HEIGHT:</strong><br>
                        {{ $record->height }} cm
                    </div>
                    <div style="margin-bottom: 10px;">
                        <strong>BMI:</strong><br>
                        {{ $record->bmi }}
                    </div>
                </td>
                <td rowspan="6" style="width: 15%; vertical-align: top;">
                    @if($record->sex === 'Female' && $record->last_menstrual_period)
                    <div>
                        <strong>LAST MENSTRUAL PERIOD:</strong><br>
                        <small>(For Female Only)</small><br>
                        {{ \Carbon\Carbon::parse($record->last_menstrual_period)->format('m/d/Y') }}
                    </div>
                    @else
                    <div>
                        <strong>LAST MENSTRUAL PERIOD:</strong><br>
                        <small>(For Female Only)</small>
                    </div>
                    @endif
                </td>
                <th style="width: 12%;">TIME:</th>
                <th style="width: 12%;">TIME:</th>
                <th style="width: 12%;">BP:</th>
                <th style="width: 12%;">BP:</th>
            </tr>
            <tr>
                <td>{{ $record->vital_signs_time_1 ?? '&nbsp;' }}</td>
                <td>{{ $record->vital_signs_time_2 ?? '&nbsp;' }}</td>
                <td>{{ $record->blood_pressure_1 ?? '&nbsp;' }}</td>
                <td>{{ $record->blood_pressure_2 ?? '&nbsp;' }}</td>
            </tr>
            <tr>
                <th>HR:</th>
                <th>HR:</th>
                <th>RR:</th>
                <th>RR:</th>
            </tr>
            <tr>
                <td>{{ $record->heart_rate_1 ?? '&nbsp;' }}</td>
                <td>{{ $record->heart_rate_2 ?? '&nbsp;' }}</td>
                <td>{{ $record->respiratory_rate_1 ?? '&nbsp;' }}</td>
                <td>{{ $record->respiratory_rate_2 ?? '&nbsp;' }}</td>
            </tr>
            <tr>
                <th>TEMP:</th>
                <th>TEMP:</th>
                <th>O2 SAT:</th>
                <th>O2 SAT:</th>
            </tr>
            <tr>
                <td>{{ $record->temperature_1 ?? '&nbsp;' }}</td>
                <td>{{ $record->temperature_2 ?? '&nbsp;' }}</td>
                <td>{{ $record->oxygen_saturation_1 ?? '&nbsp;' }}</td>
                <td>{{ $record->oxygen_saturation_2 ?? '&nbsp;' }}</td>
            </tr>
        </table>

        <!-- Diagnosis Section -->
        <table class="section-table">
            <tr>
                <td class="field-label" style="background-color: #90C695; font-weight: bold; text-align: center;">DIAGNOSIS:</td>
            </tr>
            <tr>
                <td class="diagnosis-section">{{ $record->diagnosis }}</td>
            </tr>
        </table>

        <!-- Staff Section -->
        <table class="section-table">
            <tr>
                <td class="field-label staff-section" style="width: 50%;">
                    <strong>NURSE ON DUTY:</strong><br><br>
                    {{ $record->nurse_on_duty }}
                </td>
                <td class="field-label staff-section" style="width: 50%;">
                    <strong>PHYSICIAN ON DUTY:</strong><br><br>
                    {{ $record->physician_on_duty }}
                </td>
            </tr>
        </table>

        <!-- Form Footer -->
        <div class="form-footer">
            FO-UHS-032; Revision 2.0.0; February 18, 2025
        </div>
    </div>
</body>

</html>