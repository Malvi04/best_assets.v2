<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'stock',
        'unit',
        'condition',
        'description',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'asset');
    }

    public function stockRequests()
    {
        return $this->morphMany(StockRequest::class, 'asset');
    }
}
