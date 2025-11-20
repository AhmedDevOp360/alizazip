<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegister extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'denominations' => 'array',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the Cash registers transactions.
     */
    public function cash_register_transactions()
    {
        return $this->hasMany(\App\CashRegisterTransaction::class);
    }

    public function cash_register_information(): BelongsTo
    {
        return $this->belongsTo(CashRegisterInformation::class);
    }
}
