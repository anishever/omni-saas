<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id','conversation_id','external_id','direction','sender_type','sender_user_id','type','body','payload','status','sent_at'];
    protected function casts(): array { return ['payload' => 'array', 'sent_at' => 'datetime']; }
    public function conversation(): BelongsTo { return $this->belongsTo(Conversation::class); }
    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_user_id'); }
}
