/**
 * Override the default showConfirmationDialog function to automatically confirm
 * any "Discard Changes" dialogs without showing them to the user.
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Auto-confirm script loaded');
    
    // Save a reference to the original function if it exists
    if (typeof window.showConfirmationDialog === 'function') {
        window.originalShowConfirmationDialog = window.showConfirmationDialog;
    }
    
    // Override the function
    window.showConfirmationDialog = function(title, message, onConfirm) {
        console.log('Confirmation dialog requested:', title);
        
        // If this is a discard changes confirmation, skip dialog and proceed directly
        if (title === 'Discard Changes') {
            console.log('Auto-confirming "Discard Changes" dialog');
            // Immediately invoke the confirmation callback
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
            return;
        }
        
        // For all other confirmation types, use the original implementation if available
        if (typeof window.originalShowConfirmationDialog === 'function') {
            window.originalShowConfirmationDialog(title, message, onConfirm);
        } else {
            // Fallback implementation
            const confirmed = window.confirm(message);
            if (confirmed && typeof onConfirm === 'function') {
                onConfirm();
            }
        }
    };
    
    // Also override the window.confirm function for modals
    const originalConfirm = window.confirm;
    window.confirm = function(message) {
        console.log('Browser confirm dialog requested:', message);
        // If this is about discarding changes, auto-confirm it
        if (message && message.includes('discard') && message.includes('changes')) {
            console.log('Auto-confirming discard changes browser confirm');
            return true;
        }
        // Otherwise, use the original confirm
        return originalConfirm.apply(this, arguments);
    };
    
    console.log('Confirmation dialog override installed');
}); 