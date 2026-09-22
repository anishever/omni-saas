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
        'whatsapp_opt_in', 'whatsapp_opt_in_at', 'whatsapp_opt_in_source',
        'avatar', 'company', 'job_title', 'source', 'status', 'last_contacted_at',
    ];

    protected function casts(): array
    {
        return [
            'last_contacted_at' => 'datetime',
            'whatsapp_opt_in' => 'boolean',
            'whatsapp_opt_in_at' => 'datetime',
        ];
    }
}
