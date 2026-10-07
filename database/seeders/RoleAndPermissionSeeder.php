<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache permission Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat Permissions (Hak Akses)
        $permissions = [
            'view-dashboard',
            'manage-accounts',   // CRUD Chart of Accounts
            'create-journal',    // Entri Jurnal
            'view-reports',      // Lihat Laporan Keuangan
            'manage-users',      // Kelola Pengguna
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Buat Role & Assign Permissions

        // Role STAFF: Hanya bisa input jurnal & lihat laporan
        $staffRole = Role::firstOrCreate(['name' => 'Staff']);
        $staffRole->syncPermissions([
            'view-dashboard',
            'create-journal',
            'view-reports',
        ]);

        // Role ADMIN: Memiliki seluruh hak akses
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions(Permission::all());

        // 3. Assign Role ke Pengguna Contoh

        // Buat atau Ambil User Admin
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@perusahaan.com'],
            ['name' => 'Administrator Utama', 'password' => Hash::make('password123')]
        );
        $adminUser->assignRole($adminRole);

        // Buat atau Ambil User Staff
        $staffUser = User::firstOrCreate(
            ['email' => 'staff@perusahaan.com'],
            ['name' => 'Staff Akuntansi', 'password' => Hash::make('password123')]
        );
        $staffUser->assignRole($staffRole);
    }
}
