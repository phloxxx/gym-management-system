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