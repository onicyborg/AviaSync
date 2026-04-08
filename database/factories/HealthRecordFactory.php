<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class HealthRecordFactory extends Factory
{
    public function definition(): array
    {
        $checkupDate = fake()->dateTimeBetween('-1 years', 'now');

        return [
            'checkup_date' => $checkupDate,
            'medical_examiner' => 'Dr. ' . fake()->name(),
            'status' => fake()->randomElement(['fit', 'fit', 'fit', 'restricted']),
            'notes' => fake()->sentence(),
            'next_checkup_date' => Carbon::parse($checkupDate)->addMonths(6),
        ];
    }
}