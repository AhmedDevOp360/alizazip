<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashWithdrawal extends Model
{
    protected $fillable = [
        'business_id',
        'location_id',
        'user_id',
        'cash_register_id',
        'amount',
        'note',
    ];

    public function location()
    {
        return $this->belongsTo(BusinessLocation::class, 'location_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cash_register()
    {
        return $this->belongsTo(CashRegister::class, 'cash_register_id');
    }
}


