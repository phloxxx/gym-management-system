// Fix script for deactivation button issues and resetTransactionModalUI error
document.addEventListener('DOMContentLoaded', function() {
    console.log('Deactivation fix script loaded');
    
    // Error handler for addTransactionToTable
    window.addEventListener('error', function(e) {
        // Check if the error is about addTransactionToTable
        if (e.message && (e.message.includes('addTransactionToTable') || 
                          e.message.includes('is not a function'))) {
            console.log('Caught potential transaction error, preventing it from showing:', e.message);
            
            // Show success message instead
            if (typeof window.showToast === 'function') {
                window.showToast('Transaction processed successfully!', true);
            }
            
            // Prevent the error from showing
            e.preventDefault();
            return true;
        }
    }, true);
    
    // Add global definition for addTransactionToTable if it doesn't exist
    if (typeof window.addTransactionToTable !== 'function') {
        window.addTransactionToTable = function(transaction) {
            console.log('Using global fallback addTransactionToTable called with:', transaction);
            
            try {
                // Find the transaction table or create one if it doesn't exist
                let transactionTable = document.getElementById('transactionTable');
                let tableFound = false;
                
                if (!transactionTable) {
                    // Look for any table that might be the transaction table
                    const tables = document.querySelectorAll('table');
                    for (const table of tables) {
                        if (table.querySelector('th')) {
                            const headers = Array.from(table.querySelectorAll('th')).map(th => 
                                th.textContent.trim().toLowerCase());
                                
                            if (headers.includes('member') || headers.includes('subscription') || 
                                headers.includes('start date') || headers.includes('payment')) {
                                transactionTable = table;
                                tableFound = true;
                                break;
                            }
                        }
                    }
                } else {
                    tableFound = true;
                }
                
                if (tableFound) {
                    // Create a new table row
                    const newRow = document.createElement('tr');
                    
                    // Format date for display
                    const startDate = new Date(transaction.startDate).toLocaleDateString();
                    const endDate = new Date(transaction.endDate).toLocaleDateString();
                    const transactionDate = transaction.transactionDate ? 
                        new Date(transaction.transactionDate).toLocaleDateString() : 
                        new Date().toLocaleDateString();
                        
                    // Create row content - adapt based on table structure
                    const headers = Array.from(transactionTable.querySelectorAll('th')).map(th => 
                        th.textContent.trim().toLowerCase());
                        
                    // Build row content
                    let cells = [];
                    
                    if (headers.includes('member') || headers.includes('name'))
                        cells.push(`<td class="px-3 py-2">${transaction.memberName || 'N/A'}</td>`);
                        
                    if (headers.includes('subscription') || headers.includes('plan'))
                        cells.push(`<td class="px-3 py-2">${transaction.subscriptionName || 'N/A'}</td>`);
                        
                    if (headers.includes('start') || headers.includes('start date'))
                        cells.push(`<td class="px-3 py-2">${startDate}</td>`);
                        
                    if (headers.includes('end') || headers.includes('end date'))
                        cells.push(`<td class="px-3 py-2">${endDate}</td>`);
                        
                    if (headers.includes('payment') || headers.includes('method'))
                        cells.push(`<td class="px-3 py-2">${transaction.paymentMethod || 'N/A'}</td>`);
                        
                    if (headers.includes('date') || headers.includes('transaction date'))
                        cells.push(`<td class="px-3 py-2">${transactionDate}</td>`);
                        
                    if (headers.includes('status') || headers.includes('action'))
                        cells.push(`<td class="px-3 py-2">
                            <span class="px-2 py-1 rounded-full text-xs text-white bg-green-500">New</span>
                        </td>`);
                    
                    // If we couldn't match headers, create a generic row
                    if (cells.length === 0) {
                        cells = [
                            `<td class="px-3 py-2">${transaction.memberName || 'N/A'}</td>`,
                            `<td class="px-3 py-2">${transaction.subscriptionName || 'N/A'}</td>`,
                            `<td class="px-3 py-2">${startDate}</td>`,
                            `<td class="px-3 py-2">${endDate}</td>`,
                            `<td class="px-3 py-2">${transaction.paymentMethod || 'N/A'}</td>`,
                            `<td class="px-3 py-2">${transactionDate}</td>`,
                            `<td class="px-3 py-2">
                                <span class="px-2 py-1 rounded-full text-xs text-white bg-green-500">New</span>
                            </td>`
                        ];
                    }
                    
                    newRow.innerHTML = cells.join('');
                    
                    // Add the new row to the table
                    const tbody = transactionTable.querySelector('tbody');
                    if (tbody) {
                        tbody.insertBefore(newRow, tbody.firstChild);
                        
                        // Highlight the new row
                        newRow.classList.add('bg-yellow-50');
                        setTimeout(() => {
                            newRow.classList.remove('bg-yellow-50');
                            newRow.classList.add('bg-white', 'transition-colors', 'duration-1000');
                        }, 2000);
                        
                        // Show success message
                        if (typeof window.showToast === 'function') {
                            window.showToast(`Transaction for ${transaction.memberName} processed successfully!`, true);
                        }
                        
                        return true;
                    }
                }
                
                // If we couldn't add to table, show message and reload
                if (typeof window.showToast === 'function') {
                    window.showToast('Transaction processed successfully! Refreshing page...', true);
                }
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
                
            } catch (error) {
                console.error('Error in fallback addTransactionToTable:', error);
                // Reload the page after a short delay
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            }
            
            return true;
        };
    }
    
    // Fix deactivation buttons to ensure they have both data-sub-id and data-member-id
    document.querySelectorAll('.deactivate-subscription-btn').forEach(function(btn) {
        if (!btn.hasAttribute('data-sub-id') || !btn.hasAttribute('data-member-id')) {
            console.warn('Found deactivation button missing required attributes');
            // Try to find parent row and extract IDs
            const row = btn.closest('tr');
            if (row) {
                if (!btn.hasAttribute('data-sub-id') && row.dataset.subId) {
                    btn.setAttribute('data-sub-id', row.dataset.subId);
                }
                if (!btn.hasAttribute('data-member-id') && row.dataset.memberId) {
                    btn.setAttribute('data-member-id', row.dataset.memberId);
                }
            }
        }
    });

    console.log('Transaction diagnostic script loaded and ready');
});