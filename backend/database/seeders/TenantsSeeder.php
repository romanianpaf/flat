<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Tenant System (pentru sysadmini)
        Tenant::create([
            'id' => 1,
            'name' => 'System',
            'address' => 'System',
            'fiscal_code' => 'SYSTEM',
            'description' => 'Tenant-ul system pentru administratori',
            'contact_data' => [
                'phone' => '',
                'email' => '',
                'person' => 'System',
            ],
        ]);

        Tenant::create([
            'name' => 'Pensiunea Poiana',
            'address' => 'Strada Principală nr. 45, Cluj-Napoca, Cluj',
            'fiscal_code' => 'RO12345678',
            'description' => 'Pensiune familială în inima orașului, cu camere confortabile și mic dejun inclus.',
            'contact_data' => [
                'phone' => '0740123456',
                'email' => 'contact@pensiuneapoiana.ro',
                'person' => 'Ion Popescu',
            ],
        ]);

        Tenant::create([
            'name' => 'Cabana Muntele Verde',
            'address' => 'Drumul Forestier nr. 12, Predeal, Brașov',
            'fiscal_code' => 'RO87654321',
            'description' => 'Cabană din lemn situată la 1200m altitudine, cu vedere panoramică spre munți.',
            'contact_data' => [
                'phone' => '0745987654',
                'email' => 'rezervari@muntelever.ro',
                'person' => 'Maria Ionescu',
            ],
        ]);

        Tenant::create([
            'name' => 'Hotel Dunarea',
            'address' => 'Bulevardul Republicii nr. 78, Galați',
            'fiscal_code' => 'RO24681357',
            'description' => 'Hotel de 4 stele cu vedere la Dunăre, restaurant și centru SPA.',
            'contact_data' => [
                'phone' => '0236567890',
                'email' => 'rezervari@hoteldunarea.ro',
                'person' => 'Gheorghe Dumitru',
            ],
        ]);
    }
}
