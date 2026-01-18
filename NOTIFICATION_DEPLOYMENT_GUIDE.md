# Notification System Migration Checklist & Deploy Guide

## Pre-Deployment Checklist

### Code Review
- [x] All PHP files have correct syntax
- [x] Migration files properly structured
- [x] Helper methods implemented correctly
- [x] Controller methods enhanced properly
- [x] Service layer updated completely
- [x] View file updated with Tailwind classes
- [x] No DaisyUI classes in notification dropdown

### File Verification
```bash
# Run these commands to verify
php -l app/Models/Notification.php
php -l app/Helpers/NotificationHelper.php
php -l app/Http/Controllers/Notification/NotificationController.php
php -l app/Services/Document/DocumentWorkflowService.php
php -l database/migrations/2025_10_26_102455_create_notifications_table.php
php -l database/migrations/2026_01_18_101913_add_transaction_status_to_notifications_table.php
```

Expected output: `No syntax errors detected` for each file

---

## Deployment Steps

### Step 1: Backup Database (CRITICAL)
```bash
# Create a database backup before migration
mysqldump -u your_user -p your_database > backup_$(date +%Y%m%d_%H%M%S).sql

# Or use Laravel backup
php artisan backup:run
```

### Step 2: Run Migrations
```bash
# Option A: Run all pending migrations
php artisan migrate

# Option B: Run specific migration files
php artisan migrate --path=database/migrations/2025_10_26_102455_create_notifications_table.php
php artisan migrate --path=database/migrations/2026_01_18_101913_add_transaction_status_to_notifications_table.php
```

### Step 3: Verify Migration Success
```bash
# Check migration status
php artisan migrate:status

# Check table structure
php artisan tinker
> Schema::getColumnListing('notifications')

# Should output:
# [
#   "id",
#   "user_id",
#   "document_id",
#   "transaction_status",  // ← Should be present
#   "type",
#   "message",
#   "title",
#   "is_read",
#   "read_at",
#   "data",
#   "created_at",
#   "updated_at"
# ]
```

### Step 4: Verify Table Indices
```bash
php artisan tinker
> Schema::getIndexes('notifications')

# Should include:
# - idx_user_read (user_id, is_read)
# - idx_user_created (user_id, created_at)
# - idx_user_status (user_id, transaction_status) ← NEW
```

### Step 5: Clear Application Caches
```bash
# Clear all caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Or use fresh command
php artisan cache:clear --tag=all
```

### Step 6: Test Notification System
```bash
# Test 1: Create test notification
php artisan tinker

$user = App\Models\User::find(1);
App\Helpers\NotificationHelper::send(
    $user,
    'test',
    'Test Notification',
    'This is a test notification',
    null,
    null,
    'pending'
);

# Test 2: Retrieve notification
$notification = App\Models\Notification::latest()->first();
echo $notification->transaction_status; // Should output: pending

# Test 3: Test API endpoint
exit
```

### Step 7: Browser Testing
- [ ] Open app in browser
- [ ] Navigate to any page with notifications
- [ ] Click notification bell icon
- [ ] Verify dropdown appears
- [ ] Verify styling looks correct (no DaisyUI classes)
- [ ] Check browser console (F12) for errors
- [ ] Test filtering by status
- [ ] Test mark as read functionality

### Step 8: Full Integration Test
```bash
# Create a test transaction to verify notifications trigger
php artisan tinker

$user = App\Models\User::find(1);
$reviewer = App\Models\User::find(2);

// Simulate sending document for review
$service = new App\Services\Document\DocumentWorkflowService();
// ... test the service methods

# Verify notifications were created with correct status
App\Models\Notification::where('user_id', 2)->latest()->get();
```

---

## Rollback Steps (If Needed)

### Immediate Rollback
```bash
# Rollback the last migration batch
php artisan migrate:rollback

# Or rollback specific migration
php artisan migrate:rollback --path=database/migrations/2026_01_18_101913_add_transaction_status_to_notifications_table.php
```

### Full Rollback to Before Update
```bash
# Rollback multiple batches
php artisan migrate:rollback --steps=2

# Or rollback to specific batch
php artisan migrate:reset
```

