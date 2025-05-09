# Member Status Management System

This system automatically manages member statuses (active/inactive) based on their subscription status. When a member has an active subscription that hasn't expired, they are considered "Active". If they have no active subscriptions or all their subscriptions have expired, they are marked as "Inactive".

## How Member Status is Determined

The system follows these rules to determine a member's status:

1. A member is considered **Active** if they have at least one subscription that is:
   - Marked as active (`IS_ACTIVE = 1`) in the `member_subscription` table
   - Not expired (current date is on or before the subscription end date)

2. A member is considered **Inactive** if:
   - They have no active subscriptions, or
   - All their subscriptions have expired

## Automatic Status Updates

The system updates member statuses in three ways:

1. **Real-time updates**: Whenever a subscription is created, renewed, or deactivated
2. **On-demand updates**: Using the `updateMemberStatus()` function
3. **Scheduled updates**: Using the cron job

## Setting Up the Cron Job

The `update-member-statuses.php` script can be set up as a cron job to automatically update all member statuses on a regular schedule.

### For Linux/Unix Systems:

1. Edit the crontab:
   ```
   crontab -e
   ```

2. Add a line to run the script daily (e.g., at midnight):
   ```
   0 0 * * * php /path/to/gym-management-system/cron/update-member-statuses.php >> /path/to/gym-management-system/cron/logs/cron.log 2>&1
   ```

### For Windows Systems:

1. Open Task Scheduler
2. Create a new Basic Task
3. Set the trigger to Daily
4. For the Action, select "Start a program"
5. In the Program/script field, enter the path to your PHP executable
6. In the "Add arguments" field, enter the path to the script:
   ```
   C:\path\to\gym-management-system\cron\update-member-statuses.php
   ```

## Manual Update

You can also manually trigger the status update process by running the script directly:

```
php update-member-statuses.php
```

Or by accessing it via a web browser (with appropriate authentication):

```
http://your-domain.com/cron/update-member-statuses.php
```

## Troubleshooting

- Logs are stored in the `cron/logs/` directory
- Each run logs summary information to `member_status_updates.log`
- Check the logs if members aren't being properly updated

## Security Considerations

- Ensure the cron script is properly secured and not accessible to unauthorized users
- If accessed via web, add authentication to prevent unauthorized access 