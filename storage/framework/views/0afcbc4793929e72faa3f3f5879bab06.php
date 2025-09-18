<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Patients Report - <?php echo e($monthName); ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #2d3748;
            background: white;
            margin: 10mm;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 15px;
        }
        
        .clinic-name {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .clinic-subtitle {
            color: #6b7280;
            margin: 2px 0;
        }
        
        .report-title {
            font-size: 18px;
            color: #1f2937;
            margin: 10px 0 5px 0;
            font-weight: 600;
        }
        
        .report-period {
            font-size: 12px;
            color: #4b5563;
        }
        
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            padding: 6px 0;
        }
        
        .summary-section {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .summary-title {
            font-size: 14px;
            font-weight: 600;
            color: #1e40af;
            margin: 0 0 10px 0;
            text-align: center;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .summary-card {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 10px;
            text-align: center;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .summary-number {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
            display: block;
            margin-bottom: 3px;
        }
        
        .summary-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: 600;
            color: #1f2937;
            margin: 15px 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 2px solid #2563eb;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            overflow: hidden;
            font-size: 8px;
        }
        
        .table th {
            background: #1e40af;
            color: white;
            font-weight: 600;
            padding: 6px 4px;
            text-align: left;
            border-bottom: 1px solid #d1d5db;
        }
        
        .table td {
            padding: 4px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        
        .table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .patient-id {
            font-weight: bold;
            color: #1e40af;
        }
        
        .department {
            background: #e0f2fe;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 500;
        }
        
        .date-cell {
            white-space: nowrap;
            font-size: 7px;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .nurse-notes {
            font-size: 7px;
            color: #4b5563;
            background: #f1f5f9;
            padding: 3px;
            border-radius: 2px;
            margin-top: 2px;
        }
        
        .no-data {
            text-align: center;
            color: #9ca3af;
            font-style: italic;
            padding: 20px;
            background: #f9fafb;
            border-radius: 4px;
            border: 1px dashed #d1d5db;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1 class="clinic-name">UZI CARE CLINIC</h1>
        <p class="clinic-subtitle">Comprehensive Healthcare Management System</p>
        <h2 class="report-title">Patients Report</h2>
        <p class="report-period"><?php echo e($monthName); ?></p>
    </div>

    <!-- Meta Information -->
    <div class="meta-info">
        <div><strong>Generated:</strong> <?php echo e($generatedAt); ?></div>
        <div><strong>Report Type:</strong> Patient Records</div>
        <div><strong>Total Records:</strong> <?php echo e($data['totalPatients']); ?></div>
    </div>

    <!-- Summary Statistics -->
    <div class="summary-section">
        <h3 class="summary-title">Report Summary</h3>
        <div class="summary-grid">
            <div class="summary-card">
                <span class="summary-number"><?php echo e($data['totalPatients']); ?></span>
                <div class="summary-label">Total Patients</div>
            </div>
            <div class="summary-card">
                <span class="summary-number"><?php echo e(count($data['departments'])); ?></span>
                <div class="summary-label">Departments</div>
            </div>
            <div class="summary-card">
                <span class="summary-number"><?php echo e($data['patients']->sum(function($p) { return $p->nurseNotes->count(); })); ?></span>
                <div class="summary-label">Nurse Notes</div>
            </div>
        </div>
    </div>

    <!-- Department Distribution -->
    <?php if(count($data['departments']) > 0): ?>
    <h3 class="section-title">Department Distribution</h3>
    <table class="table" style="margin-bottom: 15px;">
        <thead>
            <tr>
                <th>Department</th>
                <th>Patient Count</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $data['departments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($department ?: 'Unknown'); ?></td>
                    <td><?php echo e($count); ?></td>
                    <td><?php echo e($data['totalPatients'] > 0 ? number_format(($count / $data['totalPatients']) * 100, 1) : 0); ?>%</td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Patient Records -->
    <h3 class="section-title">Patient Records</h3>
    <?php if($data['patients']->count() > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 10%;">ID</th>
                    <th style="width: 15%;">Name</th>
                    <th style="width: 8%;">Age/Sex</th>
                    <th style="width: 12%;">Department</th>
                    <th style="width: 10%;">Date</th>
                    <th style="width: 15%;">Chief Complaints</th>
                    <th style="width: 12%;">Diagnosis</th>
                    <th style="width: 10%;">Nurse</th>
                    <th style="width: 8%;">Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $data['patients']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="patient-id"><?php echo e($patient->student_employee_id); ?></td>
                        <td><?php echo e($patient->full_name ?: ($patient->first_name . ' ' . $patient->last_name)); ?></td>
                        <td><?php echo e($patient->age); ?>/<?php echo e($patient->sex); ?></td>
                        <td>
                            <?php if($patient->department): ?>
                                <span class="department"><?php echo e(Str::limit($patient->department, 20)); ?></span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="date-cell"><?php echo e($patient->consultation_date_time ? $patient->consultation_date_time->format('M j, Y H:i') : '-'); ?></td>
                        <td><?php echo e(Str::limit($patient->chief_complaints, 50) ?: '-'); ?></td>
                        <td><?php echo e(Str::limit($patient->assessment_diagnosis, 40) ?: '-'); ?></td>
                        <td><?php echo e(Str::limit($patient->nurse_on_duty, 30) ?: '-'); ?></td>
                        <td>
                            <?php if($patient->nurseNotes && $patient->nurseNotes->count() > 0): ?>
                                <?php echo e($patient->nurseNotes->count()); ?> note(s)
                                <?php $__currentLoopData = $patient->nurseNotes->take(1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="nurse-notes">
                                        <?php echo e(Str::limit($note->nurse_notes, 50)); ?>

                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">
            No patient records found for the selected period and filters.
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <p>
            <strong>UZI CARE CLINIC</strong> | Comprehensive Healthcare Management System<br>
            This report was automatically generated on <?php echo e($generatedAt); ?><br>
            <em>Confidential Document - For Internal Use Only</em>
        </p>
    </div>
</body>
</html><?php /**PATH C:\xampp\htdocs\Uzi_Care\resources\views/reports/patients-report-pdf.blade.php ENDPATH**/ ?>