/**
 * Subscription renewal helper
 * Contains utilities to handle subscription renewals more effectively
 */

/**
 * Process a subscription renewal for a member
 * 
 * @param {Object} renewalData The renewal data: { memberId, subscriptionId, paymentId, startDate, previousSubId }
 * @returns {Promise} Promise that resolves to the server response
 */
function processRenewal(renewalData) {
    console.log('Processing renewal with data:', renewalData);
    
    // Validate required fields
    const requiredFields = ['memberId', 'subscriptionId', 'paymentId', 'startDate'];
    for (const field of requiredFields) {
        if (!renewalData[field]) {
            return Promise.reject(`Missing required field: ${field}`);
        }
    }
    
    // Get subscription duration to calculate end date
    return getSubscriptionDuration(renewalData.subscriptionId)
        .then(duration => {
            // Calculate end date
            const startDate = new Date(renewalData.startDate);
            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + parseInt(duration));
            
            // Format date as YYYY-MM-DD
            const endDateFormatted = endDate.toISOString().split('T')[0];
            
            // Create the full transaction data for a renewal
            const transactionData = {
                memberId: parseInt(renewalData.memberId),
                subscriptionId: parseInt(renewalData.subscriptionId),
                paymentId: parseInt(renewalData.paymentId),
                startDate: renewalData.startDate,
                endDate: endDateFormatted,
                isRenewal: true,
                previousSubId: renewalData.previousSubId ? parseInt(renewalData.previousSubId) : null,
                requestId: 'renewal_' + Date.now()
            };
            
            console.log('Submitting renewal transaction:', transactionData);
            
            // Submit to server
            return fetch('../../functions/create-transaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Cache-Control': 'no-cache'
                },
                body: JSON.stringify(transactionData)
            })
            .then(response => {
                console.log('Renewal server response status:', response.status);
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error('Server error: ' + (data.message || response.statusText));
                    });
                }
                return response.json();
            });
        });
}

/**
 * Get subscription duration from the server
 * 
 * @param {number} subscriptionId The subscription ID
 * @returns {Promise} Promise that resolves to the duration in days
 */
