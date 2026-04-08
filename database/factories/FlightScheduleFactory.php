<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class FlightScheduleFactory extends Factory
{
    public function definition(): array
    {
        $departure = fake()->dateTimeBetween('now', '+2 weeks');
        $flightDuration = fake()->numberBetween(60, 300); // 1 sampai 5 jam

        return [
            'flight_number' => 'AVS-' . fake()->unique()->numerify('###'),
            'origin' => fake()->randomElement(['CGK', 'DPS', 'SUB', 'KNO', 'YIA', 'BPN']),
            'destination' => fake()->randomElement(['SIN', 'KUL', 'BKK', 'CGK', 'DPS', 'NRT']),
            'departure_time' => $departure,
            'arrival_time' => Carbon::parse($departure)->addMinutes($flightDuration),
            'status' => fake()->randomElement(['scheduled', 'scheduled', 'active']),
        ];
    }
}