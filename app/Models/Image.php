<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'monument_id',
        'url'
    ];

    public function monument()
    {
        return $this->belongsTo(Monument::class);
    }

    public function imageLanguage(){
        return $this->hasMany(ImageLanguage::class, 'image_id', 'id');
    }
}