function getSubscriptionDuration(subscriptionId) {
    return fetch(`../../api/subscriptions/get.php?id=${subscriptionId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch subscription details');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success' && data.subscription && data.subscription.DURATION) {
                return parseInt(data.subscription.DURATION);
            }
            throw new Error('Invalid subscription data received');
        });
}

/**
 * Updates the UI with the newly renewed subscription
 * 
 * @param {Object} transaction Transaction data from server
 * @returns {boolean} True if the UI was updated successfully
 */
function updateUIWithRenewal(transaction) {
    console.log('Updating UI with renewal transaction:', transaction);
    
    // Try multiple methods to update the UI
    
    // Method 1: Update subscription table if it exists
    const subscriptionTable = document.getElementById('subscriptionTable') || 
                             document.querySelector('table.subscription-table') ||
                             document.querySelector('table.subscriptions-table');
                             
    if (subscriptionTable) {
        try {
            const tbody = subscriptionTable.querySelector('tbody') || subscriptionTable;
            
            // Create a new row for the renewed subscription
            const newRow = document.createElement('tr');
            
            // Format dates for display
            const startDate = new Date(transaction.startDate).toLocaleDateString();
            const endDate = new Date(transaction.endDate).toLocaleDateString();
            const transactionDate = new Date(transaction.transactionDate || Date.now()).toLocaleDateString();
            
            // Add some identifying classes to the row
            newRow.className = 'subscription-row bg-green-50 hover:bg-green-100 transition-colors';
            newRow.setAttribute('data-member-id', transaction.memberId);
            newRow.setAttribute('data-subscription-id', transaction.subscriptionId);
            
            // Set the row content
            newRow.innerHTML = `
                <td class="px-4 py-2">${transaction.memberName || 'N/A'}</td>
                <td class="px-4 py-2">${transaction.subscriptionName || 'N/A'}</td>
                <td class="px-4 py-2">${startDate}</td>
                <td class="px-4 py-2">${endDate}</td>
                <td class="px-4 py-2">
                    <span class="px-2 py-1 rounded-full text-xs font-medium text-white bg-green-500">
                        Active
                    </span>
                </td>
                <td class="px-4 py-2">${transactionDate}</td>
                <td class="px-4 py-2">
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Renewed
                    </span>
                </td>
            `;
            
            // Add the row to the beginning of the table
            if (tbody.firstChild) {
                tbody.insertBefore(newRow, tbody.firstChild);
            } else {
                tbody.appendChild(newRow);
            }
            
            // Highlight the new row
            newRow.classList.add('animate-pulse', 'bg-green-100');
            setTimeout(() => {
                newRow.classList.remove('animate-pulse', 'bg-green-100');
            }, 3000);
            
            console.log('Successfully updated subscription table');
            return true;
        } catch (err) {
            console.error('Error updating subscription table:', err);
        }
    }
    
    // Method 2: Update transaction table if it exists
    const transactionTable = document.getElementById('transactionTable') || 
                            document.querySelector('table.transaction-table');
    
    if (transactionTable) {
        try {
            updateTransactionTable(transactionTable, transaction);
            return true;
        } catch (err) {
            console.error('Error updating transaction table:', err);
        }
    }
    
    // Method 3: Look for any table that might be right
    const tables = document.querySelectorAll('table');
    for (const table of tables) {
        // Skip tables we've already tried
        if (table === subscriptionTable || table === transactionTable) continue;
        
        // Check if this table has headers that match what we're looking for
        const headers = table.querySelectorAll('th');
        const headerText = Array.from(headers).map(th => th.textContent.toLowerCase());
        
        if (headerText.includes('member') || headerText.includes('subscription') || 
            headerText.includes('date') || headerText.includes('payment')) {
            try {
                updateTransactionTable(table, transaction);
                return true;
            } catch (err) {
                console.error('Error updating generic table:', err);
                continue;
            }
        }
    }
    
    console.warn('Could not find a suitable table to update, will reload page');
    setTimeout(() => window.location.reload(), 2000);
    return false;
}

/**
 * Update a transaction table with renewal data
 * 
 * @param {HTMLElement} table The table to update
 * @param {Object} transaction The transaction data
 */
function updateTransactionTable(table, transaction) {
    const tbody = table.querySelector('tbody') || table;
    
    // Create a new row
    const newRow = document.createElement('tr');
    
    // Format dates for display
    const startDate = new Date(transaction.startDate).toLocaleDateString();
    const endDate = new Date(transaction.endDate).toLocaleDateString();
    const transactionDate = new Date(transaction.transactionDate || Date.now()).toLocaleDateString();
    
    // Add row content - adjust based on the most common transaction table structure
    newRow.innerHTML = `
        <td class="px-3 py-2">${transaction.memberName || 'N/A'}</td>
        <td class="px-3 py-2">${transaction.subscriptionName || 'N/A'}</td>
        <td class="px-3 py-2">${startDate}</td>
        <td class="px-3 py-2">${endDate}</td>
        <td class="px-3 py-2">${transaction.paymentMethod || 'N/A'}</td>
        <td class="px-3 py-2">${transactionDate}</td>
        <td class="px-3 py-2">
            <span class="px-2 py-1 rounded-full text-xs text-white bg-blue-500">Renewed</span>
        </td>
    `;
    
    // Add the row to the beginning of the table
    if (tbody.firstChild) {
        tbody.insertBefore(newRow, tbody.firstChild);
    } else {
        tbody.appendChild(newRow);
    }
    
    // Highlight the new row temporarily
    newRow.classList.add('animate-pulse', 'bg-blue-50');
    setTimeout(() => {
        newRow.classList.remove('animate-pulse', 'bg-blue-50');
    }, 3000);
}

/**
 * Shows a toast notification for renewal results
 * 
 * @param {string} message Message to display
 * @param {boolean} isSuccess Whether this is a success message
 */
function showRenewalToast(message, isSuccess) {
    if (typeof window.showToast === 'function') {
        window.showToast(message, isSuccess);
    } else {
        // Create a simple toast if the showToast function doesn't exist
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-md shadow-lg z-50 ${
            isSuccess ? 'bg-green-500' : 'bg-red-500'
        } text-white`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        // Remove the toast after 5 seconds
        setTimeout(() => {
            toast.classList.add('opacity-0', 'transition-opacity');
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 5000);
    }
}

// Export functions to window object
window.processRenewal = processRenewal;
window.updateUIWithRenewal = updateUIWithRenewal;
window.showRenewalToast = showRenewalToast;
