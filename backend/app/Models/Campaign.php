<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id','workspace_id','channel_connection_id','whatsapp_template_id','name','channel','status','audience_count','sent_count','delivered_count','read_count','failed_count','audience_filter','settings','scheduled_at','started_at','completed_at'];
    protected function casts(): array { return ['audience_filter'=>'array','settings'=>'array','scheduled_at'=>'datetime','started_at'=>'datetime','completed_at'=>'datetime']; }
    public function connection(): BelongsTo { return $this->belongsTo(ChannelConnection::class, 'channel_connection_id'); }
    public function template(): BelongsTo { return $this->belongsTo(WhatsAppTemplate::class, 'whatsapp_template_id'); }
    public function recipients(): HasMany { return $this->hasMany(CampaignRecipient::class); }
}
