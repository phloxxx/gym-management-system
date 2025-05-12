/**
 * Report Print Functionality
 * Creates a print-friendly version of reports that matches the currently displayed report
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to print button if it exists
    const printButton = document.getElementById('printReportBtn');
    if (printButton) {
        printButton.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent any default behavior
            printCurrentReport();
        });
    }
});

/**
 * Generates and displays a print-friendly version of the current report
 * using an in-page modal approach instead of opening a new tab
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
    
    // Create modal overlay for print preview
    const modalOverlay = document.createElement('div');
    modalOverlay.id = 'printPreviewModal';
    modalOverlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    `;
    
    // Create modal content container
    const modalContent = document.createElement('div');
    modalContent.style.cssText = `
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        width: 90%;
        max-width: 1000px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    `;
    
    // Create modal header
    const modalHeader = document.createElement('div');
    modalHeader.style.cssText = `
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background-color: #081738;
        color: white;
        border-bottom: 1px solid #ddd;
    `;
    
    // Add title to modal header
    const modalTitle = document.createElement('h3');
    modalTitle.textContent = 'Print Preview: ' + reportTitle;
    modalTitle.style.cssText = `
        margin: 0;
        font-size: 18px;
    `;
    
    // Add close button to modal header
    const closeButton = document.createElement('button');
    closeButton.innerHTML = '&times;';
    closeButton.style.cssText = `
        background: none;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    `;
    closeButton.onclick = function() {
        document.body.removeChild(modalOverlay);
    };
    
    // Append title and close button to modal header
    modalHeader.appendChild(modalTitle);
    modalHeader.appendChild(closeButton);
    
    // Create modal body with scrollable content
    const modalBody = document.createElement('div');
    modalBody.style.cssText = `
        padding: 20px;
        overflow-y: auto;
        flex-grow: 1;
    `;
    
    // Fill modal body with print content
    modalBody.innerHTML = `
        <!-- Header Section -->
        <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2f5496; padding-bottom: 10px;">
            <h1 style="font-size: 24px; font-weight: bold; color: #2f5496; text-transform: uppercase; margin: 0;">GYMASTER</h1>
            <h2 style="font-size: 18px; margin: 10px 0;">${reportTitle}</h2>
            <p style="font-style: italic; font-size: 14px; margin-bottom: 10px;">${dateRange}</p>
            <p>Generated on: ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</p>
        </div>
        
        <!-- Summary Section -->
        <div style="display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap;">
            <div style="border: 1px solid #ddd; background: #f9f9f9; border-radius: 4px; padding: 10px; width: 30%; text-align: center; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <p style="font-size: 12px; color: #666; text-transform: uppercase; margin: 0;">${card1Title}</p>
                <p style="font-size: 18px; font-weight: bold; color: #2f5496; margin: 5px 0;">${card1Value}</p>
            </div>
            <div style="border: 1px solid #ddd; background: #f9f9f9; border-radius: 4px; padding: 10px; width: 30%; text-align: center; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <p style="font-size: 12px; color: #666; text-transform: uppercase; margin: 0;">${card2Title}</p>
                <p style="font-size: 18px; font-weight: bold; color: #2f5496; margin: 5px 0;">${card2Value}</p>
            </div>
            <div style="border: 1px solid #ddd; background: #f9f9f9; border-radius: 4px; padding: 10px; width: 30%; text-align: center; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <p style="font-size: 12px; color: #666; text-transform: uppercase; margin: 0;">${card3Title}</p>
                <p style="font-size: 18px; font-weight: bold; color: #2f5496; margin: 5px 0;">${card3Value}</p>
            </div>
        </div>
        
        <!-- Table Section -->
        <div style="margin-top: 20px;">
            <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 10px; color: #2f5496;">${tableTitle}</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                    <thead>
                        <tr>
                            ${tableHeaders.map(header => `<th style="background-color: #2f5496; color: white; text-align: left; padding: 8px; font-weight: bold;">${header}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${tableRows.length > 0 ? 
                            tableRows.map((row, rowIndex) => `
                                <tr style="background-color: ${rowIndex % 2 === 0 ? 'white' : '#f2f2f2'};">
                                    ${row.map(cell => {
                                        if (cell === 'Active') {
                                            return '<td style="padding: 8px; border-bottom: 1px solid #ddd; color: #046c4e; font-weight: bold;">Active</td>';
                                        } else if (cell === 'Inactive') {
                                            return '<td style="padding: 8px; border-bottom: 1px solid #ddd; color: #dc2626; font-weight: bold;">Inactive</td>';
                                        } else {
                                            return `<td style="padding: 8px; border-bottom: 1px solid #ddd;">${cell}</td>`;
                                        }
                                    }).join('')}
                                </tr>
                            `).join('') : 
                            `<tr><td colspan="${tableHeaders.length}" style="text-align: center; padding: 8px; border-bottom: 1px solid #ddd;">No data available</td></tr>`
                        }
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Footer Section -->
        <div style="text-align: center; margin-top: 30px; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 10px;">
            <p>Gymaster Gym Management System - Confidential</p>
            <p>Page 1</p>
        </div>
    `;
    
    // Create modal footer with action buttons
    const modalFooter = document.createElement('div');
    modalFooter.style.cssText = `
        padding: 15px 20px;
        background-color: #f5f5f5;
        border-top: 1px solid #ddd;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    `;
    
    // Add print button
    const printButton = document.createElement('button');
    printButton.textContent = 'Print Report';
    printButton.style.cssText = `
        background-color: #081738;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 8px 16px;
        cursor: pointer;
        font-weight: 500;
    `;
    printButton.onclick = function() {
        // Create a hidden iframe for printing
        let printFrame = document.createElement('iframe');
        printFrame.name = 'printFrame';
        printFrame.style.position = 'fixed';
        printFrame.style.right = '0';
        printFrame.style.bottom = '0';
        printFrame.style.width = '0';
        printFrame.style.height = '0';
        printFrame.style.border = '0';
        document.body.appendChild(printFrame);
        
        // Create print-friendly content
        let printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>${reportTitle}</title>
                <meta charset="UTF-8">
                <style>
                    @page {
                        margin: 0.5in;
                    }
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        color: #333;
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
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 20px 0;
                    }
                    th {
                        background-color: #2f5496;
                        color: white;
                        padding: 8px;
                        text-align: left;
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
                </style>
            </head>
            <body>
                ${modalBody.innerHTML}
            </body>
            </html>
        `;
        
        // Write to the iframe and print it
        printFrame.contentWindow.document.open();
        printFrame.contentWindow.document.write(printContent);
        printFrame.contentWindow.document.close();
        
        // Wait for content to load before printing
        setTimeout(function() {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
            
            // Remove the frame after printing (or after 2 seconds)
            setTimeout(function() {
                document.body.removeChild(printFrame);
            }, 2000);
        }, 500);
    };
    
    // Add cancel button
    const cancelButton = document.createElement('button');
    cancelButton.textContent = 'Cancel';
    cancelButton.style.cssText = `
        background-color: #f5f5f5;
        color: #333;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 8px 16px;
        cursor: pointer;
    `;
    cancelButton.onclick = function() {
        document.body.removeChild(modalOverlay);
    };
    
    // Append buttons to modal footer
    modalFooter.appendChild(cancelButton);
    modalFooter.appendChild(printButton);
    
    // Assemble all modal components
    modalContent.appendChild(modalHeader);
    modalContent.appendChild(modalBody);
    modalContent.appendChild(modalFooter);
    modalOverlay.appendChild(modalContent);
    
    // Add event listener to close modal when clicking outside
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            document.body.removeChild(modalOverlay);
        }
    });
    
    // Add keyboard event listener to close modal with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.body.contains(modalOverlay)) {
            document.body.removeChild(modalOverlay);
        }
    });
    
    // Add the modal to the page
    document.body.appendChild(modalOverlay);
}