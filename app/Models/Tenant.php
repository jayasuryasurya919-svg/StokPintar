<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'owner_id',
        'subscription_plan_id',
        'name',
        'slug',
        'status',
        'trial_ends_at',
        'subscription_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->plan();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function canAddProduct(): bool
    {
        $maxProducts = $this->plan?->max_products;

        return $maxProducts === null || $this->products()->count() < $maxProducts;
    }

    public function canAddUser(): bool
    {
        $maxUsers = $this->plan?->max_users;

        return $maxUsers === null || $this->users()->count() < $maxUsers;
    }

    public function productUsageLabel(): string
    {
        $count = $this->products()->count();
        $limit = $this->plan?->max_products;

        return $limit === null ? "{$count} / Unlimited" : "{$count} / {$limit}";
    }

    public function userUsageLabel(): string
    {
        $count = $this->users()->count();
        $limit = $this->plan?->max_users;

        return $limit === null ? "{$count} / Unlimited" : "{$count} / {$limit}";
    }

    public function storeUsageLabel(): string
    {
        $count = $this->stores()->count();
        $limit = $this->plan?->max_stores;

        return $limit === null ? "{$count} / Unlimited" : "{$count} / {$limit}";
    }
}
