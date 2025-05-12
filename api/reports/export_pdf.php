<?php
require_once '../../config/db_connection.php';
require_once '../../vendor/autoload.php'; // For TCPDF

use TCPDF as TCPDF;

// Check if report data was submitted
if (!isset($_POST['reportData']) || !isset($_POST['reportType']) || !isset($_POST['reportTitle']) || !isset($_POST['dateRange']) || !isset($_POST['columns'])) {
    die('Missing required parameters');
}

// Get the submitted data
$reportData = json_decode($_POST['reportData'], true);
$reportType = $_POST['reportType'];
$reportTitle = $_POST['reportTitle'];
$dateRange = $_POST['dateRange'];
$columns = json_decode($_POST['columns'], true);
$totals = isset($_POST['totals']) ? json_decode($_POST['totals'], true) : null;

// Process column labels before PDF generation - fix the Revenue (?) issue
foreach ($columns as $key => $column) {
    if (isset($column['label']) && strpos($column['label'], 'Revenue (') !== false) {
        $columns[$key]['label'] = 'Revenue (PHP)';
    }
}

// Create new PDF document with diskcache enabled to prevent blank pages
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);

// Set document information
$pdf->SetCreator('Gymaster');
$pdf->SetAuthor('Gymaster Admin');
$pdf->SetTitle($reportTitle);
$pdf->SetSubject('Report');
$pdf->SetKeywords('Gymaster, Report, ' . $reportType);

// Remove header and footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(15, 15, 15);

// Set auto page breaks - DISABLE auto page breaks to prevent blank pages
$pdf->SetAutoPageBreak(false, 0);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Define brand colors
$primaryColor = [47, 84, 150]; // RGB values for primary blue color
$accentColor = [69, 123, 157]; // RGB for secondary blue
$highlightColor = [237, 246, 249]; // Light blue for highlighting
$grayColor = [100, 100, 100]; // Dark gray for text
$lighterGray = [240, 240, 240]; // Light gray for alternating rows

// Add a page
$pdf->AddPage();

// Add stylish header with company name and colored background
$pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
$pdf->Rect(0, 0, $pdf->getPageWidth(), 30, 'F');
$pdf->SetY(8);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 24);
$pdf->Cell(0, 15, 'GYMASTER', 0, 1, 'C');

// Add some empty space
$pdf->SetY(35);
$pdf->SetTextColor($grayColor[0], $grayColor[1], $grayColor[2]);

// Report Header with decorative line
$pdf->SetDrawColor($accentColor[0], $accentColor[1], $accentColor[2]);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $pdf->GetY() - 2, 195, $pdf->GetY() - 2);
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0, 10, $reportTitle, 0, 1, 'C');
$pdf->SetFont('helvetica', 'I', 10);
$pdf->Cell(0, 5, 'Date Range: ' . $dateRange, 0, 1, 'C');

// Add date and time of generation
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 5, 'Generated on: ' . date('F j, Y \a\t g:i A'), 0, 1, 'R');
$pdf->SetDrawColor($accentColor[0], $accentColor[1], $accentColor[2]);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $pdf->GetY() + 2, 195, $pdf->GetY() + 2);
$pdf->Ln(8);

// Add summary section with improved styling
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Report Summary', 0, 1, 'L');

// Define summary boxes
$summaryBoxes = [];
if ($reportType === 'subscription') {
    $activeCount = 0;
    foreach ($reportData as $row) {
        if (isset($row['Status']) && $row['Status'] == 1) {
            $activeCount++;
        }
    }
    
    $totalRevenue = 0;
    foreach ($reportData as $row) {
        if (isset($row['Revenue'])) {
            $revenue = is_numeric($row['Revenue']) ? $row['Revenue'] : floatval(preg_replace('/[^0-9.]/', '', $row['Revenue']));
            $totalRevenue += $revenue;
        }
    }
    
    $summaryBoxes = [
        ['title' => 'TOTAL SUBSCRIPTIONS', 'value' => count($reportData)],
        ['title' => 'ACTIVE SUBSCRIPTIONS', 'value' => $activeCount],
        ['title' => 'TOTAL REVENUE', 'value' => 'PHP ' . number_format($totalRevenue, 2)]
    ];
} else {
    $totalRevenue = 0;
    foreach ($reportData as $row) {
        if (isset($row['Revenue'])) {
            $revenue = is_numeric($row['Revenue']) ? $row['Revenue'] : floatval(preg_replace('/[^0-9.]/', '', $row['Revenue']));
            $totalRevenue += $revenue;
        }
    }
    
    $summaryBoxes = [
        ['title' => 'TOTAL TRANSACTIONS', 'value' => count($reportData)],
        ['title' => 'TRANSACTIONS COUNT', 'value' => count($reportData)],
        ['title' => 'TOTAL REVENUE', 'value' => 'PHP ' . number_format($totalRevenue, 2)]
    ];
}

