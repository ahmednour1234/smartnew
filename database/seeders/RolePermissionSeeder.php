<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('slug', 'admin')->first();
        $manager = Role::where('slug', 'manager')->first();
        $sales = Role::where('slug', 'sales')->first();

        $allPermissions = Permission::all();

        if ($admin && $allPermissions->isNotEmpty()) {
            $admin->permissions()->sync($allPermissions->pluck('id'));
        }

        if ($manager && $allPermissions->isNotEmpty()) {
            $managerPermissions = $allPermissions->filter(function ($permission) {
                return !str_contains($permission->slug, 'delete') && 
                       !str_contains($permission->slug, 'permissions');
            });
            $manager->permissions()->sync($managerPermissions->pluck('id'));
        }

        if ($sales && $allPermissions->isNotEmpty()) {
            $salesPermissions = $allPermissions->filter(function ($permission) {
                return str_contains($permission->slug, 'view');
            });
            $sales->permissions()->sync($salesPermissions->pluck('id'));
        }
    }
}
