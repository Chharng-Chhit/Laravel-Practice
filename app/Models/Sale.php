<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sale extends Model
{
    protected $fillable = ['invoice_no', 'cashier_id', 'discounts', 'total', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
