<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['id', 'sale_id', 'payment_method', 'amount', 'reference_no', 'paid_at'];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
