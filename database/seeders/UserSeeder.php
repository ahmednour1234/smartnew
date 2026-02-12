<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $managerRole = Role::where('slug', 'manager')->first();
        $salesRole = Role::where('slug', 'sales')->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if ($adminRole && !$admin->roles()->where('slug', 'admin')->exists()) {
            $admin->roles()->attach($adminRole);
        }

        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if ($managerRole && !$manager->roles()->where('slug', 'manager')->exists()) {
            $manager->roles()->attach($managerRole);
        }

        $sales = User::firstOrCreate(
            ['email' => 'sales@example.com'],
            [
                'name' => 'Sales User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if ($salesRole && !$sales->roles()->where('slug', 'sales')->exists()) {
            $sales->roles()->attach($salesRole);
        }
    }
}
