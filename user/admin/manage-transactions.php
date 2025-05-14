<!-- Add this somewhere in the head section of your file, before the closing </head> tag -->

<!-- Include the filter-transactions.js script -->
<script src="../../user/admin/filter-transactions.js" defer></script>

<!-- Add this to the head section, before the closing </head> tag -->
<script src="../../user/admin/filter-transactions.js" defer></script>

<!-- Replace any existing filter handling script with this line -->
<script>
// Remove any existing filter handling code as our external script will handle it
document.addEventListener('DOMContentLoaded', function() {
    console.log('Using external filter-transactions.js for filter handling');
});
</script>

<!-- Add this to the end of the file, just before the closing </body> tag -->
<script src="../../user/admin/filter-transactions.js"></script>
<script>
    // Initialize with full data on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we have filter form
        const filterForm = document.getElementById('filterForm');
        const resetBtn = document.getElementById('resetFiltersBtn');
        
        if (filterForm && resetBtn) {
            console.log('Using enhanced filter handler for transactions');
        }
    });
</script>

<!-- Add this before the closing </body> tag -->
<script src="../../user/admin/filter-transactions.js"></script>

<!-- Optional: Add this to initialize toast notifications if not already present -->
<script>
    // Simple toast notification if showToast function doesn't already exist
    if (typeof showToast !== 'function') {
        window.showToast = function(message, isSuccess) {
            const toastContainer = document.getElementById('toastContainer') || 
                createToastContainer();
                
            const toast = document.createElement('div');
            toast.className = `px-4 py-3 rounded shadow-lg mb-3 transition-all duration-500 transform translate-y-0 opacity-100 ${
                isSuccess ? 'bg-green-500' : 'bg-red-500'
            } text-white`;
            toast.innerHTML = message;
            
            toastContainer.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.replace('opacity-100', 'opacity-0');
                toast.classList.add('translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        };
        
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'fixed bottom-4 right-4 z-50 flex flex-col';
            document.body.appendChild(container);
            return container;
        }
    }
</script>
