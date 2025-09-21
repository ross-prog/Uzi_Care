<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class StatisticalReportExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $data;
    protected $month;
    protected $year;
    protected $monthName;

    public function __construct($data, $month, $year)
    {
        $this->data = $data;
        $this->month = $month;
        $this->year = $year;
        $this->monthName = Carbon::create($year, $month, 1)->format('F Y');
    }

    /**
     * Create headers with clinic info
     */
    public function headings(): array
    {
        return [
            ['UZI CARE CLINIC - Statistical Report'],
            ['Month/Year: ' . $this->monthName],
            ['Generated: ' . Carbon::now()->format('F j, Y \a\t g:i A')],
            [], // Empty row
            ['PATIENT STATISTICS'],
            ['Metric', 'Count'],
        ];
    }

    /**
     * Create data array with different sections
     */
    public function array(): array
    {
        $data = [];
        
        // Patient Statistics
        $data[] = ['Total Patients', $this->data['patients']['total']];
        $data[] = ['Monthly Visits', $this->data['patients']['thisMonth']];
        $data[] = ['Today\'s Visits', $this->data['patients']['today']];
        $data[] = []; // Empty row

        // Visit Types
        if (!empty($this->data['visitTypes'])) {
            $data[] = ['VISIT TYPES DISTRIBUTION'];
            $data[] = ['Visit Type', 'Count'];
            foreach ($this->data['visitTypes'] as $type) {
                $typeName = is_object($type) ? $type->type : $type['type'];
                $typeCount = is_object($type) ? $type->count : $type['count'];
                $data[] = [$typeName, $typeCount];
            }
            $data[] = []; // Empty row
        }

        // Departments
        if (!empty($this->data['departments'])) {
            $data[] = ['DEPARTMENT DISTRIBUTION'];
            $data[] = ['Department', 'Count'];
            foreach ($this->data['departments'] as $dept) {
                $deptName = is_object($dept) ? $dept->department : $dept['department'];
                $deptCount = is_object($dept) ? $dept->count : $dept['count'];
                $data[] = [$deptName, $deptCount];
            }
            $data[] = []; // Empty row
        }

        // Top Conditions
        if (!empty($this->data['topConditions'])) {
            $data[] = ['TOP CONDITIONS'];
            $data[] = ['Condition', 'Count'];
            foreach ($this->data['topConditions'] as $condition) {
                $conditionName = is_object($condition) ? ($condition->condition ?? $condition->medical_condition ?? '') : ($condition['condition'] ?? $condition['medical_condition'] ?? '');
                $conditionCount = is_object($condition) ? ($condition->count ?? $condition->condition_count ?? 0) : ($condition['count'] ?? $condition['condition_count'] ?? 0);
                $data[] = [$conditionName, $conditionCount];
            }
            $data[] = []; // Empty row
        }

        // Medicines
        if (!empty($this->data['medicines'])) {
            $data[] = ['MOST PRESCRIBED MEDICINES'];
            $data[] = ['Medicine', 'Count'];
            foreach ($this->data['medicines'] as $medicine) {
                $medicineName = is_object($medicine) ? ($medicine->name ?? '') : ($medicine['name'] ?? '');
                $medicineCount = is_object($medicine) ? ($medicine->count ?? 0) : ($medicine['count'] ?? 0);
                $data[] = [$medicineName, $medicineCount];
            }
            $data[] = []; // Empty row
        }

        // Nurses
        if (!empty($this->data['nurses'])) {
            $data[] = ['NURSE WORKLOAD'];
            $data[] = ['Nurse', 'Consultations'];
            foreach ($this->data['nurses'] as $nurse) {
                $nurseName = is_object($nurse) ? ($nurse->name ?? '') : ($nurse['name'] ?? '');
                $nurseCount = is_object($nurse) ? ($nurse->patient_count ?? 0) : ($nurse['patient_count'] ?? 0);
                $data[] = [$nurseName, $nurseCount];
            }
        }

        return $data;
    }

    /**
     * Column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 15,
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Title styling
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => '1F2937']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ]
            ],
            // Header info styling
            '2:3' => [
                'font' => [
                    'italic' => true,
                    'color' => ['rgb' => '6B7280']
                ]
            ],
            // Section headers styling
            'A:A' => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]
        ];
    }

    /**
     * Handle additional formatting
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Get the range of data
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Apply borders to data sections only
                $dataRange = 'A6:' . $highestColumn . $highestRow;
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Merge title cell
                $sheet->mergeCells('A1:B1');
                
                // Center align numeric columns
                $sheet->getStyle('B:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Highlight section headers
                $currentRow = 7; // Start after patient statistics header
                while ($currentRow <= $highestRow) {
                    $cellValue = $sheet->getCell('A' . $currentRow)->getValue();
                    if (in_array($cellValue, ['VISIT TYPES DISTRIBUTION', 'DEPARTMENT DISTRIBUTION', 'TOP CONDITIONS', 'MOST PRESCRIBED MEDICINES', 'NURSE WORKLOAD'])) {
                        $sheet->getStyle('A' . $currentRow . ':B' . $currentRow)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('E5E7EB');
                    }
                    $currentRow++;
                }
                
                // Auto-size columns
                foreach (range('A', $highestColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }
}