### Restore from Backup
```bash
# Restore database from backup
mysql -u your_user -p your_database < backup_20260118_101234.sql

# Revert code changes
git revert HEAD  # If using git
```

---

## Post-Deployment Verification

### Database Verification
```sql
-- Check notifications table exists
SHOW TABLES LIKE 'notifications';

-- Check columns
DESCRIBE notifications;

-- Check indices
SHOW INDEX FROM notifications;

-- Verify data integrity
SELECT COUNT(*) FROM notifications;
SELECT DISTINCT transaction_status FROM notifications;
```

### Application Verification
```bash
# Test notification creation
php artisan tinker
> $notif = App\Models\Notification::latest()->first();
> echo $notif->transaction_status;

# Test API endpoint
> Http::get('http://localhost/api/notifications/list')->json();

# Test smart routing
> $trans = App\Models\Transaction::find(1);
> App\Helpers\NotificationHelper::sendByTransactionStatus($trans, 'test', 'Test', 'Message');
```

### Browser Console Check
Press F12 in browser, check Console tab:
- [ ] No red errors
- [ ] No missing resource warnings
- [ ] Notification API calls successful
- [ ] Lucide icons loading correctly

---

## Troubleshooting Common Issues

### Issue: Migration fails with "Column already exists"
**Cause:** Table already has transaction_status column
**Solution:**
```bash
php artisan tinker
> Schema::hasColumn('notifications', 'transaction_status')
# If true, you can skip the second migration or delete it

# Or mark it as complete without running:
php artisan migrate --pretend --path=database/migrations/2026_01_18_101913_add_transaction_status_to_notifications_table.php
```

### Issue: "SQLSTATE[42S21]: Column already exists"
**Solution:**
```bash
# Check current migration status
php artisan migrate:status

# If migration is marked as pending but column exists:
# Modify the migration to check if column exists first
# Or manually update migration history:
php artisan tinker
> DB::table('migrations')->where('migration', '2026_01_18_101913_add_transaction_status_to_notifications_table')->delete();
> php artisan migrate
```

### Issue: API endpoint returns error
**Cause:** Code not fully deployed
**Solution:**
```bash
# Verify files are updated
grep -n "transaction_status" app/Http/Controllers/Notification/NotificationController.php

# Clear autoloader cache
composer dump-autoload

# Restart queue and cache
php artisan cache:clear
php artisan queue:restart
```

### Issue: Styling looks broken in dropdown
**Cause:** DaisyUI still loaded, Tailwind not compiled
**Solution:**
```bash
# Recompile assets
npm run dev  # Development
npm run build  # Production

# Clear browser cache
# Ctrl+Shift+Delete (Chrome/Edge)
# Cmd+Shift+Delete (Mac)
# Then refresh page
```

### Issue: Notifications not appearing for some users
**Cause:** Smart routing rules not matching user role
**Solution:**
```bash
# Check user role and department
php artisan tinker
> $user = App\Models\User::find(1);
> echo $user->type;  // Should be: Head, Staff, etc.
> echo $user->department_id;

# Check transaction status
> $trans = App\Models\Transaction::find(1);
> echo $trans->transaction_status;
> echo $trans->assign_staff_id;
```

---

## Performance Monitoring

### Monitor Notification Table Growth
```sql
-- Check table size
SELECT 
    TABLE_NAME,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE TABLE_NAME = 'notifications';

-- Check index usage
SELECT 
    OBJECT_SCHEMA,
    OBJECT_NAME,
    COUNT_READ,
    COUNT_WRITE
FROM performance_schema.table_io_waits_summary_by_index_usage
WHERE OBJECT_NAME = 'notifications';
```

### Query Performance
```sql
-- Measure query performance
EXPLAIN SELECT * FROM notifications WHERE user_id = 1 AND is_read = false;
EXPLAIN SELECT * FROM notifications WHERE user_id = 1 AND transaction_status = 'pending';

-- Should use indices (check "key" column for index name)
```

### Monitor API Endpoints
```bash
# Check Laravel logs for slow queries
tail -f storage/logs/laravel.log | grep "query time"

# Monitor API response times
php artisan tinker
> DB::enableQueryLog();
> $notifs = Notification::where('user_id', 1)->get();
> DB::getQueryLog();
```

---

## Maintenance Tasks

