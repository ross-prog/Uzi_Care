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

class PatientsReportExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
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
     * Create headers with clinic info and column headers
     */
    public function headings(): array
    {
        return [
            ['UZI CARE CLINIC - Patients Report'],
            ['Month/Year: ' . $this->monthName],
            ['Generated: ' . Carbon::now()->format('F j, Y \a\t g:i A')],
            [], // Empty row
            [
                'Date',
                'Patient Name',
                'Age',
                'Contact',
                'Type of Visit',
                'Department',
                'Condition',
                'Medicine',
                'Nurse'
            ]
        ];
    }

    /**
     * Create data array from consultation records
     */
    public function array(): array
    {
        $data = [];
        
        foreach ($this->data as $record) {
            // Handle both object and array formats
            if (is_object($record)) {
                $data[] = [
                    $record->visit_date ? Carbon::parse($record->visit_date)->format('Y-m-d') : '',
                    $record->patient_name ?? '',
                    $record->age ?? '',
                    $record->contact ?? '',
                    $record->type_of_visit ?? '',
                    $record->department ?? '',
                    $record->condition ?? '',
                    $record->medicine ?? '',
                    $record->nurse ?? ''
                ];
            } else {
                $data[] = [
                    isset($record['visit_date']) ? Carbon::parse($record['visit_date'])->format('Y-m-d') : '',
                    $record['patient_name'] ?? '',
                    $record['age'] ?? '',
                    $record['contact'] ?? '',
                    $record['type_of_visit'] ?? '',
                    $record['department'] ?? '',
                    $record['condition'] ?? '',
                    $record['medicine'] ?? '',
                    $record['nurse'] ?? ''
                ];
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
            'A' => 12, // Date
            'B' => 20, // Patient Name
            'C' => 8,  // Age
            'D' => 15, // Contact
            'E' => 15, // Type of Visit
            'F' => 15, // Department
            'G' => 25, // Condition
            'H' => 20, // Medicine
            'I' => 15, // Nurse
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
            // Column headers styling
            5 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3B82F6']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            // Data rows styling
            'A6:I1000' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
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
                
                // Merge title cell across all columns
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                
                // Apply borders to the entire data table including headers
                if ($highestRow >= 5) {
                    $tableRange = 'A5:' . $highestColumn . $highestRow;
                    $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }
                
                // Center align specific columns
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Date
                $sheet->getStyle('C:C')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Age
                
                // Apply alternating row colors to data rows (starting from row 6)
                for ($row = 6; $row <= $highestRow; $row += 2) {
                    $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('F9FAFB');
                }
                
                // Auto-size columns based on content
                foreach (range('A', $highestColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                // Set row height for headers
                $sheet->getRowDimension(5)->setRowHeight(25);
            }
        ];
    }
}