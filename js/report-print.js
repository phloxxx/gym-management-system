/**
 * Report Print Functionality
 * Creates a print-friendly version of reports that matches the currently displayed report
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to print button if it exists
    const printButton = document.getElementById('printReportBtn');
    if (printButton) {
        printButton.addEventListener('click', function() {
            printCurrentReport();
        });
    }
});

/**
 * Generates and displays a print-friendly version of the current report
 */
function printCurrentReport() {
    // Get the current report data
    const reportTitle = document.getElementById('reportTitle').textContent || 'Report';
    const dateRange = document.getElementById('reportDateRange').textContent || '';
    
    // Get the summary cards data
    const card1Title = document.getElementById('card1Title').textContent;
    const card1Value = document.getElementById('card1Value').textContent;
    const card2Title = document.getElementById('card2Title').textContent;
    const card2Value = document.getElementById('card2Value').textContent;
    const card3Title = document.getElementById('card3Title').textContent;
    const card3Value = document.getElementById('card3Value').textContent;
    
    // Get the table title
    const tableTitle = document.getElementById('tableTitle').textContent;
    
    // Get the table data
    const tableHeaders = [];
    const tableHeaderRow = document.getElementById('reportTableHead').querySelector('tr');
    if (tableHeaderRow) {
        const headerCells = tableHeaderRow.querySelectorAll('th');
        headerCells.forEach(cell => {
            tableHeaders.push(cell.textContent);
        });
    }
    
    // Get the table rows data
    const tableRows = [];
    const tableBodyRows = document.getElementById('reportTableBody').querySelectorAll('tr');
    tableBodyRows.forEach(row => {
        const rowData = [];
        const cells = row.querySelectorAll('td');
        cells.forEach(cell => {
            // For status cells, get the status text
            if (cell.querySelector('.text-green-800')) {
                rowData.push('Active');
            } else if (cell.querySelector('.text-red-800')) {
                rowData.push('Inactive');
            } else {
                rowData.push(cell.textContent);
            }
        });
        tableRows.push(rowData);
    });
    
    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'height=600,width=800');
    
    // Generate print-friendly HTML
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${reportTitle}</title>
            <meta charset="UTF-8">
            <style>
                /* Print-specific styles */
                @page {
                    size: portrait;
                    margin: 0.5in;
                }
                
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                }
                
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    border-bottom: 2px solid #2f5496;
                    padding-bottom: 10px;
                }
                
                .company-name {
                    font-size: 24px;
                    font-weight: bold;
                    color: #2f5496;
                    text-transform: uppercase;
                    margin: 0;
                }
                
                .report-title {
                    font-size: 18px;
                    margin: 10px 0;
                }
                
                .date-range {
                    font-style: italic;
                    font-size: 14px;
                    margin-bottom: 10px;
                }
                
                .summary-section {
                    display: flex;
                    justify-content: space-between;
                    margin: 20px 0;
                    flex-wrap: wrap;
                }
                
                .summary-box {
                    border: 1px solid #ddd;
                    background: #f9f9f9;
                    border-radius: 4px;
                    padding: 10px;
                    width: 30%;
                    text-align: center;
                    margin-bottom: 10px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                
                .summary-title {
                    font-size: 12px;
                    color: #666;
                    text-transform: uppercase;
                    margin: 0;
                }
                
                .summary-value {
                    font-size: 18px;
                    font-weight: bold;
                    color: #2f5496;
                    margin: 5px 0;
                }
                
                .table-section {
                    margin-top: 20px;
                }
                
                .table-title {
                    font-size: 16px;
                    font-weight: bold;
                    margin-bottom: 10px;
                    color: #2f5496;
                }
                
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                
                th {
                    background-color: #2f5496;
                    color: white;
                    text-align: left;
                    padding: 8px;
                    font-weight: bold;
                }
                
                td {
                    padding: 8px;
                    border-bottom: 1px solid #ddd;
                }
                
                tr:nth-child(even) {
                    background-color: #f2f2f2;
                }
                
                .active-status {
                    color: #046c4e;
                    font-weight: bold;
                }
                
                .inactive-status {
                    color: #dc2626;
                    font-weight: bold;
                }
                
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    font-size: 12px;
                    color: #666;
                    border-top: 1px solid #ddd;
                    padding-top: 10px;
                }
                
                @media print {
                    .no-print {
                        display: none;
                    }
                    
                    button {
                        display: none;
                    }
                    
                    /* Force background colors to print */
                    th {
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    
                    tr:nth-child(even) {
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                    
                    .summary-box {
                        -webkit-print-color-adjust: exact;
                        print-color-adjust: exact;
                    }
                }
            </style>
            <script>
                // Function to handle print dialog events
                window.onload = function() {
                    // Small delay to ensure document is fully loaded
                    setTimeout(function() {
                        // Store reference to this window for access from event handlers
                        const printWindow = window;
                        
                        // Create a flag to track whether printing was completed
                        let printInitiated = false;
                        
                        // Listen for beforeprint event (when dialog opens)
                        printWindow.addEventListener('beforeprint', function() {
                            printInitiated = true;
                        });
                        
                        // Listen for afterprint event (after print completes or is cancelled)
                        printWindow.addEventListener('afterprint', function() {
                            printWindow.close();
                        });
                        
                        // Trigger print dialog
                        printWindow.print();
                        
                        // Set a longer timeout to check if print dialog was cancelled without triggering events
                        // This fallback ensures the window closes even if the print events don't fire properly
                        setTimeout(function() {
                            if (!printInitiated) {
                                // Print dialog was likely cancelled without triggering print events
                                printWindow.close();
                            }
                        }, 500);
                        
                        // Final fallback to ensure window always closes
                        setTimeout(function() {
                            printWindow.close();
                        }, 2000);
                    }, 300);
                };
            </script>
        </head>
        <body>
            <!-- Header Section -->
            <div class="header">
                <h1 class="company-name">GYMASTER</h1>
                <h2 class="report-title">${reportTitle}</h2>
                <p class="date-range">${dateRange}</p>
                <p>Generated on: ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</p>
            </div>
            
            <!-- Summary Section -->
            <div class="summary-section">
                <div class="summary-box">
                    <p class="summary-title">${card1Title}</p>
                    <p class="summary-value">${card1Value}</p>
                </div>
                <div class="summary-box">
                    <p class="summary-title">${card2Title}</p>
                    <p class="summary-value">${card2Value}</p>
                </div>
                <div class="summary-box">
                    <p class="summary-title">${card3Title}</p>
                    <p class="summary-value">${card3Value}</p>
                </div>
            </div>
            
            <!-- Table Section -->
            <div class="table-section">
                <h3 class="table-title">${tableTitle}</h3>
                <table>
                    <thead>
                        <tr>
                            ${tableHeaders.map(header => `<th>${header}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${tableRows.length > 0 ? 
                            tableRows.map(row => `
                                <tr>
                                    ${row.map(cell => {
                                        if (cell === 'Active') {
                                            return '<td class="active-status">Active</td>';
                                        } else if (cell === 'Inactive') {
                                            return '<td class="inactive-status">Inactive</td>';
                                        } else {
                                            return `<td>${cell}</td>`;
                                        }
                                    }).join('')}
                                </tr>
                            `).join('') : 
                            `<tr><td colspan="${tableHeaders.length}" style="text-align: center;">No data available</td></tr>`
                        }
                    </tbody>
                </table>
            </div>
            
            <!-- Footer Section -->
            <div class="footer">
                <p>Gymaster Gym Management System - Confidential</p>
                <p>Page 1</p>
            </div>
            
            <!-- Close Preview Button -->
            <div class="no-print" style="text-align: center; margin-top: 20px;">
                <button onclick="window.close();" 
                        style="padding: 10px 20px; background-color: #2f5496; color: white; 
                               border: none; border-radius: 4px; cursor: pointer;">
                    Close Preview
                </button>
            </div>
        </body>
        </html>
    `);
    
    // Finalize the document and trigger print dialog
    printWindow.document.close();
    printWindow.focus();
}