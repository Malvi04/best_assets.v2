<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_batch_id',
        'asset_id',
        'asset_type',
        'qty',
    ];

    public function requestBatch()
    {
        return $this->belongsTo(RequestBatch::class);
    }

    public function asset()
    {
        return $this->morphTo();
    }
}
