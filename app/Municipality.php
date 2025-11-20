<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id', 'name', 'code', 'codefacturador',
    ];

    // protected $with = [
    //     'department',
    // ];
}
