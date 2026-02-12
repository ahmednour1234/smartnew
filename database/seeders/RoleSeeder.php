<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Full system access',
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Can view, create, and update records',
            ],
            [
                'name' => 'Sales',
                'slug' => 'sales',
                'description' => 'Can only view records',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
