# CSV Templates Documentation

This folder contains reusable CSV export templates for the Uzi Care system. Each template is designed to handle specific types of data exports with consistent formatting and structure.

**Note:** Most report exports have been converted to Excel format for better formatting and professional presentation. Only monthly inventory reports remain as CSV templates.

## 📁 Folder Structure

```
resources/js/templates/csv/
├── index.js                           # Main export module with common utilities
├── MonthlyInventoryReport.js          # Individual campus monthly inventory reports
└── README.md                          # This documentation file
```

## 🎯 Available Templates

### 1. Monthly Inventory Report (`MonthlyInventoryReport.js`)
**Purpose:** Export individual campus monthly inventory reports
**Used in:** `Pages/Reports/MonthlyInventory/Compilation.vue`
**Columns:** Medicine Name, [Campus] - Current Stock, [Campus] - Quantity to Order, Total Medicines Across Campus Needed

### 2. Monthly Inventory Report (`MonthlyInventoryReport.js`)
**Purpose:** Export individual campus monthly inventory reports
**Used in:** Individual campus inventory report pages
**Columns:** Medicine Name, Medicine Type, Current Stock, Low Stock Threshold, Stock Status, Quantity to Order, Earliest Expiry Date, Expiry Status, Total Batches, Campus

### 3. Patients Report (`PatientsReport.js`)
**Purpose:** Export patient consultation reports
**Used in:** `Pages/Reports/Index.vue`
**Columns:** Date, Patient Name, Age, Gender, Department, Campus, Chief Complaint, Diagnosis, Treatment, Attending Nurse, Status

### 4. Statistical Report (`StatisticalReport.js`)
**Purpose:** Export statistical reports and analytics
**Used in:** `Pages/Reports/Index.vue`
**Columns:** Metric, Campus, Department, Count, Percentage, Period, Category

## 🔧 Common Utilities

The `index.js` file provides common utility functions:

- `escapeCSVField(field)` - Escape CSV field content to handle commas, quotes, and newlines
- `formatDateForCSV(date)` - Format dates consistently for CSV export
- `downloadCSVFile(content, filename)` - Create and download CSV files

## 💡 Usage Examples

### Basic Usage
```javascript
import { generateMonthlyInventoryCompilationCSV, downloadCSV } from "@/templates/csv/MonthlyInventoryCompilation.js";

const exportData = () => {
    const { content, filename } = generateMonthlyInventoryCompilationCSV(
        campusData, 
        allMedicines, 
        reportPeriod
    );
    downloadCSV(content, filename);
};
```

### Using Common Utilities
```javascript
import { escapeCSVField, formatDateForCSV, downloadCSVFile } from "@/templates/csv/index.js";

const customExport = () => {
    const csvData = [
        ['Name', 'Date', 'Description'],
        [escapeCSVField(name), formatDateForCSV(date), escapeCSVField(description)]
    ];
    
    const csvContent = csvData.map(row => row.join(',')).join('\n');
    downloadCSVFile(csvContent, 'custom-export.csv');
};
```

## 📋 Template Standards

All CSV templates should follow these standards:

1. **Function Naming:** Use `generate[ReportType]CSV` pattern
2. **Return Object:** Return `{ content, filename }` object
3. **Data Validation:** Handle missing/null data gracefully with fallbacks
4. **Field Escaping:** Properly escape CSV fields that contain commas, quotes, or newlines
5. **Date Formatting:** Use consistent date formatting across all templates
6. **Documentation:** Include JSDoc comments for all functions

## 🔄 Adding New Templates

To add a new CSV template:

1. Create a new `.js` file in this directory
2. Export the main generation function
3. Follow the established patterns and standards
4. Add the export to `index.js`
5. Update this README file
6. Import and use in the appropriate Vue components

## 🐛 Error Handling

Templates should handle common error scenarios:
- Missing or null data
- Invalid date formats
- Special characters in field content
- Empty arrays or objects
- Network errors during download

## 🎨 File Naming Conventions

- **Template files:** Use PascalCase (e.g., `MonthlyInventoryReport.js`)
- **Generated CSV files:** Use lowercase with hyphens (e.g., `monthly-inventory-report-main-campus-september-2025.csv`)
- **Function names:** Use camelCase with descriptive names (e.g., `generateMonthlyInventoryReportCSV`)