# Notification System - Before & After Examples

## Migration Changes

### BEFORE: Original Migration
```php
// 2025_10_26_102455_create_notifications_table.php
Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->unsignedBigInteger('document_id')->nullable();
    $table->string('type');
    $table->string('title');
    $table->text('message');
    $table->boolean('is_read')->default(false);
    $table->timestamp('read_at')->nullable();
    $table->json('data')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'is_read']);
    $table->index(['user_id', 'created_at']);
});
```

### AFTER: Enhanced Migration
```php
// 2025_10_26_102455_create_notifications_table.php
Schema::create('notifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->unsignedBigInteger('document_id')->nullable();
    $table->string('transaction_status')->nullable();  // ✨ NEW
    $table->string('type');
    $table->string('title');
    $table->text('message');
    $table->boolean('is_read')->default(false);
    $table->timestamp('read_at')->nullable();
    $table->json('data')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'is_read']);
    $table->index(['user_id', 'created_at']);
    $table->index(['user_id', 'transaction_status']);  // ✨ NEW
});
```

---

## Helper Class Changes

### BEFORE: NotificationHelper
```php
class NotificationHelper{
    public static function send(User $user, string $type, string $title, string $message, ?int $documentId, ?array $data = null) {
        return Notification::create([
            'user_id' => $user->id,
            'document_id' => $documentId,
            'title' => $title,
            'type' => $type,
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function sendtoMultiple(array $userIds, string $type, string $message, string $title, ?int $documentId, ?array $data = null ){
        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'document_id' => $documentId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        Notification::insert($notifications);
    }
}
```

### AFTER: Enhanced NotificationHelper
```php
class NotificationHelper{
    
    // Single user notification
    public static function send(User $user, string $type, string $title, string $message, ?int $documentId, ?array $data = null, ?string $transactionStatus = null) {
        return Notification::create([
            'user_id' => $user->id,
            'document_id' => $documentId,
            'transaction_status' => $transactionStatus,  // ✨ NEW PARAMETER
            'title' => $title,
            'type' => $type,
            'message' => $message,
            'data' => $data
        ]);
    }

    // Multiple users notification
    public static function sendtoMultiple(array $userIds, string $type, string $message, string $title, ?int $documentId, ?array $data = null, ?string $transactionStatus = null ){
        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'document_id' => $documentId,
                'transaction_status' => $transactionStatus,  // ✨ NEW PARAMETER
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        Notification::insert($notifications);
    }

    // ✨ NEW METHOD - Send to all users of a type
    public static function sendToAllByUserType(string $userType, string $type, string $title, string $message, ?int $documentId, ?array $data = null, ?string $transactionStatus = null) {
        $users = User::where('type', $userType)->get();
        $userIds = $users->pluck('id')->toArray();
        
        if (!empty($userIds)) {
            self::sendtoMultiple($userIds, $type, $message, $title, $documentId, $data, $transactionStatus);
        }
    }

    // ✨ NEW METHOD - Smart routing by transaction status
    public static function sendByTransactionStatus(Transaction $transaction, string $type, string $title, string $message, ?array $data = null) {
        $status = $transaction->transaction_status;
        $users = self::getRecipientsForTransactionStatus($transaction, $status);
        
        if (!empty($users)) {
            foreach ($users as $user) {
                self::send($user, $type, $title, $message, $transaction->id, $data, $status);
            }
        }
    }

    // ✨ NEW METHOD - Determine recipients based on status
    private static function getRecipientsForTransactionStatus(Transaction $transaction, string $status): array {
        $recipients = [];
        
        switch ($status) {
            case 'pending':
            case 'in_progress':
                // Assigned staff + department heads
                if ($transaction->assign_staff_id) {
                    $staff = User::find($transaction->assign_staff_id);
                    if ($staff) $recipients[] = $staff;
                }
                $heads = User::where('type', 'Head')
                    ->where('department_id', $transaction->department_id)
                    ->get()
                    ->toArray();
                $recipients = array_merge($recipients, $heads);
                break;
                
            case 'approved':
            case 'completed':
                // Origin creator + their department heads
                $creator = User::find($transaction->created_by);
                if ($creator) $recipients[] = $creator;
                
                $deptHeads = User::where('type', 'Head')
                    ->where('department_id', $transaction->origin_department_id)
                    ->get()
                    ->toArray();
                $recipients = array_merge($recipients, $deptHeads);
                break;
                
            case 'rejected':
            case 'returned_to_creator':
                // Origin creator only
                $creator = User::find($transaction->created_by);
                if ($creator) $recipients[] = $creator;
                break;
                
            case 'cancelled':
                // All involved parties
                $creator = User::find($transaction->created_by);
                if ($creator) $recipients[] = $creator;
                
                $allReviewers = $transaction->reviewers()->get();
                foreach ($allReviewers as $reviewer) {
                    $user = User::find($reviewer->assigned_to);
                    if ($user) $recipients[] = $user;
                }
                break;
                
            default:
                // All system users
                $recipients = User::all()->toArray();
                break;
        }
        
        // Remove duplicates
        $unique = [];
        foreach ($recipients as $user) {
            $id = is_object($user) ? $user->id : $user['id'];
            if (!isset($unique[$id])) {
                $unique[$id] = $user;
            }
        }
        
        return array_values($unique);
    }
}
```

