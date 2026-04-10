<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Crew;
use App\Models\FlightSchedule;
use App\Models\Certification;
use App\Models\HealthRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

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

        // 3. Generate Crew dengan distribusi posisi & base yang lebih rapi
        $positions = ['Captain', 'First Officer', 'Purser', 'Flight Attendant'];
        $bases = ['CGK', 'SUB', 'DPS', 'KNO']; // Jakarta, Surabaya, Denpasar, Medan

        User::factory(40)->create()->each(function ($user, $idx) use ($admin, $positions, $bases) {
            $position = $positions[$idx % count($positions)];
            $base = $bases[$idx % count($bases)];

            $crew = Crew::factory()->create([
                'user_id' => $user->id,
                'position' => $position,
                'base_location' => $base,
                'status' => 'active',
                'created_by' => $admin->id,
            ]);

            // Sertifikasi: 1 valid (expire 90-180 hari lagi), 1 expired (expire 30-180 hari lalu)
            Certification::factory()->create([
                'crew_id' => $crew->id,
                'status' => 'valid',
                'expiry_date' => Carbon::now()->addDays(rand(90, 180)),
                'created_by' => $admin->id,
            ]);
            Certification::factory()->create([
                'crew_id' => $crew->id,
                'status' => 'expired',
                'expiry_date' => Carbon::now()->subDays(rand(30, 180)),
                'created_by' => $admin->id,
            ]);

            // Rekam Medis: 2 riwayat terakhir 6 bulan, dengan next_checkup_date sebagian dekat
            HealthRecord::factory()->create([
                'crew_id' => $crew->id,
                'checkup_date' => Carbon::now()->subMonths(rand(4, 6))->startOfDay(),
                'next_checkup_date' => Carbon::now()->addMonths(rand(1, 3))->startOfDay(),
                'created_by' => $admin->id,
            ]);
            HealthRecord::factory()->create([
                'crew_id' => $crew->id,
                'checkup_date' => Carbon::now()->subMonths(rand(1, 3))->startOfDay(),
                'next_checkup_date' => Carbon::now()->addMonths(rand(3, 6))->startOfDay(),
                'created_by' => $admin->id,
            ]);
        });

        // 4. Generate Jadwal Penerbangan semi-real di rute populer
        $routes = [
            ['CGK', 'SUB', 95],
            ['CGK', 'DPS', 110],
            ['CGK', 'KNO', 120],
            ['SUB', 'DPS', 65],
            ['DPS', 'KNO', 150],
            ['SUB', 'KNO', 140],
        ];

        $flights = collect();
        $startDay = Carbon::now()->startOfDay();
        $flightCount = 24; // 4 minggu ke depan, 6 rute / pekan

        for ($i = 0; $i < $flightCount; $i++) {
            $route = $routes[$i % count($routes)];
            [$origin, $destination, $durationMin] = $route;

            $dep = (clone $startDay)->addDays(rand(1, 28))->addHours(rand(6, 22))->minute(0);
            $arr = (clone $dep)->addMinutes($durationMin + rand(-10, 15));

            $flights->push(FlightSchedule::create([
                // Deterministic unique flight numbers within this seeding run
                'flight_number' => 'AV' . str_pad((string) (100 + $i), 3, '0', STR_PAD_LEFT),
                'origin' => $origin,
                'destination' => $destination,
                'departure_time' => $dep,
                'arrival_time' => $arr,
                'status' => 'scheduled',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]));
        }

        // 5. Tugaskan Crew ke Jadwal Penerbangan (Pivot Table)
        $crews = Crew::all();

        foreach ($flights as $flight) {
            // Pilih crew berdasarkan posisi untuk peran penerbangan
            $captain = $crews->where('position', 'Captain')->random();
            $fo = $crews->where('position', 'First Officer')->random();
            $purser = $crews->where('position', 'Purser')->random();
            $fa = $crews->where('position', 'Flight Attendant')->random();

            $assignments = [
                ['crew' => $captain, 'role' => 'Captain'],
                ['crew' => $fo, 'role' => 'First Officer'],
                ['crew' => $purser, 'role' => 'Purser'],
                ['crew' => $fa, 'role' => 'Flight Attendant'],
            ];

            foreach ($assignments as $a) {
                $c = $a['crew'];
                if (!$c) continue;
                $flight->crews()->attach($c->id, [
                    'id' => (string) Str::uuid(),
                    'role_in_flight' => $a['role'],
                    'assigned_at' => now(),
                    'created_by' => $admin->id,
                ]);
            }
        }
    }
}