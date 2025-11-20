<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegisterInformation extends Model
{
    protected $table = "cash_register_information";

    protected $fillable = ['business_id','location_id','sales_code','cash_type','plate_number'];
    use HasFactory;

    // public function cash_register(): BelongsTo
    // {
    //     return $this->belongsTo(CashRegister::class);
    // }
}
