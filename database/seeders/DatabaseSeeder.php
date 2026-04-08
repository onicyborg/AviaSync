<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Crew;
use App\Models\FlightSchedule;
use App\Models\Certification;
use App\Models\HealthRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Admin Statis (Agar kamu gampang login)
        $admin = User::create([
            'name' => 'Geats Admin',
            'email' => 'admin@aviasync.com',
            'password' => Hash::make('Qwerty123*'), // Ganti sesuai selera
            'role' => 'admin',
        ]);

        // 2. Buat Akun Crew Statis (Untuk testing role Crew)
        $testCrewUser = User::create([
            'name' => 'Geats Crew',
            'email' => 'crew@aviasync.com',
            'password' => Hash::make('Qwerty123*'),
            'role' => 'crew',
        ]);
        
        Crew::factory()->create([
            'user_id' => $testCrewUser->id,
            'position' => 'Captain',
            'created_by' => $admin->id
        ]);

        // 3. Generate 50 Crew secara Acak
        User::factory(50)->create()->each(function ($user) use ($admin) {
            // Buat Profil Crew
            $crew = Crew::factory()->create([
                'user_id' => $user->id,
                'created_by' => $admin->id,
            ]);

            // Berikan 2 Sertifikat untuk masing-masing Crew
            Certification::factory(2)->create([
                'crew_id' => $crew->id,
                'created_by' => $admin->id,
            ]);

            // Berikan 2 Rekam Medis untuk masing-masing Crew
            HealthRecord::factory(2)->create([
                'crew_id' => $crew->id,
                'created_by' => $admin->id,
            ]);
        });

        // 4. Generate 20 Jadwal Penerbangan
        $flights = FlightSchedule::factory(20)->create([
            'created_by' => $admin->id,
        ]);

        // 5. Tugaskan Crew ke Jadwal Penerbangan (Pivot Table)
        $crews = Crew::all();

        foreach ($flights as $flight) {
            // Ambil 4 crew acak untuk setiap penerbangan
            $assignedCrews = $crews->random(4); 
            
            foreach ($assignedCrews as $index => $crew) {
                // Tentukan peran acak berdasarkan index looping
                $roleInFlight = match($index) {
                    0 => 'Captain',
                    1 => 'First Officer',
                    2 => 'Purser',
                    default => 'Flight Attendant',
                };

                $flight->crews()->attach($crew->id, [
                    'id' => (string) \Illuminate\Support\Str::uuid(), // Generate UUID manual untuk Pivot
                    'role_in_flight' => $roleInFlight,
                    'assigned_at' => now(),
                    'created_by' => $admin->id,
                ]);
            }
        }
    }
}