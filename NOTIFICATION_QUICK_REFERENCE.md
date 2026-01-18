# Notification System - Quick Reference Guide

## Overview
The notification system now supports:
✅ Transaction status-based routing
✅ All statuses receive notifications  
✅ Tailwind CSS styling (no more DaisyUI)
✅ Smart recipient determination based on transaction state
✅ Status-filtered API endpoints

---

## Key Components

### 1. Notification Model
**Location:** `app/Models/Notification.php`

**Fields:**
- `id` - Primary key
- `user_id` - Who receives the notification
- `document_id` - Related document (nullable)
- `transaction_status` - Transaction status at time of notification (pending, in_progress, approved, rejected, cancelled)
- `type` - Notification type (approved, rejected, pending, received, etc.)
- `title` - Notification title
- `message` - Notification message
- `is_read` - Read status
- `read_at` - When marked as read
- `data` - JSON metadata
- `created_at`, `updated_at` - Timestamps

---

## Notification Helper Methods

### `send($user, $type, $title, $message, $docId, $data, $status)`
Send notification to a single user.

```php
use App\Helpers\NotificationHelper;
use App\Models\User;

$user = User::find(1);
NotificationHelper::send(
    $user,
    'approved',
    'Document Approved',
    'Your document has been reviewed and approved.',
    123,  // document ID
    null, // metadata
    'approved'  // transaction status
);
```

### `sendtoMultiple($userIds, $type, $message, $title, $docId, $data, $status)`
Send notification to multiple users at once.

```php
NotificationHelper::sendtoMultiple(
    [1, 2, 3],  // User IDs
    'pending',
    'New Document Received',
    'You have a new document to review',
    123,
    null,
    'pending'
);
```

### `sendToAllByUserType($userType, $type, $title, $message, $docId, $data, $status)`
Send notification to all users of a specific type (e.g., all Heads).

```php
NotificationHelper::sendToAllByUserType(
    'Head',
    'system',
    'System Update',
    'A new document workflow has been added',
    null,
    null,
    'pending'
);
```

### `sendByTransactionStatus($transaction, $type, $title, $message, $data)`
Smart routing - automatically determines recipients based on transaction status.

```php
use App\Models\Transaction;

$transaction = Transaction::find(1);
NotificationHelper::sendByTransactionStatus(
    $transaction,
    'status_update',
    'Transaction Status Changed',
    'Your transaction status has been updated',
    null
);
```

---

## Smart Routing Rules

The system automatically routes notifications based on transaction status:

### Status: `pending`
**Recipients:** Assigned staff + Current department heads
**Use Case:** Initial document submission

### Status: `in_progress`  
**Recipients:** Assigned staff + Current department heads
**Use Case:** Document being reviewed

### Status: `approved` / `completed`
**Recipients:** Origin creator + Their department heads
**Use Case:** Document approved and ready

### Status: `rejected`
**Recipients:** Origin creator only
**Use Case:** Document needs corrections

### Status: `cancelled`
**Recipients:** Origin creator + All involved reviewers
**Use Case:** Document process cancelled

### Default
**Recipients:** All system users
**Use Case:** System-wide announcements

---

## API Endpoints

### GET `/api/notifications/list`
Get notifications for current user.

**Query Parameters:**
- `status` (optional) - Filter by transaction status

**Response:**
```json
{
  "success": true,
  "notifications": [
    {
      "id": 1,
      "document_id": 123,
      "transaction_status": "pending",
      "type": "pending",
      "title": "New Document for Review",
      "message": "You have a new document...",
      "is_read": false,
      "created_at": "2026-01-18T10:30:00Z",
      "read_at": null
    }
  ],
  "unread_count": 5,
  "unread_counts": {
    "pending": 2,
    "approved": 1,
    "rejected": 2
  },
  "unread_by_status": {
    "pending": 3,
    "in_progress": 2
  }
}
```

### GET `/api/notifications/counts`
Get notification counts for current user.

**Response:**
```json
{
  "success": true,
  "unread_count": 5,
  "unread_counts": {
    "pending": 2,
    "approved": 1,
    "rejected": 2
  }
}
```

### POST `/api/notifications/mark-read`
Mark a notification as read.

**Body:**
```json
{
  "notification_id": 1
}
```

### POST `/api/notifications/mark-all-read`
Mark all notifications as read for current user.

---

## UI Components

### Notification Bell
Located in the top-right navbar.
- Shows red badge with count when unread notifications exist
- Click to open/close dropdown
- Auto-updates every 30 seconds
- Updates when page becomes visible

