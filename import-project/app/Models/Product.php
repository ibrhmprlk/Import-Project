<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $primaryKey = 'api_id';
    public $incrementing = false;
    protected $keyType = 'bigInteger';

    protected $fillable = [
        'api_id',
        'title',
        'price',
        'description',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];
}