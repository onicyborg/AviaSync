<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CrewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_id' => 'CRW-' . fake()->unique()->numerify('#####'),
            'position' => fake()->randomElement(['Captain', 'First Officer', 'Purser', 'Flight Attendant']),
            'base_location' => fake()->randomElement(['CGK - Jakarta', 'DPS - Bali', 'SUB - Surabaya', 'KNO - Medan']),
            'total_flight_hours' => fake()->numberBetween(100, 15000),
            'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']), // 75% active
        ];
    }
}
