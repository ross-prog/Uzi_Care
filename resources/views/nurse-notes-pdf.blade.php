<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Notes - {{ $record->student_employee_id }}</title>
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

        .patient-info {
            border: 1px solid #333;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }

        .patient-info h3 {
            font-size: 13px;
            margin-bottom: 8px;
            border-bottom: 1px solid #666;
            padding-bottom: 2px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .info-field {
            display: table-cell;
            padding: 2px 15px 2px 0;
            vertical-align: top;
        }

        .field-label {
            font-weight: bold;
            display: inline-block;
            margin-right: 5px;
        }

        .field-value {
            display: inline-block;
        }

        .notes-section {
            margin-bottom: 20px;
        }

        .note-entry {
            border: 1px solid #333;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .note-header {
            background-color: #f0f0f0;
            padding: 8px;
            border-bottom: 1px solid #333;
            font-weight: bold;
        }

        .note-body {
            padding: 10px;
        }

        .note-field {
            margin-bottom: 10px;
        }

        .note-field-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 3px;
        }

        .note-field-content {
            border: 1px solid #ddd;
            padding: 8px;
            min-height: 40px;
            background-color: #fff;
        }

        .no-notes {
            text-align: center;
            padding: 30px;
            color: #666;
            font-style: italic;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="form-number">FO-UHS-044</div>

    <div class="header">
        <h1>UNIVERSITY HEALTH SERVICES</h1>
        <h2>NURSES NOTES</h2>
    </div>

    <!-- Patient Information -->
    <div class="patient-info">
        <h3>PATIENT INFORMATION</h3>
        <div class="info-row">
            <div class="info-field">
                <span class="field-label">Name:</span>
                <span class="field-value">{{ $record->full_name }}</span>
            </div>
            <div class="info-field">
                <span class="field-label">Age:</span>
                <span class="field-value">{{ $record->age }}</span>
            </div>
            <div class="info-field">
                <span class="field-label">Sex:</span>
                <span class="field-value">{{ $record->sex }}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-field">
                <span class="field-label">Student/Employee ID:</span>
                <span class="field-value">{{ $record->student_employee_id }}</span>
            </div>
            <div class="info-field">
                <span class="field-label">Department/Course:</span>
                <span class="field-value">{{ $record->department_course }}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-field">
                <span class="field-label">Contact No.:</span>
                <span class="field-value">{{ $record->contact_no }}</span>
            </div>
            <div class="info-field">
                <span class="field-label">Consultation Date:</span>
                <span class="field-value">{{ \Carbon\Carbon::parse($record->consultation_date_time)->format('m/d/Y g:i A') }}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-field" style="width: 100%;">
                <span class="field-label">Chief Complaints:</span>
                <span class="field-value">{{ $record->chief_complaints }}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-field" style="width: 100%;">
                <span class="field-label">Diagnosis:</span>
                <span class="field-value">{{ $record->diagnosis }}</span>
            </div>
        </div>
    </div>

    <!-- Nurse Notes -->
    <div class="notes-section">
        @if($nurseNotes && count($nurseNotes) > 0)
            @foreach($nurseNotes as $note)
            <div class="note-entry">
                <div class="note-header">
                    Entry #{{ $loop->iteration }} - 
                    {{ \Carbon\Carbon::parse($note->entry_date_time)->format('F d, Y \a\t g:i A') }}
                </div>
                <div class="note-body">
                    <div class="info-row">
                        <div class="info-field">
                            <span class="field-label">Entered by Nurse:</span>
                            <span class="field-value">{{ $note->entered_by_nurse }}</span>
                        </div>
                        @if($note->relationship)
                        <div class="info-field">
                            <span class="field-label">Relationship:</span>
                            <span class="field-value">{{ $note->relationship }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="note-field">
                        <div class="note-field-label">NURSE'S NOTES:</div>
                        <div class="note-field-content">{{ $note->nurse_notes }}</div>
                    </div>
                    
                    @if($note->doctor_orders)
                    <div class="note-field">
                        <div class="note-field-label">DOCTOR'S ORDERS:</div>
                        <div class="note-field-content">{{ $note->doctor_orders }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        @else
            <div class="no-notes">
                <p>No nurse notes have been recorded for this patient consultation.</p>
            </div>
        @endif
    </div>

    <div class="footer">
        Generated on {{ now()->format('F d, Y \a\t g:i A') }}
    </div>
</body>

</html>
