<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $resources = ['users', 'roles', 'permissions', 'countries', 'events', 'packages', 'companies', 'meetings'];
        $actions = ['view_any', 'view', 'create', 'update', 'delete'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $slug = "{$action}_{$resource}";
                $name = ucfirst(str_replace('_', ' ', $action)) . ' ' . ucfirst($resource);
                
                Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'description' => "Permission to {$action} {$resource}",
                    ]
                );
            }
        }

        Permission::firstOrCreate(
            ['slug' => 'view_dashboard'],
            [
                'name' => 'View Dashboard',
                'description' => 'Permission to view dashboard',
            ]
        );

        Permission::firstOrCreate(
            ['slug' => 'view_booked_companies'],
            [
                'name' => 'View Booked Companies',
                'description' => 'Permission to view who booked/assigned companies',
            ]
        );
    }
}
