<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'latitude',
        'longitude',
        'street',
        'number',
        'city',
    ];

    public function monuments()
    {
        return $this->hasMany(Monument::class);
    }
}
