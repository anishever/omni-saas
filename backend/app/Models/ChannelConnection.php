<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChannelConnection extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id','workspace_id','channel','name','external_id','access_token','verify_token','settings','status'];
    protected $hidden = ['access_token','verify_token'];
    protected function casts(): array { return ['settings' => 'array']; }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function workspace(): BelongsTo { return $this->belongsTo(Workspace::class); }
    protected function accessToken(): Attribute { return Attribute::make(set: fn ($value) => $value ? encrypt($value) : null, get: fn ($value) => $value ? decrypt($value) : null); }
    protected function verifyToken(): Attribute { return Attribute::make(set: fn ($value) => $value ? encrypt($value) : null, get: fn ($value) => $value ? decrypt($value) : null); }
}
