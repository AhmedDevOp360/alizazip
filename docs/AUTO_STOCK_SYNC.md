# Automatic Product Stock Synchronization

## Overview

This feature automatically synchronizes product stock quantities by finding and fixing mismatches between calculated stock and actual stock in the database. It replaces the manual "Fix" button process with an automated cron job that runs every 30 minutes.

## How It Works

The system compares the calculated stock (based on all transactions) with the actual stock stored in the `variation_location_details` table. When a mismatch is detected, it automatically updates the stock to match the calculated value.

### Stock Calculation Formula

The calculated stock is determined by:
```
Total Stock = Opening Stock + Purchases + Purchase Transfers + Sell Returns + Manufactured
             - (Sales + Sell Transfers + Stock Adjustments + Purchase Returns + Combined Purchase Returns + Ingredients Used)
```

## Console Command

### Command Name
```bash
php artisan pos:autoSyncProductStock
```

### Options
- `--business_id`: Sync only a specific business
- `--location_id`: Sync only a specific location

### Examples

```bash
# Sync all businesses and locations
php artisan pos:autoSyncProductStock

# Sync only business ID 1
php artisan pos:autoSyncProductStock --business_id=1

# Sync only location ID 2 in business ID 1
php artisan pos:autoSyncProductStock --business_id=1 --location_id=2
```

## Scheduling

The command is automatically scheduled to run every 30 minutes in both live and demo environments.

### Manual Execution

You can run the command manually at any time:

```bash
# Run the command
php artisan pos:autoSyncProductStock

# Check the schedule
php artisan schedule:list

# Run all scheduled commands
php artisan schedule:run
```

## Logging

The command logs its activities to the Laravel log file. You can find logs with the following keys:
- `AutoSyncProductStock completed` - Successful completion
- `AutoSyncProductStock failed` - Error occurred

### Log Information
- Total mismatches fixed
- Total products checked
- Number of businesses processed
- Any filters applied (business_id, location_id)

## Monitoring

To monitor the automatic sync process:

1. Check the Laravel logs for completion messages
2. Use the command with verbose output to see real-time progress
3. Monitor the `variation_location_details` table for stock updates

## Troubleshooting

### Common Issues

1. **Memory Limit**: The command sets memory limit to 512M and max execution time to 0
2. **Large Datasets**: For businesses with many products, the process may take time
3. **Database Locks**: The command uses transactions to ensure data consistency

### Manual Verification

To verify the sync worked correctly:

1. Go to Reports > Product Stock Details
2. Check if the "Fix" buttons are still needed
3. Compare calculated vs actual stock values

### Rollback

If needed, you can temporarily disable the automatic sync by commenting out the schedule line in `app/Console/Kernel.php`:

```php
// $schedule->command('pos:autoSyncProductStock')->everyThirtyMinutes();
```

## Performance Considerations

- The command processes all businesses and locations by default
- Use business_id and location_id options to limit scope for testing
- The process is optimized with database queries to minimize memory usage
- Consider running during off-peak hours for large datasets

## Security

- The command runs with the same permissions as the web application
- No authentication is required as it's a console command
- Ensure the server has proper file permissions for logging 