<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\RequestStatus;

class RequestBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'status' => RequestStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function stockRequests()
    {
        return $this->hasMany(StockRequest::class);
    }
}
