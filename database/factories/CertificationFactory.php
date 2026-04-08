<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class CertificationFactory extends Factory
{
    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-3 years', '-1 months');
        
        return [
            'certificate_name' => fake()->randomElement(['Commercial Pilot License', 'Airline Transport Pilot License', 'Medical Certificate Class 1', 'Cabin Crew Attestation']),
            'certificate_number' => strtoupper(fake()->bothify('CERT-####-????')),
            'issue_date' => $issueDate,
            'expiry_date' => Carbon::parse($issueDate)->addYears(fake()->numberBetween(1, 5)),
            'status' => fake()->randomElement(['valid', 'valid', 'expired']),
        ];
    }
}