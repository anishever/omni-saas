<?php

namespace App\Services\Auth;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProvisionTenantService
{
    public function create(string $companyName, string $name, string $email, string $password): array
    {
        return DB::transaction(function () use ($companyName, $name, $email, $password) {
            $tenant = Tenant::create([
                'name' => $companyName,
                'slug' => Str::slug($companyName) . '-' . Str::lower(Str::random(6)),
                'email' => $email,
                'status' => 'active',
            ]);

            $workspace = $tenant->workspaces()->create([
                'name' => 'Main Workspace',
                'slug' => 'main',
                'status' => 'active',
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'status' => 'active',
            ]);

            $owner = Role::create([
                'tenant_id' => $tenant->id,
                'name' => 'Owner',
                'description' => 'Full access to the tenant.',
            ]);

            $owner->permissions()->sync(Permission::pluck('id')->all());
            $user->roles()->attach($owner->id);

            return compact('tenant', 'workspace', 'user');
        });
    }
}