---

## Service Layer Changes

### BEFORE: DocumentWorkflowService
```php
// Send notification with basic info only
NotificationHelper::send(
    $reviewer,
    'pending',
    'New Document for Review',
    "You have received a new {$docInfo['title']} document...",
    $review->id
);
```

### AFTER: DocumentWorkflowService
```php
// ✨ NEW - Include transaction status in every notification
NotificationHelper::send(
    $reviewer,
    'pending',
    'New Document for Review',
    "You have received a new {$docInfo['title']} document...",
    $review->id,
    null,
    'pending'  // ✨ Transaction status
);
```

**Applied to all workflow methods:**
- `sendForReview()` → Status: `'pending'`
- `forwardReview()` → Status: `'in_progress'`
- `completeReview()` → Status: `'approved'`
- `rejectReview()` → Status: `'rejected'`
- `cancelReview()` → Status: `'cancelled'`

---

## Controller Changes

### BEFORE: NotificationController
```php
public function getNotifications(Request $request): JsonResponse {
    $user = Auth::user();
    
    $notifications = Notification::where('user_id', $user->id)
        ->with('document')
        ->orderBy('created_at', 'desc')
        ->take(50)
        ->get()
        ->map(function ($notification) {
            return [
                'id' => $notification->id,
                'document_id' => $notification->document_id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'is_read' => $notification->is_read,
                'created_at' => $notification->created_at->toISOString(),
                'read_at' => $notification->read_at?->toISOString(),
            ];
        });

    $unreadCount = Notification::where('user_id', $user->id)
        ->where('is_read', false)
        ->count();

    return response()->json([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unreadCount,
        'unread_counts' => $unreadCounts
    ]);
}
```

### AFTER: Enhanced NotificationController
```php
public function getNotifications(Request $request): JsonResponse {
    $user = Auth::user();
    
    $query = Notification::where('user_id', $user->id);
    
    // ✨ NEW - Filter by transaction status if provided
    $status = $request->query('status');
    if ($status) {
        $query->where('transaction_status', $status);
    }
    
    $notifications = $query
        ->with('document')
        ->orderBy('created_at', 'desc')
        ->take(50)
        ->get()
        ->map(function ($notification) {
            return [
                'id' => $notification->id,
                'document_id' => $notification->document_id,
                'transaction_status' => $notification->transaction_status,  // ✨ NEW
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'is_read' => $notification->is_read,
                'created_at' => $notification->created_at->toISOString(),
                'read_at' => $notification->read_at?->toISOString(),
            ];
        });

    // ... unread counts ...

    // ✨ NEW - Unread counts by transaction status
    $unreadByStatus = Notification::where('user_id', $user->id)
        ->where('is_read', false)
        ->selectRaw('transaction_status, count(*) as count')
        ->groupBy('transaction_status')
        ->pluck('count', 'transaction_status')
        ->toArray();

    return response()->json([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => $unreadCount,
        'unread_counts' => $unreadCounts,
        'unread_by_status' => $unreadByStatus  // ✨ NEW
    ]);
}
```

