<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'municipal_id',
        'name',
        'email',
        'password',
        'department',
        'type',
        'status',
        'last_activity'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'last_activity'     => 'datetime',
            'status'            => 'boolean',
        ];
    }

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

    public function archive(): HasOne
    {
        return $this->hasOne(UserArchive::class);
    }

    public function deactivate($reason = null, $deactivatedBy = null)
    {
        $this->update(['status' => 0]);

        // Create archive record
        UserArchive::create([
            'user_id' => $this->id,
            'municipal_id' => $this->municipal_id,
            'name' => $this->name,
            'email' => $this->email,
            'department' => $this->department,
            'type' => $this->type,
            'reason' => $reason,
            'deactivated_at' => now(),
            'deactivated_by' => $deactivatedBy ?? Auth::id(),
        ]);
    }

    public function activate()
    {
        $this->update(['status' => 1]);

        // Remove from archive
        $this->archive()?->delete();
    }
}
