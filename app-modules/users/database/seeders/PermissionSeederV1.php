<?php

namespace Stella\Users\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeederV1 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'view dashboard',
            'manage users',
            'view users',
            'create users',
            'update users',
            'delete users',
            'manage medical records',
            'view medical records',
            'create medical records',
            'update medical records',
            'delete medical records',
            'write diagnosis',
            'manage inventory',
            'view inventory',
            'create inventory',
            'update inventory',
            'delete inventory',
            'approve medication',
            'manage pharmacy requests',
            'view pharmacy requests',
            'update pharmacy requests',
            'manage appointments',
            'view appointments',
            'create appointments',
            'update appointments',
            'delete appointments'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
