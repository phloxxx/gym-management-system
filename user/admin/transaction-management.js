// Function to highlight error fields with a red border and message
function highlightError(fieldContainer, message) {
    // Add red border to the input
    const input = fieldContainer.querySelector('input, select');
    if (input) {
        input.classList.add('border-red-500', 'error-border');
        input.classList.remove('border-gray-300');
    }
    
    // Add error message below the field
    const errorMessage = document.createElement('p');
    errorMessage.className = 'text-xs text-red-600 mt-1 error-message';
    errorMessage.textContent = message;
    fieldContainer.appendChild(errorMessage);
}

// Function to handle the transaction submission with proper error handling
function handleTransactionSubmission() {
    // This handler has been moved to manage-transaction.php directly
    // Leaving this file for reference and helper functions
    console.log("Using transaction handlers from main file instead");
}

// Initialize when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Disabled since we're using the handler in the main file
    // handleTransactionSubmission();
    
    // Make the end date field read-only
    const endDateField = document.getElementById('endDate');
    if (endDateField) {
        endDateField.readOnly = true;
        endDateField.classList.add('bg-gray-100', 'cursor-not-allowed');
        endDateField.setAttribute('tabindex', '-1');
        
        // Add a helper message near the field
        const endDateContainer = endDateField.closest('.relative');
        if (endDateContainer) {
            const helperText = document.createElement('div');
            helperText.className = 'text-xs text-gray-500 mt-1';
            helperText.textContent = 'Auto-calculated based on subscription duration';
            endDateContainer.appendChild(helperText);
        }
    }
    
    // Add event listener to subscription dropdown to update end date automatically
    const subscriptionDropdown = document.getElementById('subscriptionId');
    const startDateField = document.getElementById('startDate');
    
    // Function to calculate end date based on subscription duration
    function updateEndDate() {
        // Get selected subscription
        const subscriptionId = subscriptionDropdown.value;
        if (!subscriptionId || subscriptionId === '') return;
        
        // Get subscription duration from the data attribute or make an AJAX request
        getSubscriptionDuration(subscriptionId, function(duration) {
            // Calculate end date based on start date and duration
            const startDate = new Date(startDateField.value);
            if (isNaN(startDate.getTime())) return;
            
            // Add duration days to start date
            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + parseInt(duration));
            
            // Format date as YYYY-MM-DD
            const endDateFormatted = endDate.toISOString().split('T')[0];
            
            // Update end date field
            if (endDateField) {
                endDateField.value = endDateFormatted;
            }
        });
    }
    
    // Function to get subscription duration
    function getSubscriptionDuration(subscriptionId, callback) {
        // Check if we have the duration stored in a data attribute
        if (subscriptionDropdown.querySelector(`option[value="${subscriptionId}"]`)) {
            const duration = subscriptionDropdown.querySelector(`option[value="${subscriptionId}"]`).dataset.duration;
            if (duration) {
                callback(duration);
                return;
            }
        }
        
        // Otherwise make AJAX request to get subscription details
        fetch(`../../api/subscriptions/get.php?id=${subscriptionId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.subscription && data.subscription.DURATION) {
                    callback(data.subscription.DURATION);
                } else {
                    console.error('Failed to get subscription duration', data);
                    callback(30); // Default to 30 days if error
                }
            })
            .catch(error => {
                console.error('Error fetching subscription data', error);
                callback(30); // Default to 30 days if error
            });
    }
    
    // Update end date when subscription or start date changes
    if (subscriptionDropdown) {
        subscriptionDropdown.addEventListener('change', updateEndDate);
    }
    
    if (startDateField) {
        startDateField.addEventListener('change', updateEndDate);
    }
});

// Helper functions for formatting and calculations
function formatDisplayDate(date) {
    if (!(date instanceof Date) || isNaN(date)) {
        return '-';
    }
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long', 
        day: 'numeric'
    });
}

function calculateDaysLeft(endDateStr) {
    const today = new Date();
    const endDate = new Date(endDateStr);
    const diffTime = endDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
}

function getInitials(name) {
    return name.split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase();
}

/**
 * Transaction Management JavaScript
 * Handles validation of transaction form inputs, especially preventing past dates
 * and preventing overlapping subscription timeframes
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize date validation
    initDateValidation();
    
    // Add form submit validation
    initFormValidation();
});

/**
 * Initialize date validation for the start date input
 */
function initDateValidation() {
    const startDateInput = document.getElementById('startDateInput');
    const dateErrorMessage = document.getElementById('dateErrorMessage');
    const dateError = document.getElementById('dateError');
    
    if (!startDateInput) return;
    
    // Set min date to today to prevent selecting past dates in the datepicker
    const today = new Date();
    const formattedDate = today.toISOString().split('T')[0];
    startDateInput.min = formattedDate;
    
    // Default to today
    startDateInput.value = formattedDate;
    
    // Add change event listener to validate manually entered dates
    startDateInput.addEventListener('change', function() {
        validateStartDate();
        
        // Check for overlapping subscription dates when member is selected and dates change
        const selectedMemberId = document.getElementById('selectedMemberId')?.value;
        if (selectedMemberId && startDateInput.value && document.getElementById('endDateInput').value) {
            checkForOverlappingSubscriptions(selectedMemberId, startDateInput.value, document.getElementById('endDateInput').value);
        }
    });
    
    // Add input event listener for real-time validation
    startDateInput.addEventListener('input', function() {
        validateStartDate();
    });
    
    /**
     * Validates the start date to ensure it's not in the past
     * @returns {boolean} True if date is valid, false otherwise
     */
    function validateStartDate() {
        const selectedDate = new Date(startDateInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Set time to beginning of day for accurate comparison
        
        const isValid = selectedDate >= today;
        
        if (!isValid) {
            startDateInput.classList.add('border-red-500');
            dateErrorMessage.classList.remove('hidden');
            dateError.classList.remove('hidden');
            return false;
        } else {
            startDateInput.classList.remove('border-red-500');
            dateErrorMessage.classList.add('hidden');
            dateError.classList.add('hidden');
            return true;
        }
    }
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const form = document.getElementById('addTransactionForm');
    
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Always prevent default submission to handle validation
        
        const startDateInput = document.getElementById('startDateInput');
        const endDateInput = document.getElementById('endDateInput');
        const selectedDate = new Date(startDateInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const selectedMemberId = document.getElementById('selectedMemberId')?.value;
        
        // Validate required fields
        if (!selectedMemberId) {
            showToast('Please select a member first.', false);
            return false;
        }
        
        // Prevent submission if start date is in the past
        if (selectedDate < today) {
            startDateInput.classList.add('border-red-500');
            document.getElementById('dateErrorMessage').classList.remove('hidden');
            document.getElementById('dateError').classList.remove('hidden');
            
            showToast('Start date cannot be in the past. Please select today or a future date.', false);
            return false;
        }
        
        // Check for overlapping subscription dates
        checkForOverlappingSubscriptions(
            selectedMemberId, 
            startDateInput.value, 
            endDateInput.value
        ).then(hasOverlap => {
            if (!hasOverlap) {
                // No overlap, proceed with submission
                submitTransactionForm();
            }
            // If there is overlap, the error is displayed by checkForOverlappingSubscriptions
        });
    });
}

/**
 * Check if the given date range overlaps with any existing subscriptions for the member
 * @param {number} memberId - The ID of the member
 * @param {string} startDate - The start date in YYYY-MM-DD format
 * @param {string} endDate - The end date in YYYY-MM-DD format
 * @param {number|null} renewalId - Optional ID of subscription being renewed to exclude from overlap check
 * @returns {Promise<boolean>} - Promise resolving to true if overlap exists, false otherwise
 */
function checkForOverlappingSubscriptions(memberId, startDate, endDate, renewalId = null) {
    return fetch('../../functions/check-active-subscription.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            memberId: memberId,
            startDate: startDate,
            endDate: endDate,
            renewalId: renewalId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.hasOverlappingSubscription) {
            // Show error message about overlapping subscription
            showToast(data.message, false);
            
            // Highlight the date fields with error
            const startDateInput = document.getElementById('startDateInput');
            const endDateInput = document.getElementById('endDateInput');
            
            startDateInput.classList.add('border-red-500');
            endDateInput.classList.add('border-red-500');
            
            // Show error in subscription details summary
            const dateErrorMessage = document.getElementById('dateErrorMessage');
            if (dateErrorMessage) {
                dateErrorMessage.textContent = data.message;
                dateErrorMessage.classList.remove('hidden');
            }
            
            return true; // Has overlap
        }
        
        return false; // No overlap
    })
    .catch(error => {
        console.error('Error checking subscription overlap:', error);
        showToast('Error checking subscription dates. Please try again.', false);
        return false; // Continue with form submission in case of error
    });
}

/**
 * Handle renewal of subscription
 * @param {number} memberId - Member ID
 * @param {number} subscriptionId - Subscription ID to renew
 */
function handleRenewSubscription(memberId, subscriptionId) {
    // Get the current date and set it as the default start date
    const today = new Date();
    const formattedDate = today.toISOString().split('T')[0];
    
    // Populate the form with the member ID and subscription ID
    document.getElementById('selectedMemberId').value = memberId;
    
    // If we have a subscription select, set it to the specified subscription
    const subscriptionSelect = document.getElementById('subscriptionSelect');
    if (subscriptionSelect) {
        subscriptionSelect.value = subscriptionId;
        
        // Trigger the change event to update any dependent fields
        const event = new Event('change', { bubbles: true });
        subscriptionSelect.dispatchEvent(event);
    }
    
    // Set the start date to today
    const startDateInput = document.getElementById('startDateInput');
    if (startDateInput) {
        startDateInput.value = formattedDate;
        
        // Trigger the change event to calculate end date
        const event = new Event('change', { bubbles: true });
        startDateInput.dispatchEvent(event);
    }
    
    // Set the isRenewal flag and previousSubId in hidden fields
    const renewalFlagInput = document.getElementById('isRenewalInput') || createHiddenInput('isRenewalInput', 'true');
    renewalFlagInput.value = 'true';
    
    const previousSubIdInput = document.getElementById('previousSubIdInput') || createHiddenInput('previousSubIdInput', subscriptionId);
    previousSubIdInput.value = subscriptionId;
    
    // Show the transaction modal
    const modal = document.getElementById('addTransactionModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

/**
 * Create a hidden input field if it doesn't exist
 * @param {string} id - Element ID
 * @param {string} value - Initial value
 * @returns {HTMLElement} The input element
 */
function createHiddenInput(id, value) {
    const form = document.getElementById('addTransactionForm');
    if (!form) return null;
    
    const input = document.createElement('input');
    input.type = 'hidden';
    input.id = id;
    input.name = id;
    input.value = value;
    
    form.appendChild(input);
    return input;
}

/**
 * Submits the transaction form data to create a new transaction
 */
function submitTransactionForm() {
    const form = document.getElementById('addTransactionForm');
    if (!form) {
        console.error('Transaction form not found');
        return;
    }
    
    // Get form data
    const selectedMemberId = document.getElementById('selectedMemberId')?.value;
    const paymentSelect = document.getElementById('paymentSelect');
    const startDateInput = document.getElementById('startDateInput');
    const endDateInput = document.getElementById('endDateInput');
    const subscriptionSelect = document.getElementById('subscriptionSelect');
    
    // Get renewal flags
    const isRenewal = document.getElementById('isRenewalInput')?.value === 'true';
    const previousSubId = document.getElementById('previousSubIdInput')?.value;
    
    if (!selectedMemberId || !paymentSelect || !startDateInput || !endDateInput || !subscriptionSelect) {
        showToast('Missing form fields. Please refresh and try again.', false);
        return;
    }
    
    // Disable the submit button to prevent duplicate submissions
    const submitButton = document.querySelector('#addTransactionForm button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }
    
    // Create transaction data with additional debug info
    const transactionData = {
        memberId: parseInt(selectedMemberId),
        subscriptionId: parseInt(subscriptionSelect.value),
        paymentId: parseInt(paymentSelect.value),
        startDate: startDateInput.value,
        endDate: endDateInput.value,
        isRenewal: isRenewal,
        previousSubId: previousSubId ? parseInt(previousSubId) : null,
        requestId: 'txn_' + Date.now(), // Add unique request ID to prevent duplicates
        clientInfo: {
            timestamp: new Date().toISOString(),
            url: window.location.href
        }
    };
    
    console.log('Submitting transaction data:', transactionData);
    
    // Submit data to server with detailed error handling
    fetch('../../functions/create-transaction.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Cache-Control': 'no-cache'
        },
        body: JSON.stringify(transactionData)
    })
    .then(response => {
        console.log('Server response status:', response.status);
        if (!response.ok) {
            console.error('Server error occurred:', response.status);
            return response.json().then(data => {
                throw new Error('Server error: ' + (data.message || response.statusText));
            });
        }
        return response.json();
    })
    .then(data => {
        console.log('Transaction response:', data);
        if (data.success) {
            // Show success message
            showToast(data.message, true);
            
            // Reset form
            form.reset();
            
            // Reset renewal flag and previous sub ID
            if (document.getElementById('isRenewalInput')) {
                document.getElementById('isRenewalInput').value = 'false';
            }
            
            if (document.getElementById('previousSubIdInput')) {
                document.getElementById('previousSubIdInput').value = '';
            }
            
            // Close modal
            const modal = document.getElementById('addTransactionModal');
            if (modal) {
                modal.classList.add('hidden');
            }
            
            // Ensure we have transaction data
            if (!data.transaction) {
                console.warn('Transaction successful but no transaction data returned');
                // Force page reload after a short delay
                setTimeout(() => window.location.reload(), 1000);
                return;
            }
            
            try {
                // Try to update the UI
                if (typeof addTransactionToTable === 'function') {
                    addTransactionToTable(data.transaction);
                } else {
                    // Define the function if it doesn't exist
                    window.addTransactionToTable = function(transaction) {
                        console.log('Using fallback addTransactionToTable function with:', transaction);
                        updateUIWithNewTransaction(transaction);
                    };
                    
                    // Now call it
                    window.addTransactionToTable(data.transaction);
                }
            } catch (uiError) {
                console.error('Error updating UI:', uiError);
                // Fallback to page refresh
                setTimeout(() => window.location.reload(), 1000);
            }
        } else {
            showToast(data.message || 'Unknown error occurred', false);
        }
    })
    .catch(error => {
        console.error('Transaction creation error:', error);
        showToast('Error creating transaction. Please try again: ' + error.message, false);
    })
    .finally(() => {
        // Re-enable the submit button
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Submit';
        }
    });
}

/**
 * Fallback function to update UI with new transaction data
 * This function attempts to find and update the transaction table if it exists
 */
function updateUIWithNewTransaction(transaction) {
    console.log('Updating UI with transaction:', transaction);
    
    // Find the transaction table
    const tableId = 'transactionTable';
    let transactionTable = document.getElementById(tableId);
    
    // If we can't find the main table, look for any table that might contain transactions
    if (!transactionTable) {
        const tables = document.querySelectorAll('table');
        for (const table of tables) {
            // Check if this looks like a transaction table
            const headers = table.querySelectorAll('th');
            const headerText = Array.from(headers).map(h => h.textContent.toLowerCase());
            
            if (headerText.includes('member') || headerText.includes('subscription') || 
                headerText.includes('date') || headerText.includes('payment')) {
                transactionTable = table;
                console.log('Found potential transaction table:', table.id || 'unnamed table');
                break;
            }
        }
    }
    
    // If we found a table, add the new row
    if (transactionTable) {
        try {
            const tbody = transactionTable.querySelector('tbody') || transactionTable;
            
            // Create a new row
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-transaction-id', transaction.transactionId || '');
            newRow.setAttribute('data-member-id', transaction.memberId || '');
            
            // Format dates
            const startDate = new Date(transaction.startDate).toLocaleDateString();
            const endDate = new Date(transaction.endDate).toLocaleDateString();
            const transactionDate = new Date(transaction.transactionDate || Date.now()).toLocaleDateString();
            
            // Add suitable classes
            newRow.className = 'bg-gray-50 hover:bg-gray-100 transition-colors';
            
            // Create row content based on the most common transaction table structure
            newRow.innerHTML = `
                <td class="px-3 py-2 text-sm">${transaction.memberName || 'N/A'}</td>
                <td class="px-3 py-2 text-sm">${transaction.subscriptionName || 'N/A'}</td>
                <td class="px-3 py-2 text-sm">${startDate}</td>
                <td class="px-3 py-2 text-sm">${endDate}</td>
                <td class="px-3 py-2 text-sm">${transaction.paymentMethod || 'N/A'}</td>
                <td class="px-3 py-2 text-sm">${transactionDate}</td>
                <td class="px-3 py-2 text-sm">
                    <span class="px-2 py-1 rounded-full text-xs text-white bg-green-500">
                        New
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
            newRow.classList.add('animate-pulse');
            setTimeout(() => newRow.classList.remove('animate-pulse'), 2000);
            
            console.log('Successfully added transaction to table');
            
            // Update any counters
            updateCountersForNewTransaction();
            
        } catch (err) {
            console.error('Error adding row to table:', err);
            setTimeout(() => window.location.reload(), 1000);
        }
    } else {
        console.warn('Could not find transaction table, reloading page');
        setTimeout(() => window.location.reload(), 1000);
    }
}

