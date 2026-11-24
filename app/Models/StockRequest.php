<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StockRequest extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
