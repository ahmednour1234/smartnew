<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowUp extends Model
{
    protected $table = 'followups';
    
    protected $fillable = [
        'company_id',
        'user_id',
        'followup_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'followup_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