/**
 * Update any counters on the page when a new transaction is added
 */
function updateCountersForNewTransaction() {
    // Look for elements that might be counters
    const counters = [
        document.getElementById('transactionCounter'),
        document.getElementById('transactionCount'),
        document.querySelector('.transaction-count'),
        document.querySelector('[data-transaction-count]'),
        ...document.querySelectorAll('.counter')
    ];
    
    for (const counter of counters) {
        if (!counter) continue;
        
        const currentValue = parseInt(counter.textContent.trim());
        if (!isNaN(currentValue)) {
            counter.textContent = (currentValue + 1).toString();
            console.log('Updated counter:', counter, 'to', currentValue + 1);
        }
    }
}

// Add this function to better handle adding transactions to the table
function addTransactionToTable(transaction) {
    console.log('Adding transaction to table:', transaction);
    try {
        // Find the transaction table or create one if it doesn't exist
        let transactionTable = document.getElementById('transactionTable');
        if (!transactionTable) {
            console.warn('Transaction table not found, will reload page instead');
            setTimeout(() => window.location.reload(), 1000);
            return;
        }
        
        // Create a new table row
        const newRow = document.createElement('tr');
        
        // Format date for display
        const startDate = new Date(transaction.startDate).toLocaleDateString();
        const endDate = new Date(transaction.endDate).toLocaleDateString();
        const transactionDate = transaction.transactionDate ? 
            new Date(transaction.transactionDate).toLocaleDateString() : 
            new Date().toLocaleDateString();
            
        // Create row content
        newRow.innerHTML = `
            <td class="px-3 py-2">${transaction.memberName || 'N/A'}</td>
            <td class="px-3 py-2">${transaction.subscriptionName || 'N/A'}</td>
            <td class="px-3 py-2">${startDate}</td>
            <td class="px-3 py-2">${endDate}</td>
            <td class="px-3 py-2">${transaction.paymentMethod || 'N/A'}</td>
            <td class="px-3 py-2">${transactionDate}</td>
            <td class="px-3 py-2">
                <span class="px-2 py-1 rounded-full text-xs text-white bg-green-500">New</span>
            </td>
        `;
        
        // Add the new row to the table
        const tbody = transactionTable.querySelector('tbody');
        if (tbody) {
            tbody.insertBefore(newRow, tbody.firstChild);
        } else {
            console.warn('Table body not found');
        }
        
        // Update any counters or summaries
        updateTransactionCounters();
    } catch (error) {
        console.error('Error adding transaction to table:', error);
        // Fallback to page refresh
        setTimeout(() => window.location.reload(), 1000);
    }
}

