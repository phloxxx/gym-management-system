# Subscription Renewal System Documentation

This document explains how to use the enhanced subscription renewal system that fixes issues with renewals not being reflected in the UI and database.

## Overview

The renewal system has been completely overhauled to ensure that:
1. Subscription renewals are properly saved to the database
2. The UI is updated in real-time to reflect the renewal
3. Previous active subscriptions are correctly deactivated
4. The process is resilient against network issues or page refreshes

## Implementation Files

The implementation consists of these core files:
- `functions/create-transaction.php` - Backend PHP script that processes renewals
- `functions/renewal-helper.js` - JavaScript utility functions for renewal operations
- `user/admin/subscription-renewal.js` - Admin interface integration for renewals

## How to Use

### Method 1: Using the Transaction Modal

When clicking a "Renew" button on a subscription, the system will:
1. Pre-fill the transaction modal with renewal information
2. Set hidden fields to indicate this is a renewal
3. Process the renewal when submitted

The transaction modal approach requires no additional code - just ensure your renewal buttons have these attributes:
- `data-member-id` - The ID of the member
- `data-sub-id` or `data-subscription-id` - The ID of the subscription
- CSS class `.renew-subscription-btn` or attribute `data-action="renew"` or `data-renew="true"`

Example:
```html
<button 
  class="renew-subscription-btn" 
  data-member-id="123" 
  data-sub-id="456">
    Renew
</button>
```

### Method 2: Using the Simple Dialog

If the transaction modal isn't available, the system will create a simple dialog that:
1. Allows selecting a payment method
2. Allows setting a start date (defaulted to today)
3. Processes the renewal directly without using the full form

This approach works automatically when the modal isn't found.

### Method 3: Programmatic Renewal

You can also trigger renewals programmatically:

```javascript
// First, make sure the renewal helper script is loaded
// This can be done by including it directly:
// <script src="../../functions/renewal-helper.js"></script>

// Or loading it dynamically:
function loadRenewalHelper() {
  const script = document.createElement('script');
  script.src = '../../functions/renewal-helper.js';
  document.body.appendChild(script);
}

// Then process a renewal
function renewSubscription(memberId, subscriptionId) {
  if (typeof processRenewal !== 'function') {
    console.error('Renewal helper not loaded');
    return;
  }

  processRenewal({
    memberId: memberId,
    subscriptionId: subscriptionId,
    paymentId: 1, // Default to cash (ID: 1)
    startDate: new Date().toISOString().split('T')[0],
    previousSubId: subscriptionId
  })
  .then(response => {
    if (response.success) {
      showToast('Subscription renewed!', true);
      updateUIWithRenewal(response.transaction);
    } else {
      showToast('Error: ' + response.message, false);
    }
  })
  .catch(error => {
    showToast('Error: ' + error.message, false);
  });
}
```

## Integration into Existing Pages

To add renewal capabilities to a page:

1. Include the subscription-renewal.js script:
```html
<script src="../../user/admin/subscription-renewal.js"></script>
```

2. Make sure your renewal buttons have the proper attributes:
```html
<button 
  class="renew-subscription-btn"
  data-member-id="<?php echo $member['MEMBER_ID']; ?>"
  data-sub-id="<?php echo $subscription['SUB_ID']; ?>">
    Renew
</button>
```

3. Ensure you have a showToast function for notifications, or the system will create one

## Troubleshooting

If renewals aren't working:

1. Check browser console for JavaScript errors
2. Look at PHP error logs for backend issues
3. Verify that user has permission to create transactions
4. Check database structure for any changes to required tables

## Technical Details

The renewal process:

1. Gets subscription duration to calculate end date
2. Updates database in a single transaction to ensure data consistency
3. Deactivates previous subscription
4. Creates new transaction record
5. Creates new subscription record
6. Updates UI in real-time with the new subscription details
