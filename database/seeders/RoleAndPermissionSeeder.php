<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed the default permissions
        $permissions = Permission::defaultPermissions();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command->info('Default Permissions added.');

        $roles = Role::allRoles();

        foreach ($roles as $role) {
            $role = Role::firstOrCreate(['name' => trim($role)]);

            switch ($role->name) {
                case Role::ROLE_ADMIN:
                    $role->syncPermissions(Permission::all());
                    break;
                case Role::ROLE_USER:
            }

            $this->command->info('Adding users with teams...');
        }

        $this->command->warn('All done :)');
    }
}
