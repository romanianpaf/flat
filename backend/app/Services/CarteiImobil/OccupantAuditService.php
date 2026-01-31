<?php

namespace App\Services\CarteiImobil;

use App\Models\Occupant;
use App\Models\OccupantChangeLog;
use App\Support\CarteiImobil\SensitiveMasker;
use Illuminate\Http\Request;

class OccupantAuditService
{
    /**
     * @param array<string,mixed>|null $before
     * @param array<string,mixed>|null $after
     */
    public function log(Occupant $occupant, string $action, Request $request, ?array $before = null, ?array $after = null): void
    {
        $changes = $this->diff($before, $after);

        OccupantChangeLog::create([
            'occupant_id' => $occupant->id,
            'user_id' => optional($request->user())->id,
            'action' => $action,
            'changes' => $changes,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
            'created_at' => now(),
        ]);
    }

    /**
     * @param array<string,mixed>|null $before
     * @param array<string,mixed>|null $after
     * @return array<string,mixed>|null
     */
    private function diff(?array $before, ?array $after): ?array
    {
        if ($before === null && $after === null) {
            return null;
        }

        $before = $before ?? [];
        $after = $after ?? [];

        $keys = array_unique(array_merge(array_keys($before), array_keys($after)));
        $changedKeys = [];

        foreach ($keys as $key) {
            $b = $before[$key] ?? null;
            $a = $after[$key] ?? null;
            if ($b !== $a) {
                $changedKeys[] = $key;
            }
        }

        if (empty($changedKeys)) {
            return null;
        }

        $maskKeys = ['cnp', 'id_series', 'id_number'];
        $maskedBefore = [];
        $maskedAfter = [];

        foreach ($changedKeys as $key) {
            $b = $before[$key] ?? null;
            $a = $after[$key] ?? null;

            if (in_array($key, $maskKeys, true)) {
                $maskedBefore[$key] = is_string($b) ? SensitiveMasker::mask($b) : null;
                $maskedAfter[$key] = is_string($a) ? SensitiveMasker::mask($a) : null;
            } else {
                $maskedBefore[$key] = $b;
                $maskedAfter[$key] = $a;
            }
        }

        return [
            'before' => $maskedBefore,
            'after' => $maskedAfter,
        ];
    }
}