---

## UI/Styling Changes

### BEFORE: DaisyUI Classes
```html
<button id="notification-bell" class="btn btn-ghost btn-circle text-white">
    <div class="indicator">
        <i data-lucide="bell" fill="none" class="h-6 w-6 text-white"></i>
        <span id="notification-badge" class="badge badge-xs badge-error indicator-item hidden"></span>
    </div>
</button>

<div id="notification-dropdown" class="hidden absolute right-0 mt-3 w-80 max-h-96 overflow-y-auto bg-white text-black rounded-box shadow-xl border border-base-300 z-100">
    <div class="p-4 border-b border-base-300 bg-base-200">
        <h3 class="font-bold text-lg">Notifications</h3>
        <p class="text-sm opacity-70">Recent updates</p>
    </div>
</div>

<div class="notification-item p-4 hover:bg-base-200 cursor-pointer transition bg-primary/10" ...>
    <div class="w-10 h-10 rounded-full bg-success text-success-content flex items-center justify-center">
        <i data-lucide="check-circle" class="h-5 w-5"></i>
    </div>
    <span class="badge badge-xs badge-primary">New</span>
</div>
```

### AFTER: Tailwind CSS Classes
```html
<button id="notification-bell" class="relative inline-flex items-center justify-center p-2 text-white hover:bg-blue-700 rounded-full transition-colors">
    <div class="relative">
        <svg data-lucide="bell" fill="none" class="h-6 w-6 text-white"></svg>
        <span id="notification-badge" class="hidden absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"></span>
    </div>
</button>

<div id="notification-dropdown" class="hidden absolute right-0 mt-3 w-80 max-h-96 overflow-y-auto bg-white text-gray-900 rounded-lg shadow-xl border border-gray-200 z-100">
    <div class="p-4 border-b border-gray-200 bg-gray-50">
        <h3 class="font-bold text-lg text-gray-900">Notifications</h3>
        <p class="text-sm text-gray-600">Recent updates</p>
    </div>
</div>

<div class="notification-item p-4 hover:bg-gray-100 cursor-pointer transition bg-blue-50" ...>
    <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
        <svg data-lucide="check-circle" class="h-5 w-5"></svg>
    </div>
    <span class="inline-block px-2 py-0.5 text-xs font-semibold text-white bg-blue-500 rounded">New</span>
</div>
```

### Color Mapping Comparison

| Status | BEFORE (DaisyUI) | AFTER (Tailwind) |
|--------|------------------|------------------|
| Approved | bg-success text-success-content | bg-green-100 text-green-600 |
| Rejected | bg-error text-error-content | bg-red-100 text-red-600 |
| Canceled | bg-warning text-warning-content | bg-yellow-100 text-yellow-600 |
| Pending | bg-info text-info-content | bg-blue-100 text-blue-600 |
| Received | bg-primary text-primary-content | bg-indigo-100 text-indigo-600 |
| Completed | bg-success text-success-content | bg-green-100 text-green-600 |
| Overdue | bg-warning text-warning-content | bg-orange-100 text-orange-600 |

---

## JavaScript Changes

### BEFORE: DaisyUI Icon Color Function
```javascript
function getNotificationIconColor(type) {
    const colors = {
        'approved': 'bg-success text-success-content',
        'rejected': 'bg-error text-error-content',
        'canceled': 'bg-warning text-warning-content',
        'pending': 'bg-info text-info-content',
        'received': 'bg-primary text-primary-content',
        'completed': 'bg-success text-success-content',
        'overdue': 'bg-warning text-warning-content'
    };
    return colors[type] || 'bg-base-300';
}
```

