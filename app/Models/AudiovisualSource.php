<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudiovisualSource extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'url',
        'type',
    ];

    public function monuments()
    {
        return $this->belongsToMany(Monument::class);
    }
}
