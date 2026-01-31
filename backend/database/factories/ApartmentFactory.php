<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Apartment>
 */
class ApartmentFactory extends Factory
{
    protected $model = Apartment::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'number' => (string) $this->faker->numberBetween(1, 120),
            'staircase' => $this->faker->randomElement(['A', 'B', 'C', null]),
            'floor' => (string) $this->faker->numberBetween(0, 12),
        ];
    }
}

