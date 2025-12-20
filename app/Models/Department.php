<?php

namespace App\Models;

use App\Services\User\UserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'logo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    protected $attributes = [
        'status' => 1,
    ];

    // Get the Head of this Department
    public function head(): HasOne
    {
        return $this->hasOne(User::class)->where('type', 'Head')->where('status', 1);
    }

    // Get all users of this Department
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(User::class)->where('type', 'Admin');
    }

    public function transaction(){
        return $this->hasMany(Transaction::class);
    }

    public function documentTags() {
        return $this->belongsToMany(DocumentTag::class, 'department_document_tag')->withTimestamps();
    }

    public function activeUsers(): HasMany
    {
        return $this->users()->where('status', 1);
    }

    public function getActiveDocumentTags()  {
        return $this->documentTags()->where('status', true);
    }

    public function hasHead(): bool
    {
        return $this->head()->exists();
    }

    public function hasAdmin(): bool
    {
        return $this->admin()->exists();
    }

    // get the next user code
    public function generateEmployeeId(string $type): string
    {
        $year = now()->year;
        $typeCode = match ($type) {
            'Head' => 'H',
            'Admin' => 'A',
            default => 'H'
        };

        $prefix = "{$this->code}{$typeCode}-{$year}-";

        $lastUser = DB::table('users')
            ->where('employee_id', 'like', "%-{$year}-%")
            ->orderBy('employee_id', 'desc')
            ->first();

        if (! $lastUser) {
            $nextNumber = 1;
        } else {
            // use employee_id (the actual column)
            $lastCode = $lastUser->employee_id;
            $lastDashPos = strrpos($lastCode, '-');
            if ($lastDashPos !== false) {
                $number = (int) substr($lastCode, $lastDashPos + 1);
                $nextNumber = $number + 1;
            } else {
                $nextNumber = 1;
            }
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get staff count for this department
     */
    public function getStaffCount(): int
    {
        return $this->staff()->count();
    }

    public function getActiveStaffCount(): int
    {
        return $this->staff()->where('status', 1)->count();
    }

    public function getWorkflowsWithTags() {
        
        return Workflow::whereHas('documentTags', function ($query) {
            $query->where('department_id', $this->id);
        });
    }

    /**
     * Get total users count for this department
     */
    public function getTotalUsersCount(): int
    {
        return $this->users()->count();
    }

    /**
     * Get active users count for this department
     */
    public function getActiveUsersCount(): int
    {
        return
            $this->users()
            ->where('status', 1)
            ->count();
    }

    public function getLogoUrl(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($department) {
            // Ensure code is uppercase
            $department->code = strtoupper($department->code);

            // Set default status if not provided
            if ($department->status === null) {
                $department->status = true;
            }
        });

        static::updating(function ($department) {
            // Ensure code is uppercase when updating
            $department->code = strtoupper($department->code);
        });
    }

    /**
     * Scope to get active departments
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', strtoupper($code));
    }
}
