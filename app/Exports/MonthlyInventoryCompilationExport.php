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

class MonthlyInventoryCompilationExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $campusData;
    protected $allMedicines;
    protected $reportPeriod;
    protected $campusList;

    public function __construct($campusData, $allMedicines, $reportPeriod)
    {
        $this->campusData = $campusData;
        $this->allMedicines = $allMedicines;
        $this->reportPeriod = $reportPeriod;
        $this->campusList = array_keys($campusData);
    }

    /**
     * Create multi-row headers with merged cells
     */
    public function headings(): array
    {
        // First row: Campus headers (will be merged)
        $row1 = ['Medicine Name'];
        foreach ($this->campusList as $campus) {
            $formattedCampus = ucwords(str_replace('_', ' ', $campus));
            $row1[] = $formattedCampus;
            $row1[] = ''; // Empty cell for merging
        }
        $row1[] = 'Total Medicines';
        
        // Second row: Sub-headers
        $row2 = [''];
        foreach ($this->campusList as $campus) {
            $row2[] = 'Current Stock';
            $row2[] = 'Qty to Order';
        }
        $row2[] = 'Across Campus Needed';
        
        return [$row1, $row2];
    }

    /**
     * Create data array
     */
    public function array(): array
    {
        $data = [];
        
        foreach ($this->allMedicines as $medicine) {
            $row = [$medicine];
            
            foreach ($this->campusList as $campus) {
                $campusData = $this->getCampusData($campus, $medicine);
                $row[] = $campusData['current_stock'];
                $row[] = $campusData['quantity_to_order'];
            }
            
            $row[] = $this->getTotalQuantityNeeded($medicine);
            $data[] = $row;
        }
        
        return $data;
    }

    /**
     * Get campus data for a medicine
     */
    private function getCampusData($campus, $medicineName)
    {
        if (!isset($this->campusData[$campus]['inventory_data'])) {
            return ['current_stock' => '-', 'quantity_to_order' => '-'];
        }
        
        foreach ($this->campusData[$campus]['inventory_data'] as $item) {
            if ($item['medicine_name'] === $medicineName) {
                return [
                    'current_stock' => $item['current_stock'] ?? '-',
                    'quantity_to_order' => $item['quantity_to_order'] ?? '-'
                ];
            }
        }
        
        return ['current_stock' => '-', 'quantity_to_order' => '-'];
    }

    /**
     * Calculate total quantity needed across campuses
     */
    private function getTotalQuantityNeeded($medicineName)
    {
        $total = 0;
        
        foreach ($this->campusList as $campus) {
            $data = $this->getCampusData($campus, $medicineName);
            if ($data['quantity_to_order'] !== '-' && is_numeric($data['quantity_to_order'])) {
                $total += (int)$data['quantity_to_order'];
            }
        }
        
        return $total;
    }

    /**
     * Column widths
     */
    public function columnWidths(): array
    {
        $widths = ['A' => 25]; // Medicine Name column
        
        $column = 'B';
        foreach ($this->campusList as $campus) {
            $widths[$column++] = 15; // Current Stock
            $widths[$column++] = 15; // Qty to Order
        }
        
        $widths[$column] = 20; // Total column
        
        return $widths;
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Header rows styling
            '1:2' => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'] // Blue background
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            // Medicine name column
            'A:A' => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]
        ];
    }

    /**
     * Handle cell merging and additional formatting
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Get the range of data
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                // Merge medicine name header cell (A1:A2)
                $sheet->mergeCells('A1:A2');
                
                // Merge campus header cells
                $currentCol = 'B';
                foreach ($this->campusList as $campus) {
                    $startCol = $currentCol;
                    $currentCol = chr(ord($currentCol) + 1); // Next column
                    $endCol = $currentCol;
                    $currentCol = chr(ord($currentCol) + 1); // Skip to next campus
                    
                    // Merge campus header across two columns
                    $sheet->mergeCells($startCol . '1:' . $endCol . '1');
                }
                
                // Merge total column header (last column)
                $totalCol = $highestColumn;
                $sheet->mergeCells($totalCol . '1:' . $totalCol . '2');
                
                // Apply borders to all data
                $dataRange = 'A1:' . $highestColumn . $highestRow;
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Highlight the total column with light blue background
                $totalRange = $totalCol . '3:' . $totalCol . $highestRow;
                $sheet->getStyle($totalRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0F2FE');
                
                // Center align numeric columns (starting from row 3 due to 2-row header)
                for ($col = 'B'; $col <= $highestColumn; $col++) {
                    $sheet->getStyle($col . '3:' . $col . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                
                // Auto-size columns
                foreach (range('A', $highestColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }
}