<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view',
            'contacts.view', 'contacts.create', 'contacts.edit', 'contacts.delete',
            'conversations.view', 'conversations.reply', 'conversations.assign',
            'campaigns.view', 'campaigns.create', 'campaigns.send', 'campaigns.delete',
            'automations.view', 'automations.create', 'automations.edit', 'automations.delete',
            'ai.view', 'ai.manage',
            'analytics.view',
            'team.view', 'team.manage',
            'settings.view', 'settings.manage',
            'billing.view', 'billing.manage',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
    }
}
