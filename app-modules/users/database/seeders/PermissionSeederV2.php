<?php

namespace Stella\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeederV2 extends Seeder
{
    /**
     * New permissions not covered by PermissionSeederV1.
     */
    public function run(): void
    {
        $permissions = [
            // Access — missing from V1 but used by RoleResource & PatientResource
            'manage roles',
            'manage patients',
            'view patients',
            'create patients',
            'update patients',
            'delete patients',

            // Doctor schedules
            'manage doctor schedules',
            'view doctor schedules',

            // Triage logs
            'manage triage logs',
            'view triage logs',
            'create triage logs',
            'update triage logs',

            // Medical knowledge (AI/RAG)
            'manage medical knowledge',

            // Invoices
            'manage invoices',
            'view invoices',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
