/**
 * Filter Transactions Module
 * Handles filtering and displaying transactions based on criteria
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize filter form handlers
    initializeFilterHandlers();
    
    // Add listener for member search
    initializeMemberSearch();
    
    console.log('Filter transactions module loaded');
});

/**
 * Initialize filter form event handlers
 */
function initializeFilterHandlers() {
    const filterForm = document.getElementById('filterForm');
    const resetButton = document.getElementById('resetFiltersBtn');
    
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleFilterSubmit(e);
        });
    }
    
    if (resetButton) {
        resetButton.addEventListener('click', function(e) {
            e.preventDefault();
            handleFilterReset();
        });
    }
    
    console.log('Filter handlers initialized');
}

/**
 * Initialize member search functionality
 */
function initializeMemberSearch() {
    const memberSearchInput = document.querySelector('input[name="memberSearch"], #memberSearch');
    
    if (memberSearchInput) {
        // Check if we should add a debounce to the search
        let debounceTimeout;
        
        memberSearchInput.addEventListener('input', function() {
            // Clear previous timeout
            clearTimeout(debounceTimeout);
            
            // Set a new timeout for 300ms
            debounceTimeout = setTimeout(() => {
                const searchValue = memberSearchInput.value.trim();
                
                // Only search if we have at least 2 characters or empty to reset
                if (searchValue.length >= 2 || searchValue.length === 0) {
                    // If empty, this will effectively reset the member filter
                    handleMemberSearch(searchValue);
                }
            }, 300);
        });
    }
}

/**
 * Handle member-specific search
 * @param {string} searchTerm - The search term
 */
function handleMemberSearch(searchTerm) {
    // If search is empty, don't apply any member-specific filtering
    if (!searchTerm) {
        // Just reset the member filter aspect
        fetchFilteredTransactions({}, false);
        return;
    }
    
    console.log(`Searching for member: "${searchTerm}"`);
    
    showLoadingState(`Searching for "${searchTerm}"...`);
    
    // We're just filtering by member search term
    fetchFilteredTransactions({ 
        memberSearch: searchTerm 
    }, true);
}

/**
 * Handle filter form submission
 * @param {Event} event - The submit event
 */
function handleFilterSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const filterData = {};
    
    // Convert form data to JSON object
    for (const [key, value] of formData.entries()) {
        if (value && value !== 'all' && value !== '') {
            filterData[key] = value;
        }
    }
    
    console.log('Applying filters:', filterData);
    
    // Show loading state
    showLoadingState('Applying filters...');
    
    // Send filter data to server
    fetchFilteredTransactions(filterData, true);
}

/**
 * Handle filter reset - FIXED to ensure ALL transactions show up
 */
function handleFilterReset() {
    console.log('Reset filters button clicked');
    
    // Get the filter form
    const filterForm = document.getElementById('filterForm');
    if (!filterForm) return;
    
    // Reset all form fields to their default values
    filterForm.reset();
    
    // Clear member search input 
    const memberSearchInput = document.querySelector('input[name="memberSearch"], #memberSearch');
    if (memberSearchInput) {
        memberSearchInput.value = '';
    }
    
    // If there are any select2 dropdowns, reset those as well
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        jQuery(filterForm).find('select').val(null).trigger('change');
    }
    
    // Show loading state
    showLoadingState('Loading all transactions...');
    
    // Important fix: Explicitly tell the server this is a reset to ensure ALL transactions are returned
    fetchFilteredTransactions({reset: true, show_all: true}, false);
}

/**
 * Show loading state while waiting for filter results
 * @param {string} message - Loading message to display
 */
function showLoadingState(message) {
    const resultsContainer = document.getElementById('filteredTransactions') || 
                             document.getElementById('transactionResults') ||
                             document.querySelector('.transaction-results');
    
    if (resultsContainer) {
        resultsContainer.innerHTML = `
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-gray-900"></div>
                <p class="mt-2 text-gray-500">${message || 'Loading results...'}</p>
            </div>
        `;
    }
}

/**
 * Fetch filtered transactions from server
 * @param {Object} filterData - The filter criteria
 * @param {boolean} isApplyFilter - Whether this is an apply filter action vs reset
 */
