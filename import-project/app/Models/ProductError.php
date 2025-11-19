<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductError extends Model
{
    // Hangi alanların toplu atama (mass assignment) yoluyla doldurulabileceğini belirtir.
    protected $fillable = [
        'api_id',
        'raw_data',
        'errors',
    ];
    
    // raw_data ve errors alanları veritabanında JSON tipi olduğu için (veya JSON stringi olarak saklanacağı için)
    // bunları PHP içinde otomatik array/objeye çevirmek için cast ederiz:
    protected $casts = [
        'raw_data' => 'array',
        'errors' => 'array',
    ];
}