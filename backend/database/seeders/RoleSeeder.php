<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Owner' => Permission::pluck('id')->all(),
            'Admin' => Permission::whereNotIn('name', ['billing.manage'])->pluck('id')->all(),
            'Agent' => Permission::whereIn('name', [
                'dashboard.view', 'contacts.view', 'contacts.create', 'contacts.edit',
                'conversations.view', 'conversations.reply', 'conversations.assign',
                'campaigns.view', 'ai.view',
            ])->pluck('id')->all(),
            'Viewer' => Permission::whereIn('name', [
                'dashboard.view', 'contacts.view', 'conversations.view', 'campaigns.view', 'analytics.view',
            ])->pluck('id')->all(),
        ];

        foreach ($roles as $name => $permissionIds) {
            // Roles are tenant-owned and created when a tenant is provisioned.
            // This seeder only documents the canonical permission mapping.
            Role::query()->where('name', $name)->each(function (Role $role) use ($permissionIds) {
                $role->permissions()->sync($permissionIds);
            });
        }
    }
}