// Helper function to update transaction counters
function updateTransactionCounters() {
    // Update the total transaction count if available
    const transactionCounter = document.getElementById('transactionCounter');
    if (transactionCounter) {
        const currentCount = parseInt(transactionCounter.textContent || '0');
        transactionCounter.textContent = (currentCount + 1).toString();
    }
}

/**
 * Calculate end date based on start date and subscription duration
 * 
 * @param {string} startDate - The start date in YYYY-MM-DD format
 * @param {number} durationDays - The subscription duration in days
 * @returns {string} - The calculated end date in YYYY-MM-DD format
 */
function calculateEndDate(startDate, durationDays) {
    const start = new Date(startDate);
    const end = new Date(start);
    end.setDate(end.getDate() + durationDays);
    
    // Format date as YYYY-MM-DD
    return end.toISOString().split('T')[0];
}

/**
 * Update the subscription plan display summary with validation information
 * 
 * @param {Object} plan - The subscription plan object
 * @param {boolean} isDateValid - Whether the selected date is valid
 */
function updateSubscriptionSummary(plan, isDateValid = true) {
    const summaryEl = document.getElementById('subscriptionDetailsSummary');
    const errorMsgEl = document.getElementById('dateErrorMessage');
    
    if (!summaryEl) return;
    
    if (!plan) {
        summaryEl.textContent = 'Select a subscription plan';
        return;
    }
    
    // Format price as currency
    const formattedPrice = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(plan.price);
    
    // Update summary with plan details
    summaryEl.textContent = `${plan.name}: ${formattedPrice} for ${plan.duration} days`;
    
    // Show/hide date error message
    if (errorMsgEl) {
        if (isDateValid) {
            errorMsgEl.classList.add('hidden');
        } else {
            errorMsgEl.classList.remove('hidden');
        }
    }
}