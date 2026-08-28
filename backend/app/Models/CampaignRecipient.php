<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignRecipient extends Model
{
    use HasFactory;
    protected $fillable = ['campaign_id','contact_id','message_id','status','external_id','error','sent_at','delivered_at','read_at'];
    protected function casts(): array { return ['sent_at'=>'datetime','delivered_at'=>'datetime','read_at'=>'datetime']; }
    public function campaign(): BelongsTo { return $this->belongsTo(Campaign::class); }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function message(): BelongsTo { return $this->belongsTo(Message::class); }
}
