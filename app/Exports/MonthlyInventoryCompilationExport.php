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
    protected $itemsByCategory;
    protected $reportPeriod;
    protected $campusList;

    public function __construct($campusData, $allMedicines, $itemsByCategory, $reportPeriod)
    {
        $this->campusData = $campusData;
        $this->allMedicines = $allMedicines;
        $this->itemsByCategory = $itemsByCategory;
        $this->reportPeriod = $reportPeriod;
        $this->campusList = array_keys($campusData);
    }

    /**
     * Create multi-row headers with merged cells
     */
    public function headings(): array
    {
        $headers = [];
        
        // Medicine section headers
        $headers[] = ['MEDICINE INVENTORY COMPILATION - ' . $this->reportPeriod];
        $headers[] = [''];
        
        // First row: Campus headers (will be merged)
        $row1 = ['Medicine Name'];
        foreach ($this->campusList as $campus) {
            $formattedCampus = ucwords(str_replace('_', ' ', $campus));
            $row1[] = $formattedCampus;
            $row1[] = ''; // Empty cell for merging
        }
        $row1[] = 'Total Needed';
        $headers[] = $row1;
        
        // Second row: Sub-headers
        $row2 = [''];
        foreach ($this->campusList as $campus) {
            $row2[] = 'Current Stock';
            $row2[] = 'Qty to Order';
        }
        $row2[] = 'Across Campus';
        $headers[] = $row2;
        
        return $headers;
    }

    /**
     * Create data array
     */
    public function array(): array
    {
        $data = [];
        
        // Add medicines data
        if (!empty($this->itemsByCategory['Medicines'])) {
            foreach ($this->itemsByCategory['Medicines'] as $medicine) {
                $row = [$medicine];
                
                foreach ($this->campusList as $campus) {
                    $campusData = $this->getCampusData($campus, $medicine);
                    $row[] = $campusData['current_stock'];
                    $row[] = $campusData['quantity_to_order'];
                }
                
                $row[] = $this->getTotalQuantityNeeded($medicine);
                $data[] = $row;
            }
        }
        
        // Add empty rows for separation
        $data[] = [''];
        $data[] = [''];
        
        // Add supplies section header
        $suppliesHeader = ['SUPPLIES INVENTORY SUMMARY'];
        for ($i = 1; $i < count($this->campusList) * 2 + 1; $i++) {
            $suppliesHeader[] = '';
        }
        $data[] = $suppliesHeader;
        $data[] = [''];
        
        // Supplies table headers
        $suppliesRow1 = ['Supply Item'];
        foreach ($this->campusList as $campus) {
            $formattedCampus = ucwords(str_replace('_', ' ', $campus));
            $suppliesRow1[] = $formattedCampus;
        }
        $suppliesRow1[] = 'Total Stock';
        $data[] = $suppliesRow1;
        
        // Add supplies data
        if (!empty($this->itemsByCategory['Supplies'])) {
            foreach ($this->itemsByCategory['Supplies'] as $supply) {
                $row = [$supply];
                
                foreach ($this->campusList as $campus) {
                    $campusData = $this->getCampusData($campus, $supply);
                    $row[] = $campusData['current_stock'];
                }
                
                $row[] = $this->getTotalStock($supply);
                $data[] = $row;
            }
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
     * Calculate total stock across campuses (for supplies)
     */
    private function getTotalStock($itemName)
    {
        $total = 0;
        
        foreach ($this->campusList as $campus) {
            $data = $this->getCampusData($campus, $itemName);
            if ($data['current_stock'] !== '-' && is_numeric($data['current_stock'])) {
                $total += (int)$data['current_stock'];
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
            // Title row styling
            '1:1' => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F2937'] // Dark gray background
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            // Medicine header rows styling (rows 3-4)
            '3:4' => [
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
                
                // Merge title row across all columns
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                
                // Merge medicine name header cell (A3:A4)
                $sheet->mergeCells('A3:A4');
                
                // Merge campus header cells for medicines section (row 3)
                $currentCol = 'B';
                foreach ($this->campusList as $campus) {
                    $startCol = $currentCol;
                    $currentCol = chr(ord($currentCol) + 1); // Next column
                    $endCol = $currentCol;
                    $currentCol = chr(ord($currentCol) + 1); // Skip to next campus
                    
                    // Merge campus header across two columns
                    $sheet->mergeCells($startCol . '3:' . $endCol . '3');
                }
                
                // Merge total column header for medicines (last column)
                $totalCol = $highestColumn;
                $sheet->mergeCells($totalCol . '3:' . $totalCol . '4');
                
                // Find supplies section and format it
                $suppliesHeaderRow = 0;
                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    if (strpos($cellValue, 'SUPPLIES INVENTORY') !== false) {
                        $suppliesHeaderRow = $row;
                        break;
                    }
                }
                
                if ($suppliesHeaderRow > 0) {
                    // Style supplies header
                    $sheet->mergeCells('A' . $suppliesHeaderRow . ':' . $highestColumn . $suppliesHeaderRow);
                    $sheet->getStyle('A' . $suppliesHeaderRow . ':' . $highestColumn . $suppliesHeaderRow)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                            'color' => ['rgb' => 'FFFFFF']
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '059669'] // Green background
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ]
                    ]);
                    
                    // Style supplies table headers (row after supplies header)
                    $suppliesTableHeaderRow = $suppliesHeaderRow + 2;
                    if ($suppliesTableHeaderRow <= $highestRow) {
                        $sheet->getStyle('A' . $suppliesTableHeaderRow . ':' . $highestColumn . $suppliesTableHeaderRow)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF']
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '10B981'] // Light green background
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER
                            ]
                        ]);
                    }
                }
                
                // Apply borders to all data
                $dataRange = 'A1:' . $highestColumn . $highestRow;
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                
                // Highlight the total columns with light backgrounds
                $totalRange = $totalCol . '5:' . $totalCol . $highestRow;
                $sheet->getStyle($totalRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0F2FE');
                
                // Center align numeric columns (starting from row 5 for medicines)
                for ($col = 'B'; $col <= $highestColumn; $col++) {
                    $sheet->getStyle($col . '5:' . $col . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                
                // Auto-size columns
                foreach (range('A', $highestColumn) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            }
        ];
    }
}