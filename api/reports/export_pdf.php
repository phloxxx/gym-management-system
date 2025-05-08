<?php
require_once '../../config/db_connection.php';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="report_data.csv"');

// Create output stream for CSV
$output = fopen('php://output', 'w');

// Add UTF-8 BOM to ensure proper encoding in Excel
fputs($output, "\xEF\xBB\xBF");

// Check if report data was submitted
if (!isset($_POST['reportData']) || !isset($_POST['reportType']) || !isset($_POST['columns'])) {
    // If no data, output empty CSV with headers
    fputcsv($output, ['No data available']);
    fclose($output);
    exit;
}

// Get the submitted data
$reportData = json_decode($_POST['reportData'], true);
$reportType = $_POST['reportType'];
$columns = json_decode($_POST['columns'], true);

// Process column headers before creating CSV - fix the Revenue (?) issue
foreach ($columns as $key => $column) {
    if (isset($column['label']) && strpos($column['label'], 'Revenue (') !== false) {
        $columns[$key]['label'] = 'Revenue (PHP)';
    }
    if (isset($column['label']) && strpos($column['label'], 'Amount (') !== false) {
        $columns[$key]['label'] = 'Amount (PHP)';
    }
}

// Prepare the header row with column titles
$headers = [];
foreach ($columns as $column) {
    $headers[] = $column['label'];
}

// Output the header row
fputcsv($output, $headers);

// Process and output data rows
foreach ($reportData as $row) {
    $rowData = [];
    
    foreach ($columns as $column) {
        $field = $column['field'];
        $value = isset($row[$field]) ? $row[$field] : '';
        
        // Check if this is a revenue field - this is causing the alignment issue
        $isRevenueField = false;
        if (isset($column['format']) && $column['format'] === 'currency') {
            $isRevenueField = true;
        } elseif (strpos(strtolower($field), 'revenue') !== false || 
                 strpos(strtolower($column['label']), 'revenue') !== false) {
            $isRevenueField = true;
        }
        
        // Format special fields
        if (isset($column['format'])) {
            if ($column['format'] === 'status' && isset($row['Status'])) {
                $value = ($row['Status'] == 1) ? 'Active' : 'Inactive';
            } elseif ($column['format'] === 'currency') {
                // For currency fields, extract only the numeric portion
                if (!is_numeric($value)) {
                    // Remove currency symbols and commas
                    $value = preg_replace('/[^\d.]/', '', $value);
                }
                // Don't use number_format here - it adds commas which break CSV
                // Instead, ensure it's just a plain number
                $value = floatval($value);
            }
        } 
        // Also handle revenue fields without explicit format
        elseif ($isRevenueField) {
            if (!is_numeric($value)) {
                // Remove currency symbols and commas
                $value = preg_replace('/[^\d.]/', '', $value);
            }
            // Ensure it's a simple number
            $value = floatval($value);
        }
        
        $rowData[] = $value;
    }
    
    // Output this data row
    fputcsv($output, $rowData);
}

// Close the output stream
fclose($output);
exit;
?>