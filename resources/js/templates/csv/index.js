/**
 * Main CSV Templates Export Module
 * Central point for importing all CSV template functions
 */

// Import all CSV template modules
export { generateMonthlyInventoryReportCSV } from './MonthlyInventoryReport.js';

/**
 * Common CSV utility functions
 */

/**
 * Escape CSV field content to handle commas, quotes, and newlines
 * @param {string} field - The field content to escape
 * @returns {string} - Escaped field content
 */
export const escapeCSVField = (field) => {
    const stringField = String(field);
    if (stringField.includes(',') || stringField.includes('"') || stringField.includes('\n')) {
        return `"${stringField.replace(/"/g, '""')}"`;
    }
    return stringField;
};

/**
 * Format date for CSV export
 * @param {string|Date} date - Date to format
 * @returns {string} - Formatted date string
 */
export const formatDateForCSV = (date) => {
    if (!date) return '-';
    const dateObj = new Date(date);
    return dateObj.toLocaleDateString('en-US', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
};

/**
 * Create and download CSV file
 * @param {string} csvContent - The CSV content
 * @param {string} filename - The filename for download
 */
export const downloadCSVFile = (csvContent, filename) => {
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
};