### Regular Database Optimization
```bash
# Schedule this monthly
php artisan tinker
> DB::statement('OPTIMIZE TABLE notifications');
```

### Clean Up Old Notifications (Optional)
```bash
php artisan tinker

# Delete notifications older than 90 days
> App\Models\Notification::where('created_at', '<', now()->subDays(90))->delete();

# Delete all read notifications older than 30 days
> App\Models\Notification::where('is_read', true)->where('created_at', '<', now()->subDays(30))->delete();
```

### Archive Old Notifications (Recommended)
```bash
# Create an archive table first
php artisan make:migration create_notifications_archive_table

# Then archive periodically
php artisan tinker
> DB::table('notifications_archive')->insertUsing(
>     ['*'],
>     DB::table('notifications')->where('created_at', '<', now()->subMonths(6))
> );
```

---

## Testing Checklist

### Unit Tests
```bash
php artisan test --filter NotificationTest
```

### Feature Tests
- [ ] Create transaction → notification sent to correct user
- [ ] Forward transaction → notification status changed
- [ ] Approve transaction → notification sent to creator
- [ ] Reject transaction → notification sent to creator only
- [ ] Cancel transaction → notification sent to all parties

### Integration Tests
- [ ] API /notifications/list endpoint
- [ ] API /notifications/list?status=pending endpoint
- [ ] API /notifications/counts endpoint
- [ ] API /notifications/mark-read endpoint
- [ ] API /notifications/mark-all-read endpoint

### UI Tests
- [ ] Notification dropdown opens/closes
- [ ] Colors match notification types
- [ ] Unread badge displays correctly
- [ ] Mark as read works
- [ ] Click notification navigates to document
- [ ] Responsive on mobile devices

---

## Deployment Checklist

Before going live, complete these steps:

- [ ] Database backed up
- [ ] All PHP syntax checked
- [ ] Migrations verified
- [ ] Test environment validated
- [ ] Staging environment passed all tests
- [ ] Team reviewed changes
- [ ] Documentation complete
- [ ] Rollback plan prepared
- [ ] Monitoring configured
- [ ] Support team notified
- [ ] Users notified of changes
- [ ] Performance baseline recorded

---

## Post-Deployment Tasks

### Day 1 (After deployment)
- [ ] Monitor error logs for issues
- [ ] Verify all users can see notifications
- [ ] Check database performance
- [ ] Monitor API response times
- [ ] Gather user feedback

### Week 1
- [ ] Analyze notification usage patterns
- [ ] Verify smart routing working correctly
- [ ] Check database growth rate
- [ ] Review performance metrics
- [ ] Fine-tune cache settings if needed

### Monthly
- [ ] Review notification delivery statistics
- [ ] Clean up old notifications
- [ ] Optimize database indices
- [ ] Review and update documentation
- [ ] Plan future enhancements

---

## Support Resources

### Quick Commands Reference
```bash
# Check migration status
php artisan migrate:status

# View table structure
php artisan tinker > Schema::getColumnListing('notifications')

# Test notification
php artisan tinker
> App\Helpers\NotificationHelper::send(User::first(), 'test', 'Test', 'Test message', null, null, 'pending');

# Check API
curl -X GET http://localhost/api/notifications/list

# Clear all caches
php artisan cache:clear && php artisan config:cache
```

### Documentation Files
- NOTIFICATION_SYSTEM_UPDATE.md - Detailed technical changes
- NOTIFICATION_QUICK_REFERENCE.md - Quick developer guide
- NOTIFICATION_BEFORE_AFTER.md - Code comparisons
- NOTIFICATION_IMPLEMENTATION_SUMMARY.md - Overview

### Getting Help
1. Review documentation files
2. Check error logs: `storage/logs/laravel.log`
3. Test in tinker: `php artisan tinker`
4. Review code comments in updated files
5. Check database schema: `php artisan tinker > Schema::getColumnListing('notifications')`

---

## Sign-Off

**Deployed By:** [Your Name]  
**Date:** [Deployment Date]  
**Environment:** [Production/Staging]  
**Status:** [Success/Issues]  
**Notes:** [Any additional notes]

---

**Version:** 1.0  
**Last Updated:** January 18, 2026  
**Next Review:** [Scheduled Date]