### Notification Dropdown
Displays recent notifications (50 max).
- Tailwind-styled with blue/gray color scheme
- Unread notifications highlighted with blue background
- Color-coded icons based on notification type:
  - Green: approved, completed
  - Red: rejected
  - Yellow: canceled
  - Blue: pending
  - Indigo: received
  - Orange: overdue

### Notification Item
Each notification shows:
- Icon with status color
- Title
- Message preview
- Time ago (e.g., "5m ago")
- "New" badge if unread
- Click to view full notification

---

## Migration Files

### Initial Creation
**File:** `database/migrations/2025_10_26_102455_create_notifications_table.php`
- Creates notifications table
- Includes transaction_status column from the start
- Creates indices for performance

### Add Transaction Status (Existing Tables)
**File:** `database/migrations/2026_01_18_101913_add_transaction_status_to_notifications_table.php`
- Adds transaction_status column if upgrading
- Adds performance index
- Includes proper rollback

---

## Testing

### Manual Testing
1. Create a transaction in status "pending"
   - Verify notification sent to assigned staff and heads
   
2. Forward transaction to another reviewer
   - Verify notification sent with "in_progress" status
   
3. Approve transaction
   - Verify notification sent to origin creator
   
4. Reject transaction
   - Verify notification sent only to origin creator
   
5. Cancel transaction
   - Verify notification sent to all involved parties

### Testing API
```bash
# Get all notifications
curl -X GET http://localhost/api/notifications/list \
  -H "X-Requested-With: XMLHttpRequest"

# Filter by status
curl -X GET "http://localhost/api/notifications/list?status=pending" \
  -H "X-Requested-With: XMLHttpRequest"

# Get counts
curl -X GET http://localhost/api/notifications/counts \
  -H "X-Requested-With: XMLHttpRequest"

# Mark as read
curl -X POST http://localhost/api/notifications/mark-read \
  -H "Content-Type: application/json" \
  -d '{"notification_id": 1}'

# Mark all as read
curl -X POST http://localhost/api/notifications/mark-all-read \
  -H "Content-Type: application/json"
```

---

## Tailwind CSS Classes Used

### Notification Dropdown Container
```css
bg-white text-gray-900 rounded-lg shadow-xl border border-gray-200
```

### Notification Item (Unread)
```css
bg-blue-50 hover:bg-gray-100
```

### Icon Containers
- Approved: `bg-green-100 text-green-600`
- Rejected: `bg-red-100 text-red-600`
- Canceled: `bg-yellow-100 text-yellow-600`
- Pending: `bg-blue-100 text-blue-600`
- Received: `bg-indigo-100 text-indigo-600`
- Overdue: `bg-orange-100 text-orange-600`

### Badge (New indicator)
```css
inline-block px-2 py-0.5 text-xs font-semibold text-white bg-blue-500 rounded
```

---

## Troubleshooting

### Notifications not appearing
1. Check `unread_count` in API response
2. Verify transaction status was set correctly
3. Check browser console for JavaScript errors
4. Verify user permissions match recipient criteria

### Wrong recipients getting notifications
1. Review the smart routing rules above
2. Check transaction status is correct
3. Verify assigned staff and department assignments
4. Check user roles (Head, Staff, etc.)

### Styling issues
1. Clear browser cache
2. Run `npm run dev` or `npm run build` to recompile assets
3. Check for conflicting Tailwind/DaisyUI classes
4. Verify Tailwind config includes all paths

### Database issues
1. Run migrations: `php artisan migrate`
2. Check migration status: `php artisan migrate:status`
3. Rollback if needed: `php artisan migrate:rollback`

---

## Performance Tips

1. **Indexes**: The table has indices on:
   - `[user_id, is_read]` - For "unread" queries
   - `[user_id, created_at]` - For "recent" queries
   - `[user_id, transaction_status]` - For status filtering

2. **Pagination**: API returns max 50 notifications
   - Consider adding pagination for large datasets

3. **Caching**: Notification counts update every 30 seconds
   - Adjust interval in `app.blade.php` if needed

---

## Files Modified

- `app/Models/Notification.php`
- `app/Helpers/NotificationHelper.php`
- `app/Http/Controllers/Notification/NotificationController.php`
- `app/Services/Document/DocumentWorkflowService.php`
- `database/migrations/2025_10_26_102455_create_notifications_table.php`
- `database/migrations/2026_01_18_101913_add_transaction_status_to_notifications_table.php`
- `resources/views/layouts/app.blade.php`

---

## Support

For issues or questions:
1. Check the NOTIFICATION_SYSTEM_UPDATE.md for detailed changes
2. Review the Quick Reference section above
3. Check database schema: `php artisan tinker` → `Schema::getColumnListing('notifications')`
4. Review notification records: `Notification::latest()->limit(10)->get()`
