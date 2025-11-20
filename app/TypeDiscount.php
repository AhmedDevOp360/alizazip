<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code',
    ];
}
