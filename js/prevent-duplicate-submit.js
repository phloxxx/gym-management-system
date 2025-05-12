/**
 * Prevents duplicate form submissions by:
 * 1. Disabling submit buttons after click
 * 2. Adding unique request IDs to AJAX submissions
 * 3. Debouncing form submissions
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Duplicate submission prevention script loaded');
    
    // Generate a unique request ID
    function generateRequestId() {
        return 'req_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    // Find all forms on the page
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        // Add hidden request_id field to all forms
        const requestIdField = document.createElement('input');
        requestIdField.type = 'hidden';
        requestIdField.name = 'request_id';
        requestIdField.value = generateRequestId();
        form.appendChild(requestIdField);
        
        // Add submission handling to prevent duplicates
        form.addEventListener('submit', function(e) {
            // Check if form is already being submitted
            if (form.dataset.isSubmitting === 'true') {
                console.log('Preventing duplicate submission');
                e.preventDefault();
                return false;
            }
            
            // Mark form as being submitted
            form.dataset.isSubmitting = 'true';
            
            // Find and disable submit buttons
            const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitButtons.forEach(button => {
                button.disabled = true;
                
                // Store original text if it's a button
                if (button.tagName === 'BUTTON') {
                    button.dataset.originalText = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                }
            });
            
            // For AJAX forms, we need to reset the form status after the request completes
            if (form.dataset.ajax === 'true') {
                // This will be handled by the global AJAX interceptor below
            } else {
                // For regular forms, we don't reset - the page will refresh
            }
        });
    });
    
    // Override jQuery AJAX to prevent duplicate submissions and add request IDs
    if (typeof jQuery !== 'undefined') {
        const originalAjax = jQuery.ajax;
        jQuery.ajax = function(options) {
            // For FormData objects, add the request ID
            if (options.data instanceof FormData) {
                options.data.append('request_id', generateRequestId());
            } 
            // For data objects, add the request ID
            else if (typeof options.data === 'object' && options.data !== null) {
                options.data.request_id = generateRequestId();
            }
            
            // Enhance the complete callback to reset form submission state
            const originalComplete = options.complete;
            options.complete = function(jqXHR, textStatus) {
                // Call the original complete callback if it exists
                if (typeof originalComplete === 'function') {
                    originalComplete(jqXHR, textStatus);
                }
                
                // Reset all forms' submission state
                const forms = document.querySelectorAll('form[data-is-submitting="true"]');
                forms.forEach(form => {
                    // Reset submission status
                    form.dataset.isSubmitting = 'false';
                    
                    // Re-enable submit buttons
                    const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                    submitButtons.forEach(button => {
                        button.disabled = false;
                        
                        // Restore original text if it's a button
                        if (button.tagName === 'BUTTON' && button.dataset.originalText) {
                            button.innerHTML = button.dataset.originalText;
                        }
                    });
                    
                    // Generate new request ID for next submission
                    const requestIdField = form.querySelector('input[name="request_id"]');
                    if (requestIdField) {
                        requestIdField.value = generateRequestId();
                    }
                });
            };
            
            // Call the original ajax function with our enhanced options
            return originalAjax.call(this, options);
        };
        
        console.log('jQuery AJAX interceptor installed');
    }
});
