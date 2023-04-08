<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'monument_id',
        'url',
        'caption',
    ];

    public function monument()
    {
        return $this->belongsTo(Monument::class);
    }
}
