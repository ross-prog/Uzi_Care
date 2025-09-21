/**
 * Individual Monthly Inventory Report CSV Export Template
 * Generates CSV data for individual campus monthly inventory reports
 */

export const generateMonthlyInventoryReportCSV = (inventoryData, campus, reportPeriod) => {
    const csvData = [];
    
    // Create header row
    const header = [
        'Medicine Name',
        'Medicine Type',
        'Current Stock',
        'Low Stock Threshold',
        'Stock Status',
        'Quantity to Order',
        'Earliest Expiry Date',
        'Expiry Status',
        'Total Batches',
        'Campus'
    ];
    csvData.push(header);

    // Create data rows
    inventoryData.forEach(item => {
        const stockStatus = item.current_stock <= item.low_stock_threshold ? 'Low Stock' : 'Normal';
        const expiryStatus = item.is_expiring_soon ? 'Expiring Soon' : 'Normal';
        
        const row = [
            item.medicine_name || '-',
            item.medicine_type || '-',
            item.current_stock || '0',
            item.low_stock_threshold || '0',
            stockStatus,
            item.quantity_to_order || '0',
            item.earliest_expiry || '-',
            expiryStatus,
            item.batch_count || '0',
            campus
        ];
        csvData.push(row);
    });

    // Convert to CSV string
    const csvContent = csvData.map(row => 
        row.map(field => `"${String(field).replace(/"/g, '""')}"`)
           .join(',')
    ).join('\n');
    
    return {
        content: csvContent,
        filename: `monthly-inventory-report-${campus.toLowerCase().replace(/\s+/g, '-')}-${reportPeriod}.csv`
    };
};