// Draw professional summary boxes with shadow effect but more compact
$boxWidth = 58;
$boxHeight = 25;
$x = 15;
$y = $pdf->GetY() + 2;

// Draw boxes with gradient background
foreach ($summaryBoxes as $box) {
    // Draw shadow
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Rect($x + 2, $y + 2, $boxWidth, $boxHeight, 'F');
    
    // Draw box
    $pdf->SetFillColor($highlightColor[0], $highlightColor[1], $highlightColor[2]);
    $pdf->Rect($x, $y, $boxWidth, $boxHeight, 'F');
    
    // Draw border
    $pdf->SetDrawColor($accentColor[0], $accentColor[1], $accentColor[2]);
    $pdf->Rect($x, $y, $boxWidth, $boxHeight, 'D');
    
    // Box title
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetXY($x, $y + 2);
    $pdf->Cell($boxWidth, 5, $box['title'], 0, 0, 'C');
    
    // Box value
    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetXY($x, $y + 8);
    $pdf->Cell($boxWidth, 15, $box['value'], 0, 0, 'C');
    
    $x += $boxWidth + 5;
}

// Reset position for table - make it more compact to avoid page breaks
$pdf->SetY($y + $boxHeight + 10); // Reduced spacing

// Table title with icon-like indicator
$pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
$pdf->SetFont('helvetica', 'B', 14);
$tableTitle = ($reportType === 'subscription' ? 'Subscription Details' : 'Transaction Details');
$pdf->Cell(0, 10, $tableTitle, 0, 1, 'L');

// Add decorative line under table title
$pdf->SetDrawColor($accentColor[0], $accentColor[1], $accentColor[2]);
$pdf->SetLineWidth(0.2);
$pdf->Line(15, $pdf->GetY() - 2, 100, $pdf->GetY() - 2);
$pdf->Ln(2);

// Create the data table with enhanced styling
$pdf->SetFont('helvetica', 'B', 9);

// Table header with gradient background
$pdf->SetFillColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
$pdf->SetTextColor(255, 255, 255);

// Table header
$columnWidths = [];
$totalWidth = 180; // Available width
$columnCount = count($columns);
$defaultWidth = $totalWidth / $columnCount;

// Adjust column widths based on content type
foreach ($columns as $index => $column) {
    if (isset($column['format']) && $column['format'] === 'status') {
        $columnWidths[$index] = $defaultWidth * 0.8;
    } elseif (isset($column['format']) && $column['format'] === 'currency') {
        $columnWidths[$index] = $defaultWidth * 1.2;
    } elseif (strpos(strtolower($column['label']), 'date') !== false) {
        $columnWidths[$index] = $defaultWidth * 1.2;
    } else {
        $columnWidths[$index] = $defaultWidth;
    }
}

// Normalize widths to match the total width
$sum = array_sum($columnWidths);
foreach ($columnWidths as $index => $width) {
    $columnWidths[$index] = ($width / $sum) * $totalWidth;
}

// Calculate if we need to limit rows per page based on data count
// For shorter reports, we won't need pagination
$maxRowsPerPage = 20; // Reduced from 25 to ensure we fit on one page
$rowsToShow = min(count($reportData), $maxRowsPerPage);

// Print header row with professional styling
foreach ($columns as $index => $column) {
    // Make sure revenue header is correctly formatted
    $headerLabel = $column['label'];
    // Print the header cell
    $pdf->Cell($columnWidths[$index], 6, $headerLabel, 1, 0, 'C', true); // Reduced row height further
}
$pdf->Ln();

// Print data rows with zebra striping and borders
$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('helvetica', '', 8);
$rowColor = false;

