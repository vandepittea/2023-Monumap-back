<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudiovisualSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'type',
    ];

    protected $attributes = [
        'type' => 'video'
    ];

    public function monuments()
    {
        return $this->hasMany(Monument::class);
    }

    public function audiovisualSourceLanguage(){
        return $this->hasMany(AudioSourceLanguage::class, 'audiovisual_source_id', 'id');
    }
}