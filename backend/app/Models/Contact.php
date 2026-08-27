<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'workspace_id', 'first_name', 'last_name', 'email', 'phone',
        'avatar', 'company', 'job_title', 'source', 'status', 'last_contacted_at',
    ];

    protected function casts(): array
    {
        return ['last_contacted_at' => 'datetime'];
    }
}
