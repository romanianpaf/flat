<?php

namespace Database\Seeders;

use App\Models\Apartment;
use App\Models\Occupant;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarteiImobilSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $tenants = Tenant::query()->where('id', '>', 1)->get();

            foreach ($tenants as $tenant) {
                // 6 apartamente demo / tenant
                $apartments = collect(range(1, 6))->map(function (int $nr) use ($tenant) {
                    return Apartment::firstOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'number' => (string) $nr,
                            'staircase' => 'A',
                            'floor' => (string) max(0, intdiv($nr - 1, 2)),
                        ],
                        []
                    );
                });

                $locatari = User::query()
                    ->where('tenant_id', $tenant->id)
                    ->get()
                    ->filter(fn(User $u) => $u->hasRole('locatar'))
                    ->values();

                // Asociază locatarii la apartamente (pivot + compat: users.apartment)
                foreach ($locatari as $idx => $locatar) {
                    $apt = $apartments[$idx % $apartments->count()];
                    $locatar->apartment = $apt->number;
                    $locatar->save();
                    $locatar->apartments()->syncWithoutDetaching([$apt->id]);
                }

                // Creează ocupanți demo pentru fiecare apartament
                foreach ($apartments as $apt) {
                    /** @var User|null $ownerUser */
                    $ownerUser = $locatari->first(fn(User $u) => trim((string) $u->apartment) === (string) $apt->number);
                    $creator = $ownerUser ?: User::query()->where('tenant_id', $tenant->id)->first();
                    if (!$creator) {
                        continue;
                    }

                    if (Occupant::where('apartment_id', $apt->id)->exists()) {
                        continue;
                    }

                    Occupant::create([
                        'apartment_id' => $apt->id,
                        'first_name' => 'Ion',
                        'last_name' => 'Popescu',
                        'cnp' => null,
                        'id_series' => null,
                        'id_number' => null,
                        'domicile_address' => $tenant->address,
                        'role_in_unit' => 'owner',
                        'other_role_text' => null,
                        'move_in_date' => now()->subYears(2)->format('Y-m-d'),
                        'move_out_date' => null,
                        'is_minor' => false,
                        'legal_guardian_name' => null,
                        'phone' => null,
                        'email' => null,
                        'notes' => 'Înregistrare demo',
                        'status' => 'draft',
                        'submitted_at' => null,
                        'approved_at' => null,
                        'approved_by' => null,
                        'reject_reason' => null,
                        'created_by' => $creator->id,
                        'updated_by' => $creator->id,
                    ]);

                    Occupant::create([
                        'apartment_id' => $apt->id,
                        'first_name' => 'Maria',
                        'last_name' => 'Popescu',
                        'cnp' => null,
                        'id_series' => null,
                        'id_number' => null,
                        'domicile_address' => $tenant->address,
                        'role_in_unit' => 'family',
                        'other_role_text' => null,
                        'move_in_date' => now()->subYears(1)->format('Y-m-d'),
                        'move_out_date' => null,
                        'is_minor' => false,
                        'legal_guardian_name' => null,
                        'phone' => null,
                        'email' => null,
                        'notes' => 'Înregistrare demo',
                        'status' => 'draft',
                        'submitted_at' => null,
                        'approved_at' => null,
                        'approved_by' => null,
                        'reject_reason' => null,
                        'created_by' => $creator->id,
                        'updated_by' => $creator->id,
                    ]);
                }
            }
        });
    }
}

