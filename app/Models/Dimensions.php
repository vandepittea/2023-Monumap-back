<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dimensions extends Model
{
    use HasFactory;

    protected $fillable = [
        'height',
        'width',
        'depth',
    ];

    public function monuments()
    {
        return $this->hasMany(Monument::class);
    }
}
