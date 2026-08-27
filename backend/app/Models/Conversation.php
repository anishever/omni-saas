<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = ['tenant_id','workspace_id','contact_id','channel','external_id','status','assigned_to','last_message_at'];
    protected function casts(): array { return ['last_message_at' => 'datetime']; }
    public function contact(): BelongsTo { return $this->belongsTo(Contact::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function messages(): HasMany { return $this->hasMany(Message::class); }
}
