<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Occupant;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CarteiImobilTest extends TestCase
{
    use RefreshDatabase;

    private function seedBase(): void
    {
        // Seed minim: tenant + roluri
        $this->seed(\Database\Seeders\TenantsSeeder::class);
        $this->seed(\Database\Seeders\PermissionsSeeder::class);
    }

    private function makeTenant(string $name = 'Asociatia Test'): Tenant
    {
        return Tenant::create([
            'name' => $name,
            'address' => 'Str. Test nr. 1',
            'fiscal_code' => 'ROTEST',
            'description' => 'test',
            'contact_data' => ['phone' => '', 'email' => '', 'person' => ''],
        ]);
    }

    public function test_locatar_nu_poate_vedea_alt_apartament(): void
    {
        $this->seedBase();
        $tenant = $this->makeTenant();

        $ap1 = Apartment::create(['tenant_id' => $tenant->id, 'number' => '1', 'staircase' => 'A', 'floor' => '0']);
        $ap2 = Apartment::create(['tenant_id' => $tenant->id, 'number' => '2', 'staircase' => 'A', 'floor' => '0']);

        $locatar1 = User::factory()->create(['tenant_id' => $tenant->id]);
        $locatar1->assignRole('locatar');
        $locatar1->apartments()->attach($ap1->id);

        $locatar2 = User::factory()->create(['tenant_id' => $tenant->id]);
        $locatar2->assignRole('locatar');
        $locatar2->apartments()->attach($ap2->id);

        Passport::actingAs($locatar1);
        $this->getJson("/api/v2/apartments/{$ap2->id}/occupants")
            ->assertStatus(403);
    }

    public function test_submit_blocheaza_editarea_si_admin_poate_aproba(): void
    {
        $this->seedBase();
        $tenant = $this->makeTenant();

        // Admin tenant
        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('admin');

        $ap1 = Apartment::create(['tenant_id' => $tenant->id, 'number' => '10', 'staircase' => 'A', 'floor' => '2']);

        $locatar = User::factory()->create(['tenant_id' => $tenant->id]);
        $locatar->assignRole('locatar');
        $locatar->apartments()->attach($ap1->id);

        Passport::actingAs($locatar);

        $payload = [
            'first_name' => 'Ion',
            'last_name' => 'Popescu',
            'cnp' => null,
            'id_series' => 'RX',
            'id_number' => '123456',
            'domicile_address' => 'Str. Test 1',
            'role_in_unit' => 'owner',
            'other_role_text' => null,
            'move_in_date' => '2024-01-01',
            'move_out_date' => null,
            'is_minor' => false,
            'legal_guardian_name' => null,
            'phone' => null,
            'email' => null,
            'notes' => null,
        ];

        $res = $this->postJson("/api/v2/apartments/{$ap1->id}/occupants", $payload)
            ->assertStatus(201)
            ->assertJsonPath('success', true);

        $occupantId = $res->json('data.occupant.id');
        $this->assertNotNull($occupantId);

        $this->postJson("/api/v2/apartments/{$ap1->id}/occupants/submit")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        // după submit locatarul nu mai poate edita
        $this->putJson("/api/v2/occupants/{$occupantId}", array_merge($payload, ['first_name' => 'Ionut']))
            ->assertStatus(403);

        // admin aprobă
        Passport::actingAs($admin);
        $this->postJson("/api/v2/apartments/{$ap1->id}/occupants/approve")
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('occupants', [
            'id' => $occupantId,
            'status' => 'approved',
        ]);
    }

    public function test_export_pdf_locatar_doar_dupa_aprobare_si_se_logheaza_exportul(): void
    {
        $this->seedBase();
        $tenant = $this->makeTenant();

        $admin = User::factory()->create(['tenant_id' => $tenant->id]);
        $admin->assignRole('admin');

        $ap1 = Apartment::create(['tenant_id' => $tenant->id, 'number' => '20', 'staircase' => 'A', 'floor' => '3']);

        $locatar = User::factory()->create(['tenant_id' => $tenant->id]);
        $locatar->assignRole('locatar');
        $locatar->apartments()->attach($ap1->id);

        // Creez 1 occupant draft
        $o = Occupant::create([
            'apartment_id' => $ap1->id,
            'first_name' => 'Ion',
            'last_name' => 'Popescu',
            'cnp' => '1234567890123',
            'id_series' => 'RX',
            'id_number' => '123456',
            'domicile_address' => 'Str. Test 1',
            'role_in_unit' => 'owner',
            'other_role_text' => null,
            'move_in_date' => '2024-01-01',
            'move_out_date' => null,
            'is_minor' => false,
            'legal_guardian_name' => null,
            'phone' => null,
            'email' => null,
            'notes' => null,
            'status' => 'draft',
            'submitted_at' => null,
            'approved_at' => null,
            'approved_by' => null,
            'reject_reason' => null,
            'created_by' => $locatar->id,
            'updated_by' => $locatar->id,
        ]);

        Passport::actingAs($locatar);

        // încă nu e aprobat -> 403
        $this->get("/api/v2/apartments/{$ap1->id}/occupants/export.pdf")
            ->assertStatus(403);

        // submit + approve
        $this->postJson("/api/v2/apartments/{$ap1->id}/occupants/submit")->assertStatus(200);
        Passport::actingAs($admin);
        $this->postJson("/api/v2/apartments/{$ap1->id}/occupants/approve")->assertStatus(200);

        // export OK
        Passport::actingAs($locatar);
        $this->get("/api/v2/apartments/{$ap1->id}/occupants/export.pdf")
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertDatabaseHas('occupant_change_logs', [
            'occupant_id' => $o->id,
            'action' => 'exported',
        ]);
    }
}

