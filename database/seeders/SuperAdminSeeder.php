<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Company::firstOrCreate(
            ['slug' => 'system-demo'],
            [
                'name' => 'System Demo',
                'primary_email' => 'demo-system@platform.local',
                'status' => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@platform.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'company_id' => null,
            ]
        );
    }
}
