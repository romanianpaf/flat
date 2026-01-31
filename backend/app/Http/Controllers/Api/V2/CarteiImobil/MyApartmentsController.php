<?php

namespace App\Http\Controllers\Api\V2\CarteiImobil;

use App\Http\Controllers\Api\V2\ApiController;
use App\Models\Apartment;
use App\Support\CarteiImobil\CarteiImobilAccess;
use Illuminate\Http\Request;

class MyApartmentsController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Dacă există pivot, îl folosim
        $pivotApartments = $user->apartments()->get();
        if ($pivotApartments->isNotEmpty()) {
            return $this->success([
                'apartments' => $pivotApartments->map(fn(Apartment $a) => [
                    'id' => $a->id,
                    'tenant_id' => $a->tenant_id,
                    'number' => $a->number,
                    'staircase' => $a->staircase,
                    'floor' => $a->floor,
                ])->values(),
            ]);
        }

        // Fallback legacy: users.apartment (string)
        $legacy = trim((string) $user->apartment);
        if ($legacy !== '' && $user->tenant_id) {
            $apartment = Apartment::query()
                ->where('tenant_id', $user->tenant_id)
                ->where('number', $legacy)
                ->first();

            if ($apartment) {
                return $this->success([
                    'apartments' => [[
                        'id' => $apartment->id,
                        'tenant_id' => $apartment->tenant_id,
                        'number' => $apartment->number,
                        'staircase' => $apartment->staircase,
                        'floor' => $apartment->floor,
                    ]],
                ]);
            }
        }

        // Admin/comitet: opțional listăm apartamentele tenant-ului
        if ($user->tenant_id && CarteiImobilAccess::isApprover($user)) {
            $aps = Apartment::query()->where('tenant_id', $user->tenant_id)->orderBy('number')->get();
            return $this->success([
                'apartments' => $aps->map(fn(Apartment $a) => [
                    'id' => $a->id,
                    'tenant_id' => $a->tenant_id,
                    'number' => $a->number,
                    'staircase' => $a->staircase,
                    'floor' => $a->floor,
                ])->values(),
            ]);
        }

        return $this->success(['apartments' => []]);
    }
}

