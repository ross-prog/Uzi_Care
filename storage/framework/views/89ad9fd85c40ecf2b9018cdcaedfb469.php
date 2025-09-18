<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Consultation Record - <?php echo e($record->student_employee_id); ?></title>
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
                <span class="field-value"><?php echo e($record->student_employee_id); ?></span>
            </div>
            <div class="field">
                <span class="field-label">Date & Time:</span>
                <span class="field-value"><?php echo e(\Carbon\Carbon::parse($record->consultation_date_time)->format('m/d/Y g:i A')); ?></span>
            </div>
        </div>
    </div>

    <!-- Personal Information -->
    <div class="section">
        <div class="section-title">PERSONAL INFORMATION</div>
        <div class="field-row">
            <div class="field">
                <span class="field-label">Last Name:</span>
                <span class="field-value"><?php echo e($record->last_name); ?></span>
            </div>
            <div class="field">
                <span class="field-label">First Name:</span>
                <span class="field-value"><?php echo e($record->first_name); ?></span>
            </div>
            <div class="field">
                <span class="field-label">Middle Name:</span>
                <span class="field-value"><?php echo e($record->middle_name); ?></span>
            </div>
        </div>
        <div class="field-row">
            <div class="field">
                <span class="field-label">Age:</span>
                <span class="field-value"><?php echo e($record->age); ?></span>
            </div>
            <div class="field">
                <span class="field-label">Birthdate:</span>
                <span class="field-value"><?php echo e(\Carbon\Carbon::parse($record->birthdate)->format('m/d/Y')); ?></span>
            </div>
            <div class="field">
                <span class="field-label">Civil Status:</span>
                <span class="field-value"><?php echo e($record->civil_status); ?></span>
            </div>
            <div class="field">
                <span class="field-label">Sex:</span>
                <span class="field-value"><?php echo e($record->sex); ?></span>
            </div>
        </div>
        <div class="field-row">
            <div class="field">
                <span class="field-label">Address:</span>
                <span class="field-value"><?php echo e($record->address); ?></span>
            </div>
        </div>
        <div class="field-row">
            <div class="field">
                <span class="field-label">Department/Course:</span>
                <span class="field-value"><?php echo e($record->department_course); ?></span>
            </div>
            <div class="field">
                <span class="field-label">Contact No.:</span>
                <span class="field-value"><?php echo e($record->contact_no); ?></span>
            </div>
        </div>
    </div>

    <!-- Guardian Information -->
    <?php if($record->guardian_name): ?>
    <div class="section">
        <div class="section-title">GUARDIAN INFORMATION</div>
        <div class="field-row">
            <div class="field">
                <span class="field-label">Name of Guardian:</span>
                <span class="field-value"><?php echo e($record->guardian_name); ?></span>
            </div>
            <div class="field">
                <span class="field-label">Relationship:</span>
                <span class="field-value"><?php echo e($record->guardian_relationship); ?></span>
            </div>
            <div class="field">
                <span class="field-label">Contact No.:</span>
                <span class="field-value"><?php echo e($record->guardian_contact_no); ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Chief Complaints -->
    <div class="section">
        <div class="section-title">CHIEF COMPLAINTS / REASONS FOR CONSULTATION</div>
        <div class="text-area"><?php echo e($record->chief_complaints); ?></div>
    </div>

    <!-- Medical History -->
    <div class="section">
        <div class="section-title">MEDICAL HISTORY</div>
        <div class="field-row">
            <div class="checkbox">
                <input type="checkbox" <?php echo e($record->has_allergy ? 'checked' : ''); ?>> Allergy
                <?php if($record->has_allergy && $record->allergy_specify): ?>
                    (Specify: <?php echo e($record->allergy_specify); ?>)
                <?php endif; ?>
            </div>
            <div class="checkbox">
                <input type="checkbox" <?php echo e($record->has_hypertension ? 'checked' : ''); ?>> Hypertension
            </div>
            <div class="checkbox">
                <input type="checkbox" <?php echo e($record->has_diabetes ? 'checked' : ''); ?>> Diabetes
            </div>
            <div class="checkbox">
                <input type="checkbox" <?php echo e($record->has_asthma ? 'checked' : ''); ?>> Asthma
                <?php if($record->has_asthma && $record->asthma_last_attack): ?>
                    (Last Attack: <?php echo e(\Carbon\Carbon::parse($record->asthma_last_attack)->format('m/d/Y')); ?>)
                <?php endif; ?>
            </div>
        </div>
        <?php if($record->other_medical_history): ?>
        <div class="field-row">
            <span class="field-label">Others:</span>
            <span class="field-value"><?php echo e($record->other_medical_history); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Vital Signs -->
    <div class="section">
        <div class="section-title">VITAL SIGNS</div>
        
        <!-- Basic Measurements -->
        <div class="field-row">
            <div class="field">
                <span class="field-label">Weight (kg):</span>
                <span class="field-value"><?php echo e($record->weight); ?></span>
            </div>
            <div class="field">
                <span class="field-label">Height (cm):</span>
                <span class="field-value"><?php echo e($record->height); ?></span>
            </div>
            <div class="field">
                <span class="field-label">BMI:</span>
                <span class="field-value"><?php echo e($record->bmi); ?></span>
            </div>
            <?php if($record->sex === 'Female' && $record->last_menstrual_period): ?>
            <div class="field">
                <span class="field-label">LMP:</span>
                <span class="field-value"><?php echo e(\Carbon\Carbon::parse($record->last_menstrual_period)->format('m/d/Y')); ?></span>
            </div>
            <?php endif; ?>
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
                <?php if($record->vital_signs_time_1): ?>
                <tr>
                    <td><?php echo e($record->vital_signs_time_1); ?></td>
                    <td><?php echo e($record->blood_pressure_1); ?></td>
                    <td><?php echo e($record->heart_rate_1); ?></td>
                    <td><?php echo e($record->respiratory_rate_1); ?></td>
                    <td><?php echo e($record->temperature_1); ?></td>
                    <td><?php echo e($record->oxygen_saturation_1); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($record->vital_signs_time_2): ?>
                <tr>
                    <td><?php echo e($record->vital_signs_time_2); ?></td>
                    <td><?php echo e($record->blood_pressure_2); ?></td>
                    <td><?php echo e($record->heart_rate_2); ?></td>
                    <td><?php echo e($record->respiratory_rate_2); ?></td>
                    <td><?php echo e($record->temperature_2); ?></td>
                    <td><?php echo e($record->oxygen_saturation_2); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Diagnosis -->
    <div class="section">
        <div class="section-title">DIAGNOSIS</div>
        <div class="text-area"><?php echo e($record->diagnosis); ?></div>
    </div>

    <!-- Medicines and Equipment -->
    <div class="section">
        <div class="medicines-equipment">
            <!-- Medicines -->
            <div class="medicines-section">
                <div class="section-title">MEDICINES PRESCRIBED</div>
                <?php if($record->medicines && count($record->medicines) > 0): ?>
                    <?php $__currentLoopData = $record->medicines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medicine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="list-item">
                        <strong><?php echo e($medicine['name']); ?></strong><br>
                        <small>Dosage: <?php echo e($medicine['dosage'] ?? 'N/A'); ?> | Frequency: <?php echo e($medicine['frequency'] ?? 'N/A'); ?> | Duration: <?php echo e($medicine['duration'] ?? 'N/A'); ?></small>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <div class="list-item">No medicines prescribed</div>
                <?php endif; ?>
            </div>

            <!-- Equipment -->
            <div class="equipment-section">
                <div class="section-title">MEDICAL EQUIPMENT/SUPPLIES USED</div>
                <?php if($record->equipment && count($record->equipment) > 0): ?>
                    <?php $__currentLoopData = $record->equipment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $equipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="list-item">
                        <strong><?php echo e($equipment['name']); ?></strong><br>
                        <small>Purpose: <?php echo e($equipment['purpose'] ?? 'N/A'); ?></small>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <div class="list-item">No equipment used</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Staff Information -->
    <div class="section">
        <div class="section-title">STAFF INFORMATION</div>
        <div class="field-row">
            <div class="field">
                <span class="field-label">Nurse on Duty:</span>
                <span class="field-value"><?php echo e($record->nurse_on_duty); ?></span>
            </div>
            <?php if($record->physician_on_duty): ?>
            <div class="field">
                <span class="field-label">Physician on Duty:</span>
                <span class="field-value"><?php echo e($record->physician_on_duty); ?></span>
            </div>
            <?php endif; ?>
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
        Generated on <?php echo e(now()->format('F d, Y \a\t g:i A')); ?>

    </div>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\Uzi_Care\resources\views/patient-consultation-pdf.blade.php ENDPATH**/ ?>