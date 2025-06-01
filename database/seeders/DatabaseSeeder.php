<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $roles = ['Admin', 'Pengajar', 'Siswa'];

        foreach ($roles as $role) {
            $temp = [
                'name' => $role,
            ];
            Role::create($temp);
        }
    }
}
