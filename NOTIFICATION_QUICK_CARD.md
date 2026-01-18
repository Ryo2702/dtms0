# Notification System - Quick Reference Card

## 🎯 At A Glance

```
MODIFIED FILES:
├── app/Models/Notification.php
├── app/Helpers/NotificationHelper.php  ← 4 methods (new)
├── app/Http/Controllers/Notification/NotificationController.php
├── app/Services/Document/DocumentWorkflowService.php
├── database/migrations/2025_10_26_... (updated)
├── database/migrations/2026_01_18_... (NEW)
└── resources/views/layouts/app.blade.php (Tailwind)

NEW FEATURES:
✨ Transaction status tracking
✨ Smart recipient routing
✨ Status-based filtering
✨ Tailwind CSS styling
```

---

## 📦 Database Schema

```sql
notifications
├── id                    BIGINT PRIMARY KEY
├── user_id              BIGINT → users.id
├── document_id          BIGINT (nullable)
├── transaction_status   VARCHAR (nullable) ← NEW
├── type                 VARCHAR
├── title                VARCHAR
├── message              TEXT
├── is_read              BOOLEAN
├── read_at              TIMESTAMP
├── data                 JSON
├── created_at           TIMESTAMP
└── updated_at           TIMESTAMP

INDICES:
├── idx_user_read (user_id, is_read)
├── idx_user_created (user_id, created_at)
└── idx_user_status (user_id, transaction_status) ← NEW
```

---

## 🔌 Methods & Functions

### NotificationHelper Methods

```php
// 1. Single User
send(
    User $user,
    string $type,
    string $title,
    string $message,
    ?int $documentId,
    ?array $data = null,
    ?string $transactionStatus = null  ← NEW PARAM
)

// 2. Multiple Users (Batch)
sendtoMultiple(
    array $userIds,
    string $type,
    string $message,
    string $title,
    ?int $documentId,
    ?array $data = null,
    ?string $transactionStatus = null  ← NEW PARAM
)

// 3. All By User Type (Broadcast)
sendToAllByUserType(                   ← NEW METHOD
    string $userType,      // 'Head', 'Staff', etc.
    string $type,
    string $title,
    string $message,
    ?int $documentId,
    ?array $data = null,
    ?string $transactionStatus = null
)

// 4. Smart Routing (Auto Recipients)
sendByTransactionStatus(               ← NEW METHOD
    Transaction $transaction,
    string $type,
    string $title,
    string $message,
    ?array $data = null
)
// Automatically determines recipients based on:
// - Transaction status
// - User roles (Head, Staff)
// - Department assignments
// - Involved reviewers
```

---

## 📊 Smart Routing Logic

```
STATUS          → RECIPIENTS
─────────────────────────────────────────
pending         → assign_staff + heads
in_progress     → assign_staff + heads
approved        → creator + creator's heads
completed       → creator + creator's heads
rejected        → creator only
returned_to...  → creator only
cancelled       → creator + all reviewers
default         → all system users
```

---

## 🎨 Tailwind Color System

```javascript
{
  approved:   'bg-green-100 text-green-600',
  rejected:   'bg-red-100 text-red-600',
  canceled:   'bg-yellow-100 text-yellow-600',
  pending:    'bg-blue-100 text-blue-600',
  received:   'bg-indigo-100 text-indigo-600',
  completed:  'bg-green-100 text-green-600',
  overdue:    'bg-orange-100 text-orange-600'
}
```

---

## 🔗 API Endpoints

```
GET  /api/notifications/list
GET  /api/notifications/list?status=pending
GET  /api/notifications/counts
POST /api/notifications/mark-read
POST /api/notifications/mark-all-read
```

### Response Format

```json
{
  "success": true,
  "notifications": [
    {
      "id": 1,
      "document_id": 123,
      "transaction_status": "pending",  ← NEW
      "type": "pending",
      "title": "...",
      "message": "...",
      "is_read": false,
      "created_at": "...",
      "read_at": null
    }
  ],
  "unread_count": 5,
  "unread_counts": {...},
  "unread_by_status": {                 ← NEW
    "pending": 3,
    "approved": 2
  }
}
```

---

## 💻 Code Examples

