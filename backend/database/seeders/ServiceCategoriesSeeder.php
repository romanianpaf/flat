<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\ServiceSubcategory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ServiceCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            return;
        }

        $definitions = collect([
            'Instalatori' => [
                'description' => 'Servicii de instalații sanitare și termice.',
                'subcategories' => [
                    ['name' => 'Apă', 'description' => 'Instalații sanitare, reparații țevi, montaj aparate sanitare.'],
                    ['name' => 'Gaz', 'description' => 'Detectori gaz, montaj centrale, verificări ISCIR.'],
                ],
            ],
            'Electricieni' => [
                'description' => 'Instalații electrice rezidențiale și industriale.',
                'subcategories' => [
                    ['name' => '220V', 'description' => 'Locuințe, birouri, rețele domestice.'],
                    ['name' => '380V', 'description' => 'Spații comerciale și industriale.'],
                ],
            ],
            'Curățenie' => [
                'description' => 'Servicii profesionale de curățenie.',
                'subcategories' => [
                    ['name' => 'Rezidențial', 'description' => 'Curățenie în apartamente și case.'],
                    ['name' => 'Spații comune', 'description' => 'Întreținere scări, lifturi, parcări.'],
                ],
            ],
        ]);

        $tenants->each(function (Tenant $tenant) use ($definitions) {
            $definitions->each(function (array $data, string $categoryName) use ($tenant) {
                /** @var ServiceCategory $category */
                $category = ServiceCategory::create([
                    'tenant_id' => $tenant->id,
                    'name' => $categoryName,
                    'description' => $data['description'],
                ]);

                collect($data['subcategories'] ?? [])->each(function (array $subcategory) use ($tenant, $category) {
                    ServiceSubcategory::create([
                        'tenant_id' => $tenant->id,
                        'service_category_id' => $category->id,
                        'name' => $subcategory['name'],
                        'description' => $subcategory['description'] ?? null,
                    ]);
                });
            });
        });
    }
}
