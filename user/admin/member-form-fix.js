/**
 * Specific fixes for the member form submission
 * This script prevents duplicate member creation
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Member form fix script loaded');
    
    // Find the add member form
    const addMemberForm = document.getElementById('addMemberForm');
    if (addMemberForm) {
        console.log('Found add member form, applying fixes');
        
        // Mark it as an AJAX form
        addMemberForm.dataset.ajax = 'true';
        
        // Add a hidden input for request ID if it doesn't exist
        if (!addMemberForm.querySelector('input[name="request_id"]')) {
            const requestIdField = document.createElement('input');
            requestIdField.type = 'hidden';
            requestIdField.name = 'request_id';
            requestIdField.value = 'req_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            addMemberForm.appendChild(requestIdField);
        }
        
        // Override the form submission
        const originalSubmit = addMemberForm.onsubmit;
        addMemberForm.onsubmit = function(e) {
            console.log('Intercepted member form submission');
            
            // Check if form is already being submitted
            if (addMemberForm.dataset.isSubmitting === 'true') {
                console.log('Preventing duplicate submission');
                e.preventDefault();
                return false;
            }
            
            // Mark form as being submitted
            addMemberForm.dataset.isSubmitting = 'true';
            
            // Disable the submit button
            const submitButton = addMemberForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.dataset.originalText = submitButton.innerHTML;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            }
            
            // Call the original submit handler if it exists
            if (typeof originalSubmit === 'function') {
                return originalSubmit.call(this, e);
            }
        };
        
        // For jQuery AJAX implementations
        if (typeof $ !== 'undefined') {
            $(addMemberForm).off('submit').on('submit', function(e) {
                e.preventDefault();
                
                // Check if form is already being submitted
                if (addMemberForm.dataset.isSubmitting === 'true') {
                    console.log('Preventing duplicate jQuery submission');
                    return false;
                }
                
                // Mark form as being submitted
                addMemberForm.dataset.isSubmitting = 'true';
                
                // Disable submit button
                $("#submitMemberBtn").prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                
                // Get form data with request ID
                var formData = new FormData(this);
                formData.append('request_id', addMemberForm.querySelector('input[name="request_id"]').value);
                
                // Send the AJAX request
                $.ajax({
                    url: "../../process/member_process.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        // Parse the response if it's a string
                        if (typeof response === "string") {
                            try {
                                response = JSON.parse(response);
                            } catch(e) {
                                console.error("Error parsing JSON response:", e);
                            }
                        }
                        
                        // Check if this was a duplicate request
                        if (response.isDuplicate) {
                            console.log('Duplicate request detected by server');
                        }
                        
                        // Handle success
                        if (response.status === "success") {
                            // Close the modal
                            $("#addMemberModal").modal("hide");
                            
                            // Show success message
                            Swal.fire({
                                icon: "success",
                                title: "Success!",
                                text: response.message || "Member added successfully!",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            // Reset the form
                            $("#addMemberForm")[0].reset();
                            
                            // Refresh members table without triggering another member creation
                            setTimeout(function() {
                                $.ajax({
                                    url: "../../process/get_members.php",
                                    type: "GET",
                                    success: function(data) {
                                        // Clear existing table data first
                                        $("#membersTable tbody").empty();
                                        
                                        // Then add the retrieved data
                                        $("#membersTable tbody").html(data);
                                    }
                                });
                            }, 500);
                        } else {
                            // Show error message
                            Swal.fire({
                                icon: "error",
                                title: "Error!",
                                text: response.message || "Something went wrong. Please try again."
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: "Failed to add member. Please try again later."
                        });
                    },
                    complete: function() {
                        // Re-enable the submit button
                        $("#submitMemberBtn").prop("disabled", false).html('Add Member');
                        
                        // Reset form submission status
                        addMemberForm.dataset.isSubmitting = 'false';
                        
                        // Generate new request ID
                        addMemberForm.querySelector('input[name="request_id"]').value = 
                            'req_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                    }
                });
                
                return false;
            });
        }
    }
});
