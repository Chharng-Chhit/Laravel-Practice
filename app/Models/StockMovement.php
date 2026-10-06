<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = ['id', 'product_id', 'quantity', 'type', 'reference', 'note'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
