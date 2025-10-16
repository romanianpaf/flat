<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class ServiceProvidersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::with('users')->get();

        if ($tenants->isEmpty()) {
            return;
        }

        $providerDefinitions = collect([
            [
                'type' => 'private',
                'first_name' => 'Andrei',
                'last_name' => 'Pop',
                'phone' => '0722123456',
                'email' => 'andrei.pop@example.com',
                'service_description' => '<p>Instalator cu experiență de peste 10 ani. Intervenții rapide 24/7.</p>',
                'is_published' => true,
            ],
            [
                'type' => 'company',
                'company_name' => 'Electro Volt SRL',
                'phone' => '0364411223',
                'email' => 'contact@electrovolt.ro',
                'service_description' => '<p>Echipă autorizată ANRE. Instalări tablouri, mentenanță linii 220/380V.</p>',
                'is_published' => true,
            ],
            [
                'type' => 'company',
                'company_name' => 'Clean Homes',
                'phone' => '0744332211',
                'email' => 'office@cleanhomes.ro',
                'service_description' => '<p>Curățenie profesională pentru apartamente și spații comune.</p>',
                'is_published' => false,
            ],
        ]);

        $tenants->each(function (Tenant $tenant) use ($providerDefinitions) {
            $categories = ServiceCategory::where('tenant_id', $tenant->id)->with('subcategories')->get();

            if ($categories->isEmpty()) {
                return;
            }

            $creatorId = optional($tenant->users->first())->id;

            $providerDefinitions->each(function (array $definition) use ($tenant, $categories, $creatorId) {
                /** @var ServiceCategory $category */
                $category = $categories->random();
                $subcategory = $category->subcategories->random();

                ServiceProvider::create(array_merge($definition, [
                    'tenant_id' => $tenant->id,
                    'service_category_id' => $category->id,
                    'service_subcategory_id' => $subcategory->id,
                    'created_by' => $creatorId,
                    'is_published' => $definition['is_published'] ?? false,
                ]));
            });
        });
    }
}
