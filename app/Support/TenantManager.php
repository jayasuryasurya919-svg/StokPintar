<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

class TenantManager
{
    private ?int $tenantId = null;

    public function set(?int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function id(): ?int
    {
        if ($this->tenantId !== null) {
            return $this->tenantId;
        }

        return Auth::user()?->tenant_id;
    }

    public function clear(): void
    {
        $this->tenantId = null;
    }
}
