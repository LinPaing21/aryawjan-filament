<?php

namespace Stella\Users\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seedAdmin();
        $this->seedDoctor();
        $this->seedPharmacist();
        $this->seedPatient();
    }

    private function seedAdmin(): void
    {
        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        $role->syncPermissions([
            'view dashboard',
            // Access management
            'manage roles',
            'manage users', 'view users', 'create users', 'update users', 'delete users',
            'manage patients', 'view patients', 'create patients', 'update patients', 'delete patients',
            // Clinic
            'manage doctor schedules', 'view doctor schedules',
            'manage appointments', 'view appointments', 'create appointments', 'update appointments', 'delete appointments',
            'manage medical records', 'view medical records', 'create medical records', 'update medical records', 'delete medical records',
            'write diagnosis',
            'manage triage logs', 'view triage logs', 'create triage logs', 'update triage logs',
            'manage invoices', 'view invoices',
            // Pharmacy
            'manage inventory', 'view inventory', 'create inventory', 'update inventory', 'delete inventory',
            'approve medication',
            'manage pharmacy requests', 'view pharmacy requests', 'update pharmacy requests',
            // AI
            'manage medical knowledge',
        ]);
    }

    private function seedDoctor(): void
    {
        $role = Role::firstOrCreate(['name' => 'Doctor', 'guard_name' => 'web']);

        $role->syncPermissions([
            'view dashboard',
            // Patients — view only, no create/delete
            'view patients',
            // Doctor schedules
            'manage doctor schedules', 'view doctor schedules',
            // Appointments
            'manage appointments', 'view appointments', 'create appointments', 'update appointments',
            // Medical records & diagnosis
            'manage medical records', 'view medical records', 'create medical records', 'update medical records',
            'write diagnosis',
            // Triage — view only
            'view triage logs',
            // Pharmacy requests — view only (for context)
            'view pharmacy requests',
            // Invoices — view only
            'view invoices',
        ]);
    }

    private function seedPharmacist(): void
    {
        $role = Role::firstOrCreate(['name' => 'Pharmacist', 'guard_name' => 'web']);

        $role->syncPermissions([
            'view dashboard',
            // Patients — view only
            'view patients',
            // Appointments — view only (to check patient booking context)
            'view appointments',
            // Triage — view only
            'view triage logs',
            // Pharmacy requests
            'manage pharmacy requests', 'view pharmacy requests', 'update pharmacy requests',
            'approve medication',
            // Medicine inventory
            'manage inventory', 'view inventory', 'create inventory', 'update inventory',
            // Invoices — view only
            'view invoices',
        ]);
    }

    private function seedPatient(): void
    {
        // Patients interact via Viber, not the dashboard — no permissions assigned.
        Role::firstOrCreate(['name' => 'Patient', 'guard_name' => 'web']);
    }
}