### AFTER: Tailwind Icon Color Function
```javascript
function getNotificationIconColor(type) {
    const colors = {
        'approved': 'bg-green-100 text-green-600',
        'rejected': 'bg-red-100 text-red-600',
        'canceled': 'bg-yellow-100 text-yellow-600',
        'pending': 'bg-blue-100 text-blue-600',
        'received': 'bg-indigo-100 text-indigo-600',
        'completed': 'bg-green-100 text-green-600',
        'overdue': 'bg-orange-100 text-orange-600'
    };
    return colors[type] || 'bg-gray-100 text-gray-600';
}
```

---

## API Response Comparison

### BEFORE: Basic Response
```json
{
  "success": true,
  "notifications": [
    {
      "id": 1,
      "document_id": 123,
      "type": "pending",
      "title": "New Document for Review",
      "message": "You have received a new document...",
      "is_read": false,
      "created_at": "2026-01-18T10:30:00Z",
      "read_at": null
    }
  ],
  "unread_count": 5,
  "unread_counts": {
    "pending": 2,
    "approved": 1
  }
}
```

### AFTER: Enhanced Response
```json
{
  "success": true,
  "notifications": [
    {
      "id": 1,
      "document_id": 123,
      "transaction_status": "pending",  // ✨ NEW
      "type": "pending",
      "title": "New Document for Review",
      "message": "You have received a new document...",
      "is_read": false,
      "created_at": "2026-01-18T10:30:00Z",
      "read_at": null
    }
  ],
  "unread_count": 5,
  "unread_counts": {
    "pending": 2,
    "approved": 1
  },
  "unread_by_status": {  // ✨ NEW
    "pending": 3,
    "in_progress": 2
  }
}
```

---

## Usage Example Comparison

### BEFORE: Sending Notifications
```php
// Limited control - no status tracking
$user = User::find(1);
NotificationHelper::send($user, 'pending', 'Title', 'Message', 123);

// Multiple users
NotificationHelper::sendtoMultiple([1,2,3], 'approved', 'Message', 'Title', 123);
```

### AFTER: Enhanced Notification System
```php
// 1. Single user with status
NotificationHelper::send($user, 'pending', 'Title', 'Message', 123, null, 'pending');

// 2. Multiple users with status
NotificationHelper::sendtoMultiple([1,2,3], 'approved', 'Message', 'Title', 123, null, 'approved');

// 3. Broadcast to all users of type
NotificationHelper::sendToAllByUserType('Head', 'system', 'Title', 'Message', 123, null, 'pending');

// 4. Smart routing - automatically determine recipients!
$transaction = Transaction::find(1);
NotificationHelper::sendByTransactionStatus($transaction, 'update', 'Title', 'Message', null);
// Automatically sends to appropriate users based on transaction status
```

---

## Summary of Improvements

| Aspect | BEFORE | AFTER |
|--------|--------|-------|
| **Status Tracking** | None | Full transaction status support |
| **Smart Routing** | Manual user selection | Automatic based on transaction state |
| **Styling** | DaisyUI components | Pure Tailwind CSS |
| **Filtering** | Not available | Status-based API filtering |
| **Status Analytics** | None | Unread counts by status |
| **Flexibility** | Single-method only | 4 helper methods |
| **Database** | No status tracking | Complete audit trail |
| **API Query** | Get all only | Filter by status |

---

## Migration Path

If you're upgrading from the old system:

1. **Database**: Run the new migration
   ```bash
   php artisan migrate
   ```

2. **Code**: No breaking changes - old calls still work
   ```php
   // Old code still works
   NotificationHelper::send($user, 'pending', 'Title', 'Message', 123);
   // New code with status
   NotificationHelper::send($user, 'pending', 'Title', 'Message', 123, null, 'pending');
   ```

3. **UI**: Automatically uses new styling (no template changes needed for basic functionality)

4. **Recommended**: Update all service layer calls to use new status parameter for better tracking
