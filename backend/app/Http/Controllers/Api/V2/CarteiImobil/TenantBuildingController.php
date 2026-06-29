<?php

namespace App\Http\Controllers\Api\V2\CarteiImobil;

use App\Http\Controllers\Api\V2\ApiController;
use App\Http\Requests\CarteiImobil\SyncApartmentsRequest;
use App\Http\Requests\CarteiImobil\UpsertStaircaseRequest;
use App\Models\Apartment;
use App\Models\Staircase;
use App\Models\Tenant;
use App\Support\CarteiImobil\CarteiImobilAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantBuildingController extends ApiController
{
    private function resolveTenantId(Request $request): ?int
    {
        $user = $request->user();

        if (CarteiImobilAccess::isGlobalAdmin($user)) {
            // sysadmin poate trimite ?tenant_id=
            $tenantId = $request->query('tenant_id');
            return $tenantId ? (int) $tenantId : $user->tenant_id;
        }

        return $user->tenant_id;
    }

    private function authorizeConfig(Request $request, int $tenantId): bool
    {
        $user = $request->user();
        if (CarteiImobilAccess::isGlobalAdmin($user)) {
            return true;
        }

        return $user->tenant_id === $tenantId && $user->can('configure apartments');
    }

    /**
     * Get list of tenants for sysadmin to select
     */
    public function tenants(Request $request)
    {
        $user = $request->user();

        if (!CarteiImobilAccess::isGlobalAdmin($user)) {
            return $this->error('Nu ai drepturi pentru a vedea lista de tenanți.', 403);
        }

        $tenants = Tenant::query()
            ->orderBy('name')
            ->get()
            ->map(fn(Tenant $t) => [
                'id' => $t->id,
                'name' => $t->name,
            ]);

        return $this->success(['tenants' => $tenants]);
    }

    public function show(Request $request)
    {
        $tenantId = $this->resolveTenantId($request);
        if (!$tenantId) {
            return $this->success(['staircases' => [], 'apartments' => []]);
        }

        if (!$this->authorizeConfig($request, $tenantId)) {
            return $this->error('Nu ai drepturi pentru configurarea imobilului.', 403);
        }

        $staircases = Staircase::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->map(fn(Staircase $s) => [
                'id' => $s->id,
                'tenant_id' => $s->tenant_id,
                'code' => $s->code,
                'name' => $s->name,
                'sort_order' => $s->sort_order,
                'floors' => $s->floors ?? [],
            ])->values();

        $apartments = Apartment::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('staircase')
            ->orderByRaw('CAST(number AS UNSIGNED) ASC')
            ->orderBy('number')
            ->get()
            ->groupBy(fn(Apartment $a) => (string) ($a->staircase ?? ''))
            ->map(function ($group, $staircaseCode) {
                return [
                    'staircase' => $staircaseCode,
                    'numbers' => $group->pluck('number')->values(),
                ];
            })->values();

        return $this->success([
            'staircases' => $staircases,
            'apartments' => $apartments,
        ]);
    }

    public function storeStaircase(UpsertStaircaseRequest $request)
    {
        $tenantId = $this->resolveTenantId($request);
        if (!$tenantId || !$this->authorizeConfig($request, $tenantId)) {
            return $this->error('Nu ai drepturi pentru configurarea imobilului.', 403);
        }

        $data = $request->validated();

        $s = Staircase::create([
            'tenant_id' => $tenantId,
            'code' => trim($data['code']),
            'name' => $data['name'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'floors' => $this->normalizeFloors($data['floors'] ?? []),
        ]);

        return $this->success(['staircase' => [
            'id' => $s->id,
            'tenant_id' => $s->tenant_id,
            'code' => $s->code,
            'name' => $s->name,
            'sort_order' => $s->sort_order,
            'floors' => $s->floors ?? [],
        ]], 201);
    }

    public function updateStaircase(UpsertStaircaseRequest $request, Staircase $staircase)
    {
        $tenantId = $this->resolveTenantId($request);
        if (!$tenantId || !$this->authorizeConfig($request, $staircase->tenant_id)) {
            return $this->error('Nu ai drepturi pentru configurarea imobilului.', 403);
        }

        $data = $request->validated();
        $staircase->code = trim($data['code']);
        $staircase->name = $data['name'] ?? null;
        $staircase->sort_order = $data['sort_order'] ?? $staircase->sort_order;
        // Actualizăm etajele doar dacă au fost trimise (modalul de editare nu le trimite).
        if (array_key_exists('floors', $data)) {
            $staircase->floors = $this->normalizeFloors($data['floors'] ?? []);
        }
        $staircase->save();

        return $this->success(['staircase' => [
            'id' => $staircase->id,
            'tenant_id' => $staircase->tenant_id,
            'code' => $staircase->code,
            'name' => $staircase->name,
            'sort_order' => $staircase->sort_order,
            'floors' => $staircase->floors ?? [],
        ]]);
    }

    /**
     * Normalizează lista de etaje: trim, fără goluri, fără duplicate, reindexat.
     */
    private function normalizeFloors($floors): array
    {
        return collect(is_array($floors) ? $floors : [])
            ->map(fn($f) => trim((string) $f))
            ->filter(fn($f) => $f !== '')
            ->unique()
            ->values()
            ->all();
    }

    public function destroyStaircase(Request $request, Staircase $staircase)
    {
        $tenantId = $this->resolveTenantId($request);
        if (!$tenantId || !$this->authorizeConfig($request, $staircase->tenant_id)) {
            return $this->error('Nu ai drepturi pentru configurarea imobilului.', 403);
        }

        $hasApartments = Apartment::query()
            ->where('tenant_id', $staircase->tenant_id)
            ->where('staircase', $staircase->code)
            ->exists();

        if ($hasApartments) {
            return $this->error('Nu poți șterge scara cât timp există apartamente asociate.', 409);
        }

        $staircase->delete();
        return $this->success(['message' => 'Scara a fost ștearsă.']);
    }

    public function syncApartments(SyncApartmentsRequest $request, Staircase $staircase)
    {
        $tenantId = $this->resolveTenantId($request);
        if (!$tenantId || !$this->authorizeConfig($request, $staircase->tenant_id)) {
            return $this->error('Nu ai drepturi pentru configurarea imobilului.', 403);
        }

        $payload = $request->validated();
        $numbers = collect($payload['numbers'])->map(fn($n) => trim((string) $n))->filter()->unique()->values();
        $removeMissing = (bool) ($payload['remove_missing'] ?? false);

        DB::transaction(function () use ($staircase, $numbers, $removeMissing) {
            foreach ($numbers as $nr) {
                Apartment::firstOrCreate([
                    'tenant_id' => $staircase->tenant_id,
                    'number' => $nr,
                    'staircase' => $staircase->code,
                ], [
                    'floor' => null,
                ]);
            }

            if ($removeMissing) {
                Apartment::query()
                    ->where('tenant_id', $staircase->tenant_id)
                    ->where('staircase', $staircase->code)
                    ->whereNotIn('number', $numbers->all())
                    ->delete();
            }
        });

        return $this->success(['message' => 'Apartamentele au fost sincronizate.']);
    }

    /**
     * Update floor for a specific apartment
     */
    public function updateApartmentFloor(Request $request, Apartment $apartment)
    {
        $tenantId = $this->resolveTenantId($request);
        if (!$tenantId || !$this->authorizeConfig($request, $apartment->tenant_id)) {
            return $this->error('Nu ai drepturi pentru configurarea imobilului.', 403);
        }

        $floor = $request->input('floor');
        $apartment->floor = $floor ? trim((string) $floor) : null;
        $apartment->save();

        return $this->success([
            'apartment' => [
                'id' => $apartment->id,
                'number' => $apartment->number,
                'staircase' => $apartment->staircase,
                'floor' => $apartment->floor,
            ]
        ]);
    }

    /**
     * Get building options for dropdowns (staircases, apartments, floors)
     * This endpoint is accessible to all authenticated users from the same tenant
     */
    public function options(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        if (!$tenantId) {
            return $this->success(['staircases' => [], 'apartments' => [], 'floors' => []]);
        }

        $staircases = Staircase::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->pluck('code')
            ->unique()
            ->values();

        $apartments = Apartment::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('staircase')
            ->orderByRaw('CAST(number AS UNSIGNED) ASC')
            ->orderBy('number')
            ->get()
            ->map(fn(Apartment $a) => [
                'id' => $a->id,
                'number' => $a->number,
                'staircase' => $a->staircase,
                'floor' => $a->floor,
            ]);

        // Extract unique floors
        $floors = $apartments
            ->pluck('floor')
            ->filter()
            ->unique()
            ->sort(function ($a, $b) {
                // Sort floors: P (parter), then numeric
                if ($a === 'P' || $a === 'p') return -1;
                if ($b === 'P' || $b === 'p') return 1;
                return (int) $a <=> (int) $b;
            })
            ->values();

        return $this->success([
            'staircases' => $staircases,
            'apartments' => $apartments,
            'floors' => $floors,
        ]);
    }

    /**
     * Get detailed apartment list with floors for configuration
     */
    public function apartmentsList(Request $request)
    {
        $tenantId = $this->resolveTenantId($request);
        if (!$tenantId) {
            return $this->success(['apartments' => []]);
        }

        if (!$this->authorizeConfig($request, $tenantId)) {
            return $this->error('Nu ai drepturi pentru configurarea imobilului.', 403);
        }

        $apartments = Apartment::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('staircase')
            ->orderByRaw('CAST(number AS UNSIGNED) ASC')
            ->orderBy('number')
            ->get()
            ->map(fn(Apartment $a) => [
                'id' => $a->id,
                'number' => $a->number,
                'staircase' => $a->staircase,
                'floor' => $a->floor,
            ]);

        return $this->success(['apartments' => $apartments]);
    }
}