// Check if there is data
if (empty($reportData)) {
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell($totalWidth, 10, 'No data available for the selected filters', 1, 1, 'C', true);
} else {
    // Determine how many rows to show (limited subset for large datasets)
    $displayData = array_slice($reportData, 0, $rowsToShow);
    
    foreach ($displayData as $row) {
        // Set alternating row colors
        if ($rowColor) {
            $pdf->SetFillColor($highlightColor[0], $highlightColor[1], $highlightColor[2]);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }
        
        foreach ($columns as $index => $column) {
            $field = $column['field'];
            $value = isset($row[$field]) ? $row[$field] : '';
            $align = 'L'; // Default alignment
            
            // Check for revenue-related fields that need special formatting
            $isRevenueField = false;
            if (strpos(strtolower($field), 'revenue') !== false || 
                strpos(strtolower($column['label']), 'revenue') !== false ||
                (isset($column['format']) && $column['format'] === 'currency')) {
                $isRevenueField = true;
            }
            
            // Format special fields and set alignment
            if (isset($column['format'])) {
                if ($column['format'] === 'status' && isset($row['Status'])) {
                    $status = $row['Status'];
                    $value = ($status == 1) ? 'Active' : 'Inactive';
                    // Set text color based on status
                    if ($status == 1) {
                        $pdf->SetTextColor(46, 125, 50); // Green for active
                    } else {
                        $pdf->SetTextColor(211, 47, 47); // Red for inactive
                    }
                    $align = 'C';
                } elseif ($column['format'] === 'currency' || $isRevenueField) {
                    // Always ensure we format currency with "PHP" prefix
                    if (empty($value)) {
                        $value = 'PHP 0.00';
                    } elseif (!is_numeric($value)) {
                        // If it's already formatted as a string, remove any currency symbols
                        // and re-format with PHP prefix
                        $numericValue = floatval(preg_replace('/[^0-9.]/', '', $value));
                        $value = 'PHP ' . number_format($numericValue, 2);
                    } else {
                        // For numeric values, simply format with PHP prefix
                        $value = 'PHP ' . number_format($value, 2);
                    }
                    $align = 'R';
                    $pdf->SetTextColor(50, 50, 50);
                } else {
                    $pdf->SetTextColor(50, 50, 50);
                }
            } elseif ($isRevenueField) {
                // Handle revenue fields that may not have format set
                if (empty($value)) {
                    $value = 'PHP 0.00';
                } elseif (!is_numeric($value)) {
                    $numericValue = floatval(preg_replace('/[^0-9.]/', '', $value));
                    $value = 'PHP ' . number_format($numericValue, 2);
                } else {
                    $value = 'PHP ' . number_format($value, 2);
                }
                $align = 'R';
                $pdf->SetTextColor(50, 50, 50);
            } else {
                $pdf->SetTextColor(50, 50, 50);
            }
            
            // Output cell with fill and border
            $pdf->Cell($columnWidths[$index], 6, $value, 1, 0, $align, true); // Reduced row height
        }
        $pdf->Ln();
        $rowColor = !$rowColor; // Alternate row colors
        $pdf->SetTextColor(50, 50, 50); // Reset text color for next row
    }
    
    // If we have more data than we're showing, add an indicator
    if (count($reportData) > $rowsToShow) {
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell($totalWidth, 6, '... and ' . (count($reportData) - $rowsToShow) . ' more records (download full report for complete data)', 0, 1, 'C');
    }
}

// Add footer with totals for certain report types - more compact to save space
if ($reportType === 'subscription' || $reportType === 'revenue') {
    $pdf->Ln(2); // Reduced spacing
    
    // Create a box for the total
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetDrawColor($accentColor[0], $accentColor[1], $accentColor[2]);
    $pdf->SetLineWidth(0.2);
    
    // Set positioning
    $totalBoxWidth = 90; // Width of the total box
    $totalBoxX = $pdf->getPageWidth() - $pdf->getMargins()['right'] - $totalBoxWidth;
    
    // Draw the total box with border
    $pdf->Rect($totalBoxX, $pdf->GetY(), $totalBoxWidth, 10, 'FD');
    
    // Set font for total text
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
    
    // Add total label and value next to each other
    $labelWidth = 50; // Width for "Total Revenue:" text
    $pdf->SetXY($totalBoxX + 5, $pdf->GetY() + 1.5);
    $pdf->Cell($labelWidth, 8, 'Total Revenue:', 0, 0, 'R');
    
    // Use "PHP" as prefix instead of the problematic peso sign
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell($totalBoxWidth - $labelWidth - 5, 8, 'PHP ' . number_format($totalRevenue, 2), 0, 1, 'L');
}

// Add footer at fixed position
$pdf->SetY($pdf->getPageHeight() - 20);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(128, 128, 128);
$pdf->Cell(0, 10, 'Page 1', 0, 1, 'C'); // Just say "Page 1" since we're forcing one page

// Add company footer
$pdf->SetFont('helvetica', '', 8);
$pdf->Cell(0, 10, 'Gymaster Gym Management System - Confidential', 0, 0, 'C');

// Ensure only 1 page is output
$totalPages = $pdf->getNumPages();
if ($totalPages > 1) {
    for ($i = $totalPages; $i > 1; $i--) {
        $pdf->deletePage($i);
    }
}

// Generate filename
$filename = strtolower(str_replace(' ', '_', $reportTitle)) . '_' . date('Y-m-d') . '.pdf';

// Output PDF as download
$pdf->Output($filename, 'D');
exit;
?>