<?php
// Simple test script to verify member search functionality
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Member Search</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-2xl font-bold mb-6">Member Search Test</h1>
        
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Search Form</h2>
            <div class="relative">
                <input type="text" id="searchInput" 
                       class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Type to search members...">
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Search Results</h2>
            <div id="searchResults" class="border border-gray-300 rounded-md p-4 min-h-32">
                <p class="text-gray-500">Results will appear here...</p>
            </div>
        </div>

        <div>
            <h2 class="text-xl font-semibold mb-2">Debug Information</h2>
            <div id="debugInfo" class="border border-gray-300 rounded-md p-4">
                <pre id="requestDetails">No requests made yet</pre>
                <pre id="responseDetails">No responses received yet</pre>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');
            const requestDetails = document.getElementById('requestDetails');
            const responseDetails = document.getElementById('responseDetails');
            let searchTimeout;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const searchTerm = this.value.trim();
                
                // Update request details
                requestDetails.textContent = `Search term: "${searchTerm}"`;
                
                if (!searchTerm) {
                    searchResults.innerHTML = '<p class="text-gray-500">Type to search...</p>';
                    return;
                }

                // Show loading state
                searchResults.innerHTML = '<p class="text-blue-500"><i class="fas fa-spinner fa-spin"></i> Searching...</p>';
                
                // Debounce the search
                searchTimeout = setTimeout(() => {
                    // Make the request to search-members.php
                    const url = `./functions/search-members.php?term=${encodeURIComponent(searchTerm)}`;
                    requestDetails.textContent = `Request URL: ${url}\nSearch term: "${searchTerm}"`;
                    
                    fetch(url)
                        .then(response => {
                            const status = `Response status: ${response.status} ${response.statusText}`;
                            responseDetails.textContent = status;
                            return response.json();
                        })
                        .then(data => {
                            // Display the response data
                            responseDetails.textContent += `\n\nResponse data: ${JSON.stringify(data, null, 2)}`;
                            
                            if (!Array.isArray(data)) {
                                searchResults.innerHTML = '<p class="text-red-500">Invalid response format</p>';
                                return;
                            }
                            
                            if (data.length === 0) {
                                searchResults.innerHTML = '<p class="text-gray-500">No members found</p>';
                                return;
                            }
                            
                            // Build results HTML
                            const resultsHtml = data.map(member => `
                                <div class="mb-3 p-3 bg-gray-50 border border-gray-200 rounded-md">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold mr-3">
                                            ${member.initials}
                                        </div>
                                        <div>
                                            <div class="font-medium">${member.name}</div>
                                            <div class="text-sm text-gray-500">${member.email}</div>
                                            <div class="text-sm text-gray-500">Program: ${member.program || 'None'}</div>
                                            <div class="text-sm text-gray-500">Subscription: ${member.subscription}</div>
                                        </div>
                                    </div>
                                </div>
                            `).join('');
                            
                            searchResults.innerHTML = resultsHtml;
                        })
                        .catch(error => {
                            searchResults.innerHTML = `<p class="text-red-500">Error: ${error.message}</p>`;
                            responseDetails.textContent += `\n\nError: ${error.message}`;
                        });
                }, 300);
            });
        });
    </script>
</body>
</html>
