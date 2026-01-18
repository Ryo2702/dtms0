# Notification System Update Summary

## Overview
The notification system has been completely refactored to:
1. Support transaction status-based notifications
2. Allow all statuses to receive notifications
3. Replace DaisyUI with Tailwind CSS for styling
4. Enhance controller-side filtering and logic

---

## Changes Made

### 1. **Database Migration Updates**

#### File: `database/migrations/2025_10_26_102455_create_notifications_table.php`
- Added `transaction_status` column (nullable string)
- Added index on `[user_id, transaction_status]` for optimized queries

#### File: `database/migrations/2026_01_18_101913_add_transaction_status_to_notifications_table.php` (New)
- Migration to add `transaction_status` column to existing notifications table
- Includes proper down migration for rollback

### 2. **Model Updates**

#### File: `app/Models/Notification.php`
- Updated `$fillable` array to include `transaction_status` field
- No changes to relationships or casts

### 3. **Helper Class Enhancements**

#### File: `app/Helpers/NotificationHelper.php`
**New Methods:**
- `send()` - Enhanced to accept optional `$transactionStatus` parameter
- `sendtoMultiple()` - Enhanced to broadcast `$transactionStatus` to multiple users
- `sendToAllByUserType()` - NEW: Send notifications to all users of a specific type
- `sendByTransactionStatus()` - NEW: Smart routing based on transaction status
- `getRecipientsForTransactionStatus()` - NEW: Private helper to determine notification recipients

**Smart Routing Logic:**
- **pending/in_progress**: Sends to assigned staff and current department heads
- **approved/completed**: Sends to origin creator and their department heads
- **rejected/returned_to_creator**: Sends to origin creator only
- **cancelled**: Sends to all involved parties (creator + all reviewers)
- **default**: Sends to all system users

### 4. **Controller Updates**

#### File: `app/Http/Controllers/Notification/NotificationController.php`

**Enhanced `getNotifications()` method:**
- Added query parameter `status` for filtering by transaction status
- Returns additional field: `transaction_status` in notification response
- New response field: `unread_by_status` - counts unread notifications per status

**Example Request:**
```
GET /api/notifications/list?status=pending
```

**Response Structure:**
```json
{
  "success": true,
  "notifications": [
    {
      "id": 1,
      "document_id": 123,
      "transaction_status": "pending",
      "type": "pending",
      "title": "...",
      "message": "...",
      "is_read": false,
      "created_at": "2026-01-18T...",
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

### 5. **UI/Frontend Updates**

#### File: `resources/views/layouts/app.blade.php`

**Removed DaisyUI Classes:**
- Replaced `btn btn-ghost btn-circle` with Tailwind equivalents
- Replaced `badge badge-xs badge-error` with Tailwind badge styles
- Replaced `bg-primary/10` with `bg-blue-50`
- Replaced `opacity-*` with explicit color utilities
- Replaced `rounded-box` with `rounded-lg`
- Removed all `@apply` patterns in favor of inline Tailwind classes

**New Tailwind Styling:**
- Notification bell: `inline-flex items-center justify-center p-2 text-white hover:bg-blue-700 rounded-full`
- Notification badge: `inline-block px-2 py-1 text-xs font-bold text-white bg-red-600 rounded-full`
- Dropdown container: `bg-white text-gray-900 rounded-lg shadow-xl border border-gray-200`
- Notification items: `hover:bg-gray-100 cursor-pointer transition` with `bg-blue-50` for unread
- Icon containers: Color-coded backgrounds (green, red, yellow, blue, etc.)

**Updated `getNotificationIconColor()` function:**
```javascript
// Old (DaisyUI)
'approved': 'bg-success text-success-content'

// New (Tailwind)
'approved': 'bg-green-100 text-green-600'
```

**Colors Used:**
| Type | Tailwind Classes |
|------|-----------------|
| approved | bg-green-100 text-green-600 |
| rejected | bg-red-100 text-red-600 |
| canceled | bg-yellow-100 text-yellow-600 |
| pending | bg-blue-100 text-blue-600 |
| received | bg-indigo-100 text-indigo-600 |
| completed | bg-green-100 text-green-600 |
| overdue | bg-orange-100 text-orange-600 |

### 6. **Service Layer Updates**

#### File: `app/Services/Document/DocumentWorkflowService.php`

**Updated all notification calls** to include transaction status:

```php
// Before
NotificationHelper::send($user, 'pending', 'Title', 'Message', $id);

// After
NotificationHelper::send($user, 'pending', 'Title', 'Message', $id, null, 'pending');
```

**Updated methods:**
- `sendForReview()` - Status: `pending`
- `forwardReview()` - Status: `in_progress`
- `completeReview()` - Status: `approved`
- `rejectReview()` - Status: `rejected`
- `cancelReview()` - Status: `cancelled`

---

## Database Migration Instructions

To apply all changes to your database:

```bash
# Run all pending migrations
php artisan migrate

# Or specifically for notifications
php artisan migrate --path=database/migrations/2025_10_26_102455_create_notifications_table.php
php artisan migrate --path=database/migrations/2026_01_18_101913_add_transaction_status_to_notifications_table.php
```

### Rollback (if needed)
```bash
php artisan migrate:rollback
```

---

## API Usage Examples

### Get All Notifications
```javascript
fetch('/api/notifications/list', {
  method: 'GET',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken
  }
})
.then(res => res.json())
.then(data => console.log(data));
```

### Get Notifications by Status
```javascript
fetch('/api/notifications/list?status=pending', {
  method: 'GET',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken
  }
})
.then(res => res.json())
.then(data => console.log(data));
```

### Mark as Read
```javascript
fetch('/api/notifications/mark-read', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken
  },
  body: JSON.stringify({ notification_id: 1 })
})
.then(res => res.json())
.then(data => console.log(data));
```

### Mark All as Read
```javascript
fetch('/api/notifications/mark-all-read', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': csrfToken
  }
})
.then(res => res.json())
.then(data => console.log(data));
```

---

## Testing Checklist

- [ ] Database migrations run without errors
- [ ] Notification dropdown displays with Tailwind styling
- [ ] Badge colors match transaction types
- [ ] Notifications can be filtered by status
- [ ] Mark as read functionality works
- [ ] Mark all as read functionality works
- [ ] Notifications appear for all transaction statuses
- [ ] Smart routing sends notifications to correct users based on status
- [ ] No console errors in browser dev tools

---

## Technical Notes

1. **Backwards Compatibility**: The `transaction_status` parameter is optional in all notification methods, maintaining backwards compatibility with existing code.

2. **Performance**: New index on `[user_id, transaction_status]` optimizes queries for status-based filtering.

3. **Smart Routing**: The `getRecipientsForTransactionStatus()` method automatically determines who should receive notifications based on transaction state, ensuring the right people get notified at the right time.

4. **Tailwind Migration**: All DaisyUI component classes have been replaced with pure Tailwind utilities, providing better customization and lighter CSS output.

---

## Future Enhancements

Possible improvements for future iterations:
- Real-time notifications using WebSockets/Pusher
- Email notification integration
- Notification preference settings per user
- Notification history/archive
- Bulk notification operations
- Advanced filtering and search
