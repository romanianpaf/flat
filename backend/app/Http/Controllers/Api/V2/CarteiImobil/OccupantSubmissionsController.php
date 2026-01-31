<?php

namespace App\Http\Controllers\Api\V2\CarteiImobil;

use App\Http\Controllers\Api\V2\ApiController;
use App\Models\Apartment;
use App\Models\Occupant;
use App\Support\CarteiImobil\CarteiImobilAccess;
use Illuminate\Http\Request;

class OccupantSubmissionsController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!CarteiImobilAccess::isApprover($user) && !CarteiImobilAccess::isGlobalAdmin($user)) {
            return $this->error('Nu ai drepturi pentru a vedea cererile.', 403);
        }

        $query = Apartment::query()
            ->withCount([
                'occupants as submitted_count' => fn($q) => $q->where('status', 'submitted'),
                'occupants as total_count' => fn($q) => $q,
            ])
            ->whereHas('occupants', fn($q) => $q->where('status', 'submitted'))
            ->orderByDesc('submitted_count');

        if ($user->tenant_id && !CarteiImobilAccess::isGlobalAdmin($user)) {
            $query->where('tenant_id', $user->tenant_id);
        }

        $apartments = $query->get();

        $data = $apartments->map(function (Apartment $a) {
            $submittedAt = Occupant::query()
                ->where('apartment_id', $a->id)
                ->where('status', 'submitted')
                ->max('submitted_at');

            return [
                'apartment_id' => $a->id,
                'tenant_id' => $a->tenant_id,
                'apartment_number' => $a->number,
                'staircase' => $a->staircase,
                'floor' => $a->floor,
                'nr_persoane' => (int) $a->total_count,
                'nr_submitted' => (int) $a->submitted_count,
                'status' => 'submitted',
                'submitted_at' => $submittedAt ? (string) $submittedAt : null,
            ];
        })->values();

        return $this->success(['submissions' => $data]);
    }
}