function fetchFilteredTransactions(filterData, isApplyFilter) {
    // Add a timestamp to prevent caching
    filterData.timestamp = Date.now();
    
    // Make sure we're requesting ALL transactions for a specific member if memberSearch is provided
    if (filterData.memberSearch) {
        filterData.show_all_member_transactions = true;
    }
    
    // Create a more resilient POST request with error handling
    fetch('../../functions/filter-transactions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-Action-Type': isApplyFilter ? 'apply-filters' : 'reset-filters',
            'Cache-Control': 'no-cache, no-store, must-revalidate'
        },
        body: JSON.stringify(filterData)
    })
    .then(response => {
        if (!response.ok) {
            console.error(`Server responded with status: ${response.status}`);
            // Try to get response text for better error messages
            return response.text().then(text => {
                throw new Error(`Server error (${response.status}): ${text.substring(0, 200)}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (!Array.isArray(data)) {
            console.error('Invalid response format, expected array:', data);
            throw new Error('Invalid response format from server');
        }
        
        console.log(`Received ${data.length} transactions from server`);
        updateTransactionTable(data);
        
        // Show success message
        if (typeof showToast === 'function') {
            if (filterData.memberSearch) {
                const resultText = data.length === 1 ? 'transaction' : 'transactions';
                showToast(`Found ${data.length} ${resultText} for "${filterData.memberSearch}"`, true);
            } else if (isApplyFilter) {
                showToast('Filters applied successfully', true);
            } else {
                showToast('Filters reset successfully', true);
            }
        }
    })
    .catch(error => {
        console.error('Error fetching transactions:', error);
        
        // Show error in results area
        const resultsContainer = document.getElementById('filteredTransactions') || 
                               document.getElementById('transactionResults') ||
                               document.querySelector('.transaction-results');
        
        if (resultsContainer) {
            resultsContainer.innerHTML = `
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error loading transactions!</strong>
                    <span class="block sm:inline"> ${error.message}</span>
                    <p class="mt-2">Please try again or refresh the page.</p>
                    <button id="retryLoadBtn" class="mt-2 bg-red-600 hover:bg-red-700 text-white py-1 px-3 rounded">
                        Retry
                    </button>
                </div>
            `;
            
            // Add retry button handler
            document.getElementById('retryLoadBtn')?.addEventListener('click', () => {
                // On retry, load all transactions
                handleFilterReset();
            });
        }
        
        // Show toast notification if available
        if (typeof showToast === 'function') {
            showToast('Error: ' + error.message, false);
        }
    });
}

/**
 * Update transaction table with filtered/reset results
 * @param {Array} transactions - The transactions to display
 */
function updateTransactionTable(transactions) {
    // Find the appropriate container
    const resultsContainer = document.getElementById('filteredTransactions') || 
                            document.getElementById('transactionResults') ||
                            document.querySelector('.transaction-results');
    
    if (!resultsContainer) {
        console.error('Results container not found');
        return;
    }
    
    // Check if we have any transactions
    if (!transactions || transactions.length === 0) {
        resultsContainer.innerHTML = `
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded">
                <p>No transactions found matching your criteria.</p>
                <p class="text-sm mt-2">Try different filter settings or <button class="underline" id="clearFiltersBtn">clear filters</button>.</p>
            </div>
        `;
        
        // Add event listener to the clear filters button
        document.getElementById('clearFiltersBtn')?.addEventListener('click', handleFilterReset);
        return;
    }
    
    console.log(`Updating table with ${transactions.length} transactions`);
    
    // Create table HTML
    let tableHTML = `
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white" id="transactionTable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-xs text-gray-500 text-left">MEMBER</th>
                        <th class="px-3 py-2 text-xs text-gray-500 text-left">SUBSCRIPTION</th>
                        <th class="px-3 py-2 text-xs text-gray-500 text-left">START DATE</th>
                        <th class="px-3 py-2 text-xs text-gray-500 text-left">END DATE</th>
                        <th class="px-3 py-2 text-xs text-gray-500 text-left">PAYMENT METHOD</th>
                        <th class="px-3 py-2 text-xs text-gray-500 text-left">TRANSACTION DATE</th>
                        <th class="px-3 py-2 text-xs text-gray-500 text-left">STATUS</th>
                        <th class="px-3 py-2 text-xs text-gray-500 text-left">ACTION</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    // Add rows for each transaction
    transactions.forEach(transaction => {
        // Determine status class
        let statusClass = transaction.isActive ? 'bg-green-500' : 'bg-gray-500';
        let statusText = transaction.isActive ? 'Active' : 'Inactive';
        
        // Add days left info if active
        let daysLeftText = '';
        if (transaction.isActive && transaction.daysLeft !== undefined) {
            if (transaction.daysLeft < 0) {
                statusClass = 'bg-red-500';
                statusText = 'Expired';
            } else if (transaction.daysLeft <= 7) {
                statusClass = 'bg-yellow-500';
                statusText = 'Expiring Soon';
                daysLeftText = ` (${transaction.daysLeft} days)`;
            }
        }
        
        // Determine if a row should be highlighted
        const isHighlighted = transaction.isNew ? 'bg-green-50' : '';
        
        tableHTML += `
            <tr class="border-b hover:bg-gray-50 ${isHighlighted}">
                <td class="px-3 py-2 text-sm">
                    <div class="flex items-center">
                        <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-medium mr-3">
                            ${transaction.memberInitials || 'N/A'}
                        </div>
                        <span>${transaction.memberName || 'N/A'}</span>
                    </div>
                </td>
                <td class="px-3 py-2 text-sm">${transaction.subscriptionName || 'N/A'}</td>
                <td class="px-3 py-2 text-sm">${transaction.startDate || 'N/A'}</td>
                <td class="px-3 py-2 text-sm">${transaction.endDate || 'N/A'}</td>
                <td class="px-3 py-2 text-sm">${transaction.paymentMethod || 'N/A'}</td>
                <td class="px-3 py-2 text-sm">${transaction.paidDate || 'N/A'}</td>
                <td class="px-3 py-2 text-sm">
                    <span class="px-2 py-1 rounded-full text-xs text-white ${statusClass}">
                        ${statusText}${daysLeftText}
                    </span>
                </td>
                <td class="px-3 py-2 text-sm">
                    <button 
                        class="text-green-600 hover:text-green-800" 
                        data-renew="true"
                        data-member-id="${transaction.memberId}"
                        data-sub-id="${transaction.subscriptionId}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tableHTML += `
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-sm text-gray-500">
            Found ${transactions.length} transaction${transactions.length !== 1 ? 's' : ''}.
        </div>
    `;
    
    // Update the container
    resultsContainer.innerHTML = tableHTML;
    
    // Reinitialize any renewals buttons added to the table
    if (typeof initializeRenewalButtons === 'function') {
        initializeRenewalButtons();
    }
}
