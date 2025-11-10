<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $full_name
 * @property string|null $position
 * @property string $role
 * @property int $department_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Department $department
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignStaff active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignStaff newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignStaff newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignStaff query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignStaff whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignStaff whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignStaff whereFullName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignStaff whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignStaff whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignStaff wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignStaff whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AssignStaff whereUpdatedAt($value)
 */
	class AssignStaff extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $model_type
 * @property int|null $model_id
 * @property string $description
 * @property array<array-key, mixed>|null $old_values
 * @property array<array-key, mixed>|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $url
 * @property string|null $method
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent|null $auditable
 * @property-read string $action_badge_class
 * @property-read array $changes
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog byAction(string $action)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog byModelType(string $modelType)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog byUser(int $userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog dateRange($startDate, $endDate)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereModelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereModelType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUserId($value)
 */
	class AuditLog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property string|null $logo
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DocumentType> $activeDocumentTypes
 * @property-read int|null $active_document_types_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $activeUsers
 * @property-read int|null $active_users_count
 * @property-read \App\Models\User|null $admin
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DocumentReview> $currentDocumentReviews
 * @property-read int|null $current_document_reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DocumentReview> $documentReviews
 * @property-read int|null $document_reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DocumentType> $documentTypes
 * @property-read int|null $document_types_count
 * @property-read \App\Models\User|null $head
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DocumentReview> $originalDocumentReviews
 * @property-read int|null $original_document_reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department byCode($code)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereUpdatedAt($value)
 */
	class Department extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $document_id
 * @property string $document_type
 * @property string $client_name
 * @property int $reviewer_id
 * @property int $process_time
 * @property string $time_unit
 * @property int $time_value
 * @property string $priority
 * @property string $assigned_staff
 * @property string|null $attachment_path
 * @property string $created_via
 * @property int $department_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Department $department
 * @property-read \App\Models\DocumentType|null $documentType
 * @property-read mixed $attachment_url
 * @property-read mixed $formatted_process_time
 * @property-read mixed $priority_badge_class
 * @property-read \App\Models\User $reviewer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document byPriority($priority)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document byStatus($status)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document department($departmentId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereAssignedStaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereAttachmentPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedVia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereProcessTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereReviewerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereTimeUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereTimeValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 */
	class Document extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $document_id
 * @property string $document_type
 * @property string $client_name
 * @property string $priority
 * @property array<array-key, mixed> $document_data
 * @property string|null $attachment_path
 * @property string|null $official_receipt_number
 * @property int $created_by
 * @property int|null $assigned_to
 * @property string|null $assigned_staff
 * @property int|null $current_department_id
 * @property int|null $original_department_id
 * @property string $status
 * @property string|null $review_notes
 * @property string|null $forwarding_notes
 * @property array<array-key, mixed>|null $forwarding_chain
 * @property bool $is_final_review
 * @property int $process_time_minutes
 * @property int|null $time_value
 * @property string $time_unit
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property bool|null $completed_on_time
 * @property \Illuminate\Support\Carbon|null $downloaded_at
 * @property \Illuminate\Support\Carbon|null $due_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $creator
 * @property-read \App\Models\Department|null $currentDepartment
 * @property-read mixed $current_step
 * @property-read mixed $display_priority
 * @property-read mixed $due_status
 * @property-read mixed $is_overdue
 * @property-read mixed $priority_badge_class
 * @property-read mixed $progress_percentage
 * @property-read mixed $remaining_time
 * @property-read mixed $remaining_time_minutes
 * @property-read \App\Models\Department|null $originalDepartment
 * @property-read \App\Models\User|null $reviewer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview approved()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview assignedTo($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview canceled()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview createdBy($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview downloaded()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview rejected()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereAssignedStaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereAttachmentPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereClientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereCompletedOnTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereCurrentDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereDocumentData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereDocumentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereDownloadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereDueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereForwardingChain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereForwardingNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereIsFinalReview($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereOfficialReceiptNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereOriginalDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereProcessTimeMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereReviewNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereTimeUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereTimeValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentReview whereUpdatedAt($value)
 */
	class DocumentReview extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property int $department_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Department $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DocumentType whereUpdatedAt($value)
 */
	class DocumentType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int|null $document_id
 * @property string $type
 * @property string $title
 * @property string $message
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property array<array-key, mixed>|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\DocumentReview|null $document
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereDocumentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereIsRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUserId($value)
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $employee_id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $avatar
 * @property int|null $department_id
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $last_activity
 * @property bool $status
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Department|null $department
 * @property-read string|null $department_code
 * @property-read string|null $department_name
 * @property-read string $formatted_status
 * @property-read string $formatted_type
 * @property-read bool $is_active
 * @property-read string $last_seen
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User byHeads()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User byType($type)
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User inactive()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent {}
}

