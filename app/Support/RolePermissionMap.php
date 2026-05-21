<?php

namespace App\Support;

use App\Models\User;

class RolePermissionMap
{
    /**
     * @return array<string, array<int, string>>
     */
    public static function definitions(): array
    {
        return [
            User::ROLE_SUPER_ADMIN => [
                'platform.manage',
            ],
            User::ROLE_OWNER => [
                'dashboard.view',
                'products.manage',
                'stock.mutate',
                'pos.access',
                'reports.view',
                'reports.export',
                'reports.cashier',
                'stock_history.view',
                'sales.receipt',
                'sales.void',
                'users.manage',
                'users.invite',
                'activity_log.view',
                'subscription.manage',
            ],
            User::ROLE_MANAGER => [
                'dashboard.view',
                'reports.view',
                'reports.export',
                'reports.cashier',
                'stock_history.view',
                'sales.receipt',
                'sales.void',
                'users.manage',
                'activity_log.view',
            ],
            User::ROLE_CASHIER => [
                'dashboard.view',
                'pos.access',
                'reports.view',
                'sales.receipt',
            ],
            User::ROLE_STAFF_GUDANG => [
                'dashboard.view',
                'stock.mutate',
                'stock_history.view',
            ],
            User::ROLE_VIEWER => [
                'dashboard.view',
                'reports.view',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function permissionsFor(string $role): array
    {
        return self::definitions()[$role] ?? [];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            User::ROLE_SUPER_ADMIN => 'Super Admin',
            User::ROLE_OWNER => 'Owner',
            User::ROLE_MANAGER => 'Manager',
            User::ROLE_CASHIER => 'Cashier',
            User::ROLE_STAFF_GUDANG => 'Staff Gudang',
            User::ROLE_VIEWER => 'Viewer',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function manageableRoles(): array
    {
        return [
            User::ROLE_OWNER,
            User::ROLE_MANAGER,
            User::ROLE_CASHIER,
            User::ROLE_STAFF_GUDANG,
            User::ROLE_VIEWER,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function assignableRolesFor(User $actor): array
    {
        if ($actor->role === User::ROLE_OWNER) {
            return self::manageableRoles();
        }

        if ($actor->role === User::ROLE_MANAGER) {
            return [
                User::ROLE_CASHIER,
                User::ROLE_STAFF_GUDANG,
                User::ROLE_VIEWER,
            ];
        }

        return [];
    }
}