### Example 1: Send to Single User with Status
```php
use App\Helpers\NotificationHelper;
use App\Models\User;

$user = User::find(1);
NotificationHelper::send(
    $user,
    'approved',
    'Document Approved',
    'Your document is ready.',
    123,
    null,
    'approved'
);
```

### Example 2: Smart Routing
```php
use App\Helpers\NotificationHelper;
use App\Models\Transaction;

$transaction = Transaction::find(1);

// Automatically routes to correct recipients
NotificationHelper::sendByTransactionStatus(
    $transaction,
    'status_update',
    'Status Changed',
    'Your transaction status has changed.',
    null
);
```

### Example 3: Broadcast to All Heads
```php
NotificationHelper::sendToAllByUserType(
    'Head',
    'announcement',
    'System Announcement',
    'New workflow has been added.',
    null,
    null,
    'pending'
);
```

### Example 4: JavaScript API Call
```javascript
// Get notifications filtered by status
fetch('/api/notifications/list?status=pending')
    .then(res => res.json())
    .then(data => {
        console.log('Pending:', data.notifications);
        console.log('Unread by status:', data.unread_by_status);
    });
```

---

## ✅ Deployment Checklist

```bash
# 1. Backup
mysqldump -u user -p db > backup.sql

# 2. Migrate
php artisan migrate

# 3. Verify
php artisan tinker
> Schema::getColumnListing('notifications')

# 4. Cache
php artisan cache:clear

# 5. Test
# Open app, test notification dropdown
```

---

## 🐛 Troubleshooting Quick Guide

| Issue | Solution |
|-------|----------|
| Migrations fail | Check if column exists: `Schema::hasColumn('notifications', 'transaction_status')` |
| API returns error | Clear cache: `php artisan cache:clear` |
| Styling broken | Recompile assets: `npm run dev` |
| Wrong recipients | Verify user roles and department IDs |
| Notifications missing | Check transaction_status value in DB |

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| NOTIFICATION_SYSTEM_UPDATE.md | Technical reference |
| NOTIFICATION_QUICK_REFERENCE.md | Developer guide |
| NOTIFICATION_BEFORE_AFTER.md | Code examples |
| NOTIFICATION_IMPLEMENTATION_SUMMARY.md | Overview |
| NOTIFICATION_DEPLOYMENT_GUIDE.md | Deployment steps |

---

## 🎯 One-Minute Summary

**What Changed:**
- Added `transaction_status` column to track notification context
- Created smart routing to automatically send to correct recipients
- Replaced DaisyUI with Tailwind CSS
- Enhanced helper with 4 methods (was 2)
- Added status filtering to API

**Why:**
- Better tracking and analytics
- Automatic recipient determination
- Modern, lightweight styling
- More flexible notification system

**How to Use:**
```php
// Old way still works
NotificationHelper::send($user, 'type', 'title', 'msg', 123);

// New way with status
NotificationHelper::send($user, 'type', 'title', 'msg', 123, null, 'pending');

// Smart routing (recommended)
NotificationHelper::sendByTransactionStatus($transaction, 'type', 'title', 'msg');
```

---

## 📞 Getting Help

1. Check documentation files (5 provided)
2. Review code comments in modified files
3. Test in `php artisan tinker`
4. Check database schema
5. Review browser console for errors

---

## ⚡ Performance Notes

- New index on `[user_id, transaction_status]` improves query speed
- API returns max 50 notifications (limit can be adjusted)
- Caches clear automatically on deployment
- No breaking changes to existing queries

---

## 🔒 Security Notes

- All notifications user-scoped (only current user sees theirs)
- Smart routing respects user roles and permissions
- No changes to existing auth/permission logic
- All input validated by existing controllers

---

**Version:** 1.0  
**Status:** Production Ready  
**Last Updated:** January 18, 2026

Use these 6 pages as your quick reference:
1. This card (quick lookup)
2. NOTIFICATION_QUICK_REFERENCE.md (deeper guide)
3. NOTIFICATION_SYSTEM_UPDATE.md (technical details)
4. NOTIFICATION_BEFORE_AFTER.md (code examples)
5. NOTIFICATION_DEPLOYMENT_GUIDE.md (deployment)
6. NOTIFICATION_IMPLEMENTATION_SUMMARY.md (overview)
