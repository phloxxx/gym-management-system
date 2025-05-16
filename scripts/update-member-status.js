/**
 * Update member status periodically to reflect real-time subscription status
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Member status updater loaded');
    
    // Set up background status updates every 30 seconds
    function setupStatusUpdates() {
        // Initial update
        updateMemberStatusDisplay();
        
        // Set interval for updates
        setInterval(updateMemberStatusDisplay, 30000);
    }
    
    // Function to update status display based on API calls
    function updateMemberStatusDisplay() {
        // Get all member rows with data-member-id attribute
        const memberRows = document.querySelectorAll('tr[data-member-id]');
        
        memberRows.forEach(row => {
            const memberId = row.getAttribute('data-member-id');
            if (!memberId) return;
            
            // Find the status cell in this row
            const statusCell = row.querySelector('.status-cell') || 
                               row.querySelector('td:nth-child(5)'); // Adjust column index if needed
            
            if (!statusCell) return;
            
            // Call the API to check subscription status
            fetch(`../../api/members/check-active-subscription.php?id=${memberId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Update the status display
                        if (data.has_active_subscription) {
                            statusCell.innerHTML = '<span class="px-2 py-1 rounded-full text-xs text-white bg-green-500">Active</span>';
                        } else {
                            statusCell.innerHTML = '<span class="px-2 py-1 rounded-full text-xs text-white bg-red-500">Inactive</span>';
                        }
                    }
                })
                .catch(error => console.error('Error updating status:', error));
        });
    }
    
    // Initialize status updates if we're on the members page
    if (document.querySelector('table') && 
        (window.location.href.includes('manage-members') || 
         document.title.toLowerCase().includes('member'))) {
        setupStatusUpdates();
    }
});
