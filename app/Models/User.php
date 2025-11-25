<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'password',
        'department_id',
        'type',
        'status',
        'last_activity',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_activity' => 'datetime',
            'status' => 'boolean',
        ];
    }

    protected $attributes = [
        'status' => 1,
    ];
    
    public function isOnline(): bool
    {
        return $this->last_activity && $this->last_activity->gt(now()->subMinutes(1));
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include inactive users.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 0);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByHeads($query)
    {
        return $query->where('type', 'Head');
    }

    public function getLastSeenAttribute(): string
    {
        if ($this->isOnline()) {
            return 'Online';
        }

        if (! $this->last_activity) {
            return 'Never';
        }

        return $this->last_activity->diffForHumans();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignTransactions()  {
        return $this->hasMany(Transaction::class, 'assign_staff_id');
    }

    public function reviews()  {
        return $this->hasMany(TransactionReviewer::class, 'reviewer_id');
    }

    
    /**
     * Check if user is head
     */
    public function isHead(): bool
    {
        return $this->type === 'Head';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->type === 'Admin';
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status == 1;
    }

    /**
     * Get the department name attribute
     */
    public function getDepartmentNameAttribute(): ?string
    {
        return $this->department?->name;
    }

    /**
     * Get the department code attribute
     */
    public function getDepartmentCodeAttribute(): ?string
    {
        return $this->department?->code;
    }

    /**
     * Get formatted user type with badge styling
     */
    public function getFormattedTypeAttribute(): string
    {
        return match ($this->type) {
            'Admin' => '<span class="badge badge-error">Admin</span>',
            'Head' => '<span class="badge badge-warning">Head</span>',
            default => '<span class="badge badge-warning">'.$this->type.'</span>'
        };
    }

    /**
     * Get formatted status with badge styling
     */
    public function getFormattedStatusAttribute(): string
    {
        return $this->status
            ? '<span class="badge badge-success">Active</span>'
            : '<span class="badge badge-error">Inactive</span>';
    }


    /**
     * Check if user has a valid municipal_id format
     */
    public function hasValidMunicipalId(): bool
    {
        if (! $this->employee_id) {
            return false;
        }

        // Check if it matches the pattern: CODE+TYPE-YEAR-NUMBER
        return (bool) preg_match('/^[A-Z]{2,5}[A-Z]-\d{4}-\d{3}$/', $this->employee_id);
    }

    public function regenerateMunicipalId(): bool
    {
        if (! $this->department_id || ! $this->type) {
            return false;
        }

        $department = Department::find($this->department_id);
        if (! $department) {
            return false;
        }

        $this->employee_id = $department->generateMunicipalId($this->type);

        return $this->save();
    }

    
}
