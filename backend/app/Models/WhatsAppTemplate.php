<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppTemplate extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id','workspace_id','channel_connection_id','external_id','name','language','category','status','components','metadata'];
    protected function casts(): array { return ['components' => 'array', 'metadata' => 'array']; }
    public function connection(): BelongsTo { return $this->belongsTo(ChannelConnection::class, 'channel_connection_id'); }
}
