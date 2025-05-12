/**
 * Transaction Fix Script
 * Patches transaction submission to fix "Internal Server Error" issues
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Transaction fix script loaded');

    // Wait for page to fully load
    setTimeout(function() {
        patchTransactionSubmission();
    }, 500);

    function patchTransactionSubmission() {
        console.log('Patching transaction submission handler');
        
        // Get the submit button
        const submitBtn = document.getElementById('submitTransactionBtn');
        if (!submitBtn) {
            console.log('Submit button not found, cannot patch');
            return;
        }
        
        // Remove all existing click event listeners
        const newSubmitBtn = submitBtn.cloneNode(true);
        submitBtn.parentNode.replaceChild(newSubmitBtn, submitBtn);
        
        // Add our new event listener
        newSubmitBtn.addEventListener('click', function() {
            console.log('Using patched transaction submission handler');
            
            // Get form fields
            const memberId = document.getElementById('selectedMemberId').value;
            const subscriptionId = document.getElementById('subscriptionSelect').value;
            const paymentId = document.getElementById('paymentSelect').value;
            const startDate = document.getElementById('startDateInput').value;
            const endDate = document.getElementById('endDateInput').value;
            
            // Validate form
            let hasErrors = false;
            
            // Reset previous error states
            document.querySelectorAll('#addTransactionForm .error-border').forEach(el => {
                el.classList.remove('error-border');
            });
            document.querySelectorAll('#addTransactionForm .error-message').forEach(el => {
                el.remove();
            });
            
            // Member validation
            if (!memberId) {
                hasErrors = true;
                const memberSearchContainer = document.querySelector('#addTransactionForm #memberSearch').closest('div');
                if (memberSearchContainer) {
                    highlightError(memberSearchContainer, 'Please select a member');
                }
            }
            
            // Subscription validation
            if (!subscriptionId) {
                hasErrors = true;
                const subContainer = document.getElementById('subscriptionSelect').parentElement;
                highlightError(subContainer, 'Please select a subscription plan');
            }
            
            // Payment method validation
            if (!paymentId) {
                hasErrors = true;
                const payContainer = document.getElementById('paymentSelect').parentElement;
                highlightError(payContainer, 'Please select a payment method');
            }
            
            // Date validation
            if (!startDate) {
                hasErrors = true;
                const startContainer = document.getElementById('startDateInput').parentElement;
                highlightError(startContainer, 'Please set a start date');
            }
            
            if (!endDate) {
                hasErrors = true;
                const endContainer = document.getElementById('endDateInput').parentElement;
                highlightError(endContainer, 'Please set an end date');
            }
            
            // If validation fails, exit
            if (hasErrors) {
                return;
            }
            
            // Show loading state on the button
            const originalBtnText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
            this.disabled = true;
            
            // Get form attributes for renewal info
            const form = document.getElementById('addTransactionForm');
            const isRenewal = form.getAttribute('data-is-renewal') === 'true';
            const previousSubId = form.getAttribute('data-previous-sub-id');
            
            // Create transaction data
            const transactionData = {
                memberId: memberId,
                subscriptionId: subscriptionId,
                paymentId: paymentId,
                startDate: startDate,
                endDate: endDate
            };
            
            // Add renewal information if available
            if (isRenewal) {
                transactionData.isRenewal = true;
                if (previousSubId) {
                    transactionData.previousSubId = previousSubId;
                }
            }
            
            console.log('Sending transaction data:', transactionData);
            
            // Use a try-catch block around the fetch to handle all errors
            try {
                fetch('../../functions/create-transaction.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(transactionData)
                })
                .then(response => {
                    // Check if we have a valid response
                    if (!response.ok) {
                        // Don't fake a success response for 500 errors anymore
                        // Log the error and let the user know something went wrong
                        console.error('Server error occurred: ' + response.status);
                        return response.json().catch(() => {
                            throw new Error('Server error: ' + response.statusText);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Success - close modal and show success message
                        if (typeof closeModal === 'function') {
                            closeModal(document.getElementById('addTransactionModal'));
                        } else {
                            document.getElementById('addTransactionModal').classList.add('hidden');
                        }
                        
                        // Reset form
                        form.reset();
                        
                        // Reset UI
                        if (typeof resetTransactionModalUI === 'function') {
                            resetTransactionModalUI();
                        }
                        
                        // Show success message
                        if (typeof showToast === 'function') {
                            showToast(data.message || 'Transaction processed successfully!', true);
                        } else {
                            alert(data.message || 'Transaction processed successfully!');
                        }
                        
                        console.log('Transaction successfully created with ID: ' + 
                                   (data.transaction ? data.transaction.transactionId : 'unknown'));
                        
                        // Refresh the page after a short delay
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        throw new Error(data.message || 'Transaction failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Show error message
                    if (typeof showToast === 'function') {
                        showToast(error.message || 'An error occurred while processing the transaction', false);
                    } else {
                        alert(error.message || 'An error occurred while processing the transaction');
                    }
                })
                .finally(() => {
                    // Reset button
                    this.innerHTML = originalBtnText;
                    this.disabled = false;
                });
            } catch (error) {
                console.error('Critical error in fetch:', error);
                
                // Reset button
                this.innerHTML = originalBtnText;
                this.disabled = false;
                
                // Show error message instead of fake success
                if (typeof showToast === 'function') {
                    showToast('Error: Could not connect to server. Please try again.', false);
                } else {
                    alert('Error: Could not connect to server. Please try again.');
                }
            }
        });
        
        console.log('Transaction submission handler patched successfully');
    }
    
    // Helper function to highlight error fields
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
}); 