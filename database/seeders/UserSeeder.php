<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['gian.mistica', 'Joseph Gian Carlo Mistica'],
            ['maria.lopez', 'Maria Lopez'],
            ['paolo.rivera', 'Paolo Rivera'],
            ['sofia.torres', 'Sofia Torres'],
            ['luis.mendoza', 'Luis Mendoza'],
        ];

        foreach ($records as [$username, $name]) {
            User::query()->updateOrCreate(
                ['username' => $username],
                ['full_name' => $name],
            );
        }
    }
}
