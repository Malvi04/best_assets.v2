<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'asset_type',
        'user_id',
        'type',
        'qty',
        'source',
        'notes',
    ];

    protected $casts = [
        'type' => TransactionType::class,
    ];

    public function asset()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
