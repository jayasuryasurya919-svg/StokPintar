<?php

namespace App\Models;

use App\Support\RolePermissionMap;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['tenant_id', 'name', 'email', 'password', 'role', 'google_id', 'google_avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_OWNER = 'owner';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_CASHIER = 'cashier';
    public const ROLE_STAFF_GUDANG = 'staff_gudang';
    public const ROLE_VIEWER = 'viewer';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function ownedTenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'owner_id');
    }

    public function accessSchedules(): HasMany
    {
        return $this->hasMany(UserAccessSchedule::class);
    }

    public function storeAccess(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'user_store_access');
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isCashier(): bool
    {
        return $this->role === self::ROLE_CASHIER;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * @return array<int, string>
     */
    public function permissions(): array
    {
        return RolePermissionMap::permissionsFor($this->role);
    }

    public function canPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }

    public function roleLabel(): string
    {
        return RolePermissionMap::labels()[$this->role] ?? ucfirst(str_replace('_', ' ', $this->role));
    }
}
