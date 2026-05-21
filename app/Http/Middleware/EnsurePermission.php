<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user() || ! $request->user()->canPermission($permission)) {
            abort(403, 'Akses tidak diizinkan untuk permission ini.');
        }

        if (! $this->isWithinAccessSchedule($request)) {
            abort(403, 'Akses di luar jadwal kerja yang diizinkan.');
        }

        return $next($request);
    }

    private function isWithinAccessSchedule(Request $request): bool
    {
        $user = $request->user();

        if (! $user || ! $user->tenant_id || ! $user->accessSchedules()->exists()) {
            return true;
        }

        $now = Carbon::now(config('app.timezone'));
        $minuteOfDay = ($now->hour * 60) + $now->minute;
        $yesterday = $now->copy()->subDay()->dayOfWeek;

        return $user->accessSchedules()
            ->whereIn('day_of_week', [$now->dayOfWeek, $yesterday])
            ->get()
            ->contains(function ($schedule) use ($minuteOfDay, $now, $yesterday) {
                [$startHour, $startMinute] = array_map('intval', explode(':', substr((string) $schedule->start_time, 0, 5)));
                [$endHour, $endMinute] = array_map('intval', explode(':', substr((string) $schedule->end_time, 0, 5)));
                $start = ($startHour * 60) + $startMinute;
                $end = ($endHour * 60) + $endMinute;

                if ($start <= $end) {
                    return (int) $schedule->day_of_week === $now->dayOfWeek
                        && $minuteOfDay >= $start
                        && $minuteOfDay <= $end;
                }

                return ((int) $schedule->day_of_week === $now->dayOfWeek && $minuteOfDay >= $start)
                    || ((int) $schedule->day_of_week === $yesterday && $minuteOfDay <= $end);
            });
    }
}
