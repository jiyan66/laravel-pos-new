<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['Ana Reyes', 'ana.reyes@example.com', '09170000001'],
            ['Mark Santos', 'mark.santos@example.com', '09170000002'],
            ['Carla Cruz', 'carla.cruz@example.com', '09170000003'],
            ['John Garcia', 'john.garcia@example.com', '09170000004'],
            ['Ella Ramos', 'ella.ramos@example.com', '09170000005'],
        ];

        foreach ($records as [$name, $email, $phone]) {
            Customer::query()->updateOrCreate(
                ['email' => $email],
                ['full_name' => $name, 'phone' => $phone],
            );
        }
    }
}
