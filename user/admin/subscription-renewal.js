/**
 * Subscription Renewal Handler
 * Contains functionality specific to subscription renewals
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to renewal buttons
    initializeRenewalButtons();
    
    // Check if we need to load the renewal helper script
    if (typeof window.processRenewal !== 'function') {
        loadRenewalHelperScript();
    }
});

/**
 * Load the renewal helper script dynamically
 */
function loadRenewalHelperScript() {
    const script = document.createElement('script');
    script.src = '../../functions/renewal-helper.js';
    script.onload = function() {
        console.log('Renewal helper script loaded');
        // Re-initialize renewal buttons to make sure event handlers use the loaded functions
        initializeRenewalButtons();
    };
    script.onerror = function() {
        console.error('Failed to load renewal helper script');
    };
    document.body.appendChild(script);
}

/**
 * Initialize renewal buttons
 * Attaches event listeners to buttons with renewal functionality
 */
function initializeRenewalButtons() {
    // Find all renewal buttons
    const renewalButtons = document.querySelectorAll('.renew-subscription-btn, [data-action="renew"], button[data-renew="true"]');
    
    renewalButtons.forEach(button => {
        // Remove any existing event listeners by cloning and replacing
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);
        
        // Add the new event listener
        newButton.addEventListener('click', handleRenewalClick);
    });
    
    console.log(`Initialized ${renewalButtons.length} renewal buttons`);
}

/**
 * Handle click on renewal button
 * @param {Event} e Click event
 */
function handleRenewalClick(e) {
    e.preventDefault();
    
    const button = e.currentTarget;
    
    // Extract member ID and subscription ID from button data attributes
    const memberId = button.dataset.memberId || button.getAttribute('data-member-id');
    const subscriptionId = button.dataset.subId || button.dataset.subscriptionId || button.getAttribute('data-sub-id');
    const previousSubId = button.dataset.prevSubId || button.dataset.previousSubId || subscriptionId;
    
    if (!memberId || !subscriptionId) {
        console.error('Missing required data attributes for renewal:', { memberId, subscriptionId });
        showToast('Unable to renew: Missing member or subscription information', false);
        return;
    }
    
    console.log(`Initializing renewal for member ${memberId}, subscription ${subscriptionId}`);
    
    // Show renewal form or modal
    // If the addTransactionModal exists, we'll use that
    const transactionModal = document.getElementById('addTransactionModal');
    
    if (transactionModal) {
        // Use the existing transaction modal but prefill it for renewal
        prefillTransactionFormForRenewal(memberId, subscriptionId, previousSubId);
        transactionModal.classList.remove('hidden');
    } else {
        // No modal found, create a simpler renewal process
        processSimpleRenewal(memberId, subscriptionId, previousSubId);
    }
}

/**
 * Prefill the transaction form for renewal
 */
function prefillTransactionFormForRenewal(memberId, subscriptionId, previousSubId) {
    // Set the member ID
    const memberIdInput = document.getElementById('selectedMemberId');
    if (memberIdInput) memberIdInput.value = memberId;
    
    // Set the subscription ID
    const subscriptionSelect = document.getElementById('subscriptionSelect');
    if (subscriptionSelect) {
        subscriptionSelect.value = subscriptionId;
        // Trigger change event to update any dependent fields
        subscriptionSelect.dispatchEvent(new Event('change'));
    }
    
    // Set the payment method to a default if available
    const paymentSelect = document.getElementById('paymentSelect');
    if (paymentSelect && paymentSelect.options.length > 0) {
        // Default to first payment method
        paymentSelect.selectedIndex = 0;
    }
    
    // Set today as the start date
    const startDateInput = document.getElementById('startDateInput');
    if (startDateInput) {
        startDateInput.value = new Date().toISOString().split('T')[0];
        // Trigger change event to calculate end date
        startDateInput.dispatchEvent(new Event('change'));
    }
    
    // Add renewal flags
    const form = document.getElementById('addTransactionForm');
    if (form) {
        // Create or update isRenewal input
        let renewalInput = document.getElementById('isRenewalInput');
        if (!renewalInput) {
            renewalInput = document.createElement('input');
            renewalInput.type = 'hidden';
            renewalInput.id = 'isRenewalInput';
            renewalInput.name = 'isRenewal';
            form.appendChild(renewalInput);
        }
        renewalInput.value = 'true';
        
        // Create or update previousSubId input
        let prevSubInput = document.getElementById('previousSubIdInput');
        if (!prevSubInput) {
            prevSubInput = document.createElement('input');
            prevSubInput.type = 'hidden';
            prevSubInput.id = 'previousSubIdInput';
            prevSubInput.name = 'previousSubId';
            form.appendChild(prevSubInput);
        }
        prevSubInput.value = previousSubId;
    }
    
    // Update any modal title or labels to indicate this is a renewal
    const modalTitle = document.querySelector('#addTransactionModal .modal-title') || 
                      document.querySelector('#addTransactionModal h2');
    if (modalTitle) {
        modalTitle.textContent = 'Renew Subscription';
    }
}

/**
 * Process a simple renewal without using the modal form
 * This is used when the standard transaction modal isn't available
 */
function processSimpleRenewal(memberId, subscriptionId, previousSubId) {
    // First check if we have the processRenewal function available
    if (typeof window.processRenewal !== 'function') {
        console.error('processRenewal function not available');
        // Try to load the renewal helper script if it's not already loaded
        loadRenewalHelperScript();
        setTimeout(() => {
            if (typeof window.processRenewal === 'function') {
                processSimpleRenewal(memberId, subscriptionId, previousSubId);
            } else {
                showToast('Unable to process renewal: Required functions not available', false);
            }
        }, 1000);
        return;
    }
    
    // Create a simple dialog to collect payment method
    const dialogHtml = `
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" id="simpleRenewalDialog">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-medium mb-4">Renew Subscription</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Method
                    </label>
                    <select id="renewalPaymentMethod" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1">Cash</option>
                        <option value="2">Credit Card</option>
                        <option value="3">Debit Card</option>
                        <option value="4">Online Transfer</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Start Date
                    </label>
                    <input type="date" id="renewalStartDate" value="${new Date().toISOString().split('T')[0]}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button id="cancelRenewalBtn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none">
                        Cancel
                    </button>
                    <button id="confirmRenewalBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">
                        Process Renewal
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Add the dialog to the page
    const dialogContainer = document.createElement('div');
    dialogContainer.innerHTML = dialogHtml;
    document.body.appendChild(dialogContainer.firstChild);
    
    const dialog = document.getElementById('simpleRenewalDialog');
    const cancelBtn = document.getElementById('cancelRenewalBtn');
    const confirmBtn = document.getElementById('confirmRenewalBtn');
    
    // Add event listeners
    cancelBtn.addEventListener('click', () => {
        dialog.remove();
    });
    
    confirmBtn.addEventListener('click', () => {
        const paymentId = document.getElementById('renewalPaymentMethod').value;
        const startDate = document.getElementById('renewalStartDate').value;
        
        // Show loading state
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="animate-pulse">Processing...</span>';
        
        // Process the renewal
        const renewalData = {
            memberId: memberId,
            subscriptionId: subscriptionId,
            paymentId: paymentId,
            startDate: startDate,
            previousSubId: previousSubId
        };
        
        window.processRenewal(renewalData)
            .then(response => {
                console.log('Renewal response:', response);
                dialog.remove();
                
                if (response.success) {
                    // Show success message
                    showToast(response.message || 'Subscription renewed successfully', true);
                    
                    // Update UI
                    if (typeof window.updateUIWithRenewal === 'function' && response.transaction) {
                        window.updateUIWithRenewal(response.transaction);
                    } else {
                        // Fallback to page reload after showing the success message
                        setTimeout(() => window.location.reload(), 1500);
                    }
                } else {
                    showToast(response.message || 'Failed to renew subscription', false);
                }
            })
            .catch(error => {
                console.error('Renewal error:', error);
                dialog.remove();
                showToast('Error renewing subscription: ' + error.message, false);
            });
    });
}

/**
 * Helper function to show toast notifications
 * @param {string} message The message to display
 * @param {boolean} isSuccess Whether this is a success message
 */
function showToast(message, isSuccess) {
    // Check if there's a global showToast function
    if (typeof window.showToast === 'function') {
        window.showToast(message, isSuccess);
        return;
    }
    
    // Check if there's a global showRenewalToast function
    if (typeof window.showRenewalToast === 'function') {
        window.showRenewalToast(message, isSuccess);
        return;
    }
    
    // Create our own simple toast
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-md shadow-lg z-50 ${
        isSuccess ? 'bg-green-500' : 'bg-red-500'
    } text-white opacity-0 transition-opacity duration-300`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Fade in
    setTimeout(() => {
        toast.classList.remove('opacity-0');
    }, 10);
    
    // Fade out and remove
    setTimeout(() => {
        toast.classList.add('opacity-0');
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 5000);
}
