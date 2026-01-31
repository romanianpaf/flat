<?php

namespace Database\Factories;

use App\Models\Apartment;
use App\Models\Occupant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Occupant>
 */
class OccupantFactory extends Factory
{
    protected $model = Occupant::class;

    public function definition(): array
    {
        $first = $this->faker->firstName();
        $last = $this->faker->lastName();

        return [
            'apartment_id' => Apartment::factory(),
            'first_name' => $first,
            'last_name' => $last,
            'cnp' => null,
            'id_series' => null,
            'id_number' => null,
            'domicile_address' => $this->faker->address(),
            'role_in_unit' => $this->faker->randomElement(['owner', 'tenant', 'family']),
            'other_role_text' => null,
            'move_in_date' => $this->faker->dateTimeBetween('-5 years', '-1 day')->format('Y-m-d'),
            'move_out_date' => null,
            'is_minor' => false,
            'legal_guardian_name' => null,
            'phone' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->optional()->safeEmail(),
            'notes' => $this->faker->optional()->sentence(),
            'status' => 'draft',
            'submitted_at' => null,
            'approved_at' => null,
            'approved_by' => null,
            'reject_reason' => null,
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }
}

