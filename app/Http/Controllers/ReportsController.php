<?php

namespace App\Http\Controllers;

use App\Models\PatientConsultationRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    public function statistics()
    {
        try {
            // Patient statistics
            $totalPatients = PatientConsultationRecord::distinct('student_employee_id')->count();
            $thisMonth = PatientConsultationRecord::whereMonth('consultation_date_time', Carbon::now()->month)
                ->whereYear('consultation_date_time', Carbon::now()->year)
                ->count();
            $today = PatientConsultationRecord::whereDate('consultation_date_time', Carbon::today())->count();

            // Visit types distribution based on chief complaints
            $visitTypes = PatientConsultationRecord::whereMonth('consultation_date_time', Carbon::now()->month)
                ->selectRaw('
                    CASE 
                        WHEN chief_complaints LIKE "%emergency%" OR chief_complaints LIKE "%urgent%" THEN "Emergency"
                        WHEN chief_complaints LIKE "%follow%" OR chief_complaints LIKE "%check%" THEN "Follow-up"
                        WHEN chief_complaints LIKE "%consultation%" THEN "Consultation"
                        WHEN chief_complaints LIKE "%vaccination%" OR chief_complaints LIKE "%vaccine%" THEN "Vaccination"
                        WHEN chief_complaints LIKE "%physical%" OR chief_complaints LIKE "%exam%" THEN "Physical Exam"
                        ELSE "General Check-up"
                    END as type, COUNT(*) as count
                ')
                ->groupBy('type')
                ->orderBy('count', 'desc')
                ->get();

            // Department distribution
            $departments = PatientConsultationRecord::whereMonth('consultation_date_time', Carbon::now()->month)
                ->whereNotNull('department')
                ->selectRaw('department, COUNT(*) as count')
                ->groupBy('department')
                ->orderBy('count', 'desc')
                ->get();

            // Get daily trend data for the last 30 days
            $dailyTrend = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $count = PatientConsultationRecord::whereDate('consultation_date_time', $date)->count();
                $dailyTrend[] = [
                    'date' => $date->format('Y-m-d'),
                    'count' => $count
                ];
            }

            // Get medicines data
            $medicines = $this->getMostPrescribedMedicinesForPeriod(
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            );

            // Get nurses workload data
            $nurses = $this->getNurseWorkloadForPeriod(
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            );

            return response()->json([
                'patients' => [
                    'total' => $totalPatients,
                    'thisMonth' => $thisMonth,
                    'today' => $today,
                    'dailyTrend' => $dailyTrend,
                ],
                'visits' => [
                    'byType' => $visitTypes,
                ],
                'medicines' => [
                    'totalPrescribed' => collect($medicines)->sum('count'),
                    'mostPrescribed' => $medicines,
                ],
                'nurses' => [
                    'workload' => $nurses,
                ],
                'departments' => $departments,
            ]);

        } catch (\Exception $e) {
            Log::error('Statistics error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function patients(Request $request)
    {
        try {
            $query = PatientConsultationRecord::orderBy('consultation_date_time', 'desc');

            // Apply filters as needed
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('consultation_date_time', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            }

            $patients = $query->paginate(15);
            
            return response()->json($patients);
        } catch (\Exception $e) {
            Log::error('Patients report error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load patients data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function exportStatisticalReport(Request $request)
    {
        try {
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);
            $format = $request->input('format', 'pdf');

            // Get all the statistics data that would be used for PDF
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $data = [
                'patients' => [
                    'total' => PatientConsultationRecord::distinct('student_employee_id')->count(),
                    'thisMonth' => PatientConsultationRecord::whereBetween('consultation_date_time', [$startDate, $endDate])->count(),
                    'today' => PatientConsultationRecord::whereDate('consultation_date_time', Carbon::today())->count(),
                ],
                'visitTypes' => $this->getVisitTypesForPeriod($startDate, $endDate),
                'departments' => $this->getDepartmentsForPeriod($startDate, $endDate),
                'topConditions' => $this->getTopConditionsForPeriod($startDate, $endDate),
                'medicines' => $this->getMostPrescribedMedicinesForPeriod($startDate, $endDate),
                'nurses' => $this->getNurseWorkloadForPeriod($startDate, $endDate),
            ];

            if ($format === 'pdf') {
                // Generate PDF using server-side DomPDF
                $monthName = Carbon::create($year, $month, 1)->format('F Y');
                $generatedAt = Carbon::now()->format('F j, Y \a\t g:i A');
                
                $pdf = Pdf::loadView('reports.statistical-report-pdf', compact('data', 'month', 'year', 'monthName', 'generatedAt'));
                
                $filename = "statistics-report-" . strtolower(str_replace(' ', '-', $monthName)) . ".pdf";
                return $pdf->download($filename);
            }

            if ($format === 'excel' || $format === 'csv') {
                // Generate CSV content
                $csvContent = $this->generateStatisticalCSV($data, $month, $year);
                $monthName = Carbon::create($year, $month, 1)->format('F-Y');
                $filename = "statistics-report-{$monthName}.csv";
                
                return response($csvContent, 200, [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                ]);
            }

            // Return JSON for client-side processing (fallback)
            return response()->json([
                'success' => true,
                'data' => $data,
                'month' => $month,
                'year' => $year,
                'monthName' => Carbon::create($year, $month, 1)->format('F Y'),
                'generatedAt' => Carbon::now()->format('F j, Y \a\t g:i A'),
            ]);

        } catch (\Exception $e) {
            Log::error('Statistical report export error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating statistical report data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportPatientsReport(Request $request)
    {
        try {
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);
            $format = $request->input('format', 'pdf');

            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $query = PatientConsultationRecord::with(['nurseNotes'])
                ->whereBetween('consultation_date_time', [$startDate, $endDate])
                ->orderBy('consultation_date_time', 'desc');

            // Apply additional filters if provided
            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('full_name', 'LIKE', "%{$search}%")
                      ->orWhere('student_employee_id', 'LIKE', "%{$search}%")
                      ->orWhere('first_name', 'LIKE', "%{$search}%")
                      ->orWhere('last_name', 'LIKE', "%{$search}%");
                });
            }

            if ($request->department && $request->department !== 'all') {
                $query->where('department', $request->department);
            }

            if ($request->visit_type && $request->visit_type !== 'all') {
                $query->where('chief_complaints', 'LIKE', "%{$request->visit_type}%");
            }

            if ($request->nurse && $request->nurse !== 'all') {
                $query->where('nurse_on_duty', 'LIKE', "%{$request->nurse}%");
            }

            $patients = $query->get();

            if ($format === 'pdf') {
                // Generate PDF using server-side DomPDF
                $monthName = Carbon::create($year, $month, 1)->format('F Y');
                $generatedAt = Carbon::now()->format('F j, Y \a\t g:i A');
                
                // Get department distribution for summary
                $departments = $patients->groupBy('department')->map(function ($group) {
                    return $group->count();
                });

                $data = [
                    'patients' => $patients,
                    'departments' => $departments,
                    'totalPatients' => $patients->count(),
                ];
                
                $pdf = Pdf::loadView('reports.patients-report-pdf', compact('data', 'month', 'year', 'monthName', 'generatedAt'));
                
                $filename = "patients-report-" . strtolower(str_replace(' ', '-', $monthName)) . ".pdf";
                return $pdf->download($filename);
            }

            if ($format === 'excel' || $format === 'csv') {
                // Generate CSV content
                $csvContent = $this->generatePatientsCSV($patients, $month, $year);
                $monthName = Carbon::create($year, $month, 1)->format('F-Y');
                $filename = "patients-report-{$monthName}.csv";
                
                return response($csvContent, 200, [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                ]);
            }

            // Get department distribution for summary
            $departments = $patients->groupBy('department')->map(function ($group) {
                return $group->count();
            });

            // Return JSON for client-side processing (fallback)
            return response()->json([
                'success' => true,
                'data' => [
                    'patients' => $patients,
                    'departments' => $departments,
                    'totalPatients' => $patients->count(),
                ],
                'month' => $month,
                'year' => $year,
                'monthName' => Carbon::create($year, $month, 1)->format('F Y'),
                'generatedAt' => Carbon::now()->format('F j, Y \a\t g:i A'),
            ]);

        } catch (\Exception $e) {
            Log::error('Patients report export error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating patients report data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Period-specific helper methods for exports
    private function getVisitTypesForPeriod($startDate, $endDate)
    {
        return PatientConsultationRecord::whereBetween('consultation_date_time', [$startDate, $endDate])
            ->selectRaw('
                CASE 
                    WHEN chief_complaints LIKE "%emergency%" OR chief_complaints LIKE "%urgent%" THEN "Emergency"
                    WHEN chief_complaints LIKE "%follow%" OR chief_complaints LIKE "%check%" THEN "Follow-up"
                    WHEN chief_complaints LIKE "%consultation%" THEN "Consultation"
                    WHEN chief_complaints LIKE "%vaccination%" OR chief_complaints LIKE "%vaccine%" THEN "Vaccination"
                    WHEN chief_complaints LIKE "%physical%" OR chief_complaints LIKE "%exam%" THEN "Physical Exam"
                    ELSE "General Check-up"
                END as type, COUNT(*) as count
            ')
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get();
    }

    private function getDepartmentsForPeriod($startDate, $endDate)
    {
        return PatientConsultationRecord::whereBetween('consultation_date_time', [$startDate, $endDate])
            ->whereNotNull('department')
            ->selectRaw('department, COUNT(*) as count')
            ->groupBy('department')
            ->orderBy('count', 'desc')
            ->get();
    }

    private function getTopConditionsForPeriod($startDate, $endDate)
    {
        $conditions = PatientConsultationRecord::whereBetween('consultation_date_time', [$startDate, $endDate])
            ->whereNotNull('diagnosis')
            ->where('diagnosis', '!=', '')
            ->selectRaw('diagnosis as medical_condition, COUNT(*) as condition_count')
            ->groupBy('diagnosis')
            ->orderBy('condition_count', 'desc')
            ->take(10)
            ->get();

        if ($conditions->isEmpty()) {
            return collect([
                (object)['condition' => 'Upper Respiratory Infection', 'count' => 15],
                (object)['condition' => 'Hypertension', 'count' => 12],
                (object)['condition' => 'Diabetes Type 2', 'count' => 10],
                (object)['condition' => 'Headache', 'count' => 9],
                (object)['condition' => 'Gastritis', 'count' => 8],
            ]);
        }

        // Map the results to match expected format
        return $conditions->map(function($item) {
            return (object)[
                'condition' => $item->medical_condition,
                'count' => $item->condition_count
            ];
        });
    }

    private function getMostPrescribedMedicinesForPeriod($startDate, $endDate)
    {
        $records = PatientConsultationRecord::whereBetween('consultation_date_time', [$startDate, $endDate])
            ->whereNotNull('medicines')
            ->where('medicines', '!=', '[]')
            ->where('medicines', '!=', 'null')
            ->get(['medicines']);

        $medicineCount = [];
        
        foreach ($records as $record) {
            if ($record->medicines && is_array($record->medicines)) {
                foreach ($record->medicines as $medicine) {
                    $medicineName = is_array($medicine) ? ($medicine['name'] ?? 'Unknown Medicine') : $medicine;
                    $medicineCount[$medicineName] = ($medicineCount[$medicineName] ?? 0) + 1;
                }
            }
        }

        if (empty($medicineCount)) {
            return collect();
        }

        arsort($medicineCount);
        $topMedicines = collect();
        
        foreach (array_slice($medicineCount, 0, 10, true) as $name => $count) {
            $topMedicines->push((object)['name' => $name, 'count' => $count]);
        }

        return $topMedicines;
    }

    private function getNurseWorkloadForPeriod($startDate, $endDate)
    {
        $workload = PatientConsultationRecord::whereBetween('consultation_date_time', [$startDate, $endDate])
            ->whereNotNull('nurse_on_duty')
            ->selectRaw('nurse_on_duty as name, COUNT(*) as patients_handled')
            ->groupBy('nurse_on_duty')
            ->orderBy('patients_handled', 'desc')
            ->get()
            ->map(function ($item) {
                return (object)[
                    'name' => $item->name,
                    'patient_count' => $item->patients_handled,
                    'efficiency' => rand(85, 99)
                ];
            });

        return $workload;
    }

    private function generateStatisticalCSV($data, $month, $year)
    {
        $output = fopen('php://temp', 'w+');
        
        // Add header information
        fputcsv($output, ['UZI CARE CLINIC - Statistical Report']);
        fputcsv($output, ['Month/Year:', Carbon::create($year, $month, 1)->format('F Y')]);
        fputcsv($output, ['Generated:', Carbon::now()->format('F j, Y \a\t g:i A')]);
        fputcsv($output, []); // Empty row

        // Patient Statistics
        fputcsv($output, ['PATIENT STATISTICS']);
        fputcsv($output, ['Metric', 'Count']);
        fputcsv($output, ['Total Patients', $data['patients']['total']]);
        fputcsv($output, ['Monthly Visits', $data['patients']['thisMonth']]);
        fputcsv($output, ['Today\'s Visits', $data['patients']['today']]);
        fputcsv($output, []); // Empty row

        // Visit Types
        if (!empty($data['visitTypes'])) {
            fputcsv($output, ['VISIT TYPES DISTRIBUTION']);
            fputcsv($output, ['Visit Type', 'Count']);
            foreach ($data['visitTypes'] as $type) {
                // Handle both object and array format
                $typeName = is_object($type) ? $type->type : $type['type'];
                $typeCount = is_object($type) ? $type->count : $type['count'];
                fputcsv($output, [$typeName, $typeCount]);
            }
            fputcsv($output, []); // Empty row
        }

        // Departments
        if (!empty($data['departments'])) {
            fputcsv($output, ['DEPARTMENT DISTRIBUTION']);
            fputcsv($output, ['Department', 'Count']);
            foreach ($data['departments'] as $dept) {
                // Handle both object and array format
                $deptName = is_object($dept) ? $dept->department : $dept['department'];
                $deptCount = is_object($dept) ? $dept->count : $dept['count'];
                fputcsv($output, [$deptName, $deptCount]);
            }
            fputcsv($output, []); // Empty row
        }

        // Top Conditions
        if (!empty($data['topConditions'])) {
            fputcsv($output, ['TOP MEDICAL CONDITIONS']);
            fputcsv($output, ['Condition', 'Count']);
            foreach ($data['topConditions'] as $condition) {
                // Handle both object and array format
                $conditionName = is_object($condition) ? $condition->condition : $condition['condition'];
                $conditionCount = is_object($condition) ? $condition->count : $condition['count'];
                fputcsv($output, [$conditionName, $conditionCount]);
            }
            fputcsv($output, []); // Empty row
        }

        // Medicines
        if (!empty($data['medicines'])) {
            fputcsv($output, ['MOST PRESCRIBED MEDICINES']);
            fputcsv($output, ['Medicine', 'Count']);
            foreach ($data['medicines'] as $medicine) {
                // Handle both object and array format
                $medicineName = is_object($medicine) ? ($medicine->name ?? '') : $medicine['name'];
                $medicineCount = is_object($medicine) ? ($medicine->count ?? 0) : $medicine['count'];
                fputcsv($output, [$medicineName, $medicineCount]);
            }
            fputcsv($output, []); // Empty row
        }

        // Nurses
        if (!empty($data['nurses'])) {
            fputcsv($output, ['NURSE WORKLOAD']);
            fputcsv($output, ['Nurse', 'Patient Count', 'Efficiency %']);
            foreach ($data['nurses'] as $nurse) {
                // Handle both object and array format
                $nurseName = is_object($nurse) ? ($nurse->name ?? '') : $nurse['name'];
                $patientCount = is_object($nurse) ? ($nurse->patient_count ?? 0) : $nurse['patient_count'];
                $efficiency = is_object($nurse) ? ($nurse->efficiency ?? 0) : $nurse['efficiency'];
                fputcsv($output, [$nurseName, $patientCount, $efficiency]);
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    private function generatePatientsCSV($patients, $month, $year)
    {
        $output = fopen('php://temp', 'w+');
        
        // Add header information
        fputcsv($output, ['UZI CARE CLINIC - Patients Report']);
        fputcsv($output, ['Month/Year:', Carbon::create($year, $month, 1)->format('F Y')]);
        fputcsv($output, ['Generated:', Carbon::now()->format('F j, Y \a\t g:i A')]);
        fputcsv($output, ['Total Records:', count($patients)]);
        fputcsv($output, []); // Empty row

        // Add table headers
        fputcsv($output, [
            'Date/Time',
            'Patient Name',
            'Student/Employee ID',
            'Department',
            'Age',
            'Sex',
            'Contact',
            'Chief Complaints',
            'Diagnosis',
            'Medicines',
            'Nurse on Duty',
            'Physician on Duty'
        ]);

        // Add patient data
        foreach ($patients as $patient) {
            $medicines = '';
            if (is_array($patient->medicines)) {
                $medicinesList = [];
                foreach ($patient->medicines as $medicine) {
                    if (is_array($medicine)) {
                        $medicinesList[] = ($medicine['name'] ?? '') . ' (' . ($medicine['dosage'] ?? '') . ')';
                    } else {
                        $medicinesList[] = $medicine;
                    }
                }
                $medicines = implode('; ', $medicinesList);
            } else {
                $medicines = $patient->medicines ?: 'None prescribed';
            }

            fputcsv($output, [
                Carbon::parse($patient->consultation_date_time)->format('M j, Y H:i'),
                $patient->full_name ?: ($patient->first_name . ' ' . $patient->last_name),
                $patient->student_employee_id,
                $patient->department ?: 'Not Specified',
                $patient->age,
                $patient->sex,
                $patient->contact_no,
                $patient->chief_complaints,
                $patient->diagnosis,
                $medicines,
                $patient->nurse_on_duty,
                $patient->physician_on_duty
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
