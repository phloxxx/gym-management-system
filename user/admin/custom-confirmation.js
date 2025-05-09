/**
 * Shows a custom confirmation dialog with the specified title, message, and callback function.
 * 
 * @param {string} title The title of the confirmation dialog
 * @param {string} message The message to display in the confirmation dialog
 * @param {Function} onConfirm The callback function to execute if the user confirms
 */
function showCustomConfirmation(title, message, onConfirm) {
    console.log('Showing confirmation dialog:', { title, message });
    
    // First, remove any existing dialogs to prevent duplicates
    const existingDialogs = document.querySelectorAll('.custom-confirmation-dialog');
    if (existingDialogs.length > 0) {
        console.log(`Removing ${existingDialogs.length} existing dialog(s)`);
        existingDialogs.forEach(dialog => {
            document.body.removeChild(dialog);
        });
    }
    
    // Disable any standard browser confirmations
    window.confirmBackup = window.confirm;
    window.confirm = function() {
        console.log('Blocking standard confirm dialog');
        return false;
    };
    
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center custom-confirmation-dialog';
    
    // Create dialog
    const dialog = document.createElement('div');
    dialog.className = 'bg-white rounded-lg shadow-xl p-6 max-w-md mx-auto';
    
    // Create dialog content
    dialog.innerHTML = `
        <h3 class="text-lg font-medium text-gray-900 mb-4">${title || 'Confirmation'}</h3>
        <p class="text-gray-600 mb-6">${message || 'Are you sure?'}</p>
        <div class="flex justify-end space-x-3">
            <button type="button" class="cancel-btn px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                Cancel
            </button>
            <button type="button" class="confirm-btn px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition-colors">
                Confirm
            </button>
        </div>
    `;
    
    // Add dialog to overlay
    overlay.appendChild(dialog);
    
    // Add overlay to body
    document.body.appendChild(overlay);
    
    // Handle button clicks
    const cancelBtn = dialog.querySelector('.cancel-btn');
    const confirmBtn = dialog.querySelector('.confirm-btn');
    
    // Close dialog
    const closeDialog = () => {
        document.body.removeChild(overlay);
        // Restore original confirm function
        window.confirm = window.confirmBackup;
    };
    
    // Cancel button
    cancelBtn.addEventListener('click', closeDialog);
    
    // Confirm button
    confirmBtn.addEventListener('click', () => {
        closeDialog();
        if (typeof onConfirm === 'function') {
            onConfirm();
        }
    });
    
    // Return false to prevent any original event handlers from continuing
    return false;
} 