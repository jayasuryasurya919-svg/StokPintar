<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(string $action, ?Model $subject = null, array $meta = []): ?ActivityLog
    {
        $user = auth()->user();
        if (!$user || !$user->tenant_id) {
            return null;
        }

        return ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'meta' => empty($meta) ? null : $meta,
            'ip_address' => Request::ip(),
        ]);
    }
}
