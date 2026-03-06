<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyTarget extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'year_month', 'target_amount'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}