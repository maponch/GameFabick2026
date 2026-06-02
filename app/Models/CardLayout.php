<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CardLayout extends Model
{
    protected $fillable = ['slug', 'name', 'description', 'schema'];

    protected $casts = [
        'schema' => 'array',
    ];
}