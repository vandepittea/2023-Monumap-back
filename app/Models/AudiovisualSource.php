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

    protected $attributes = [
        'type' => 'video',
    ];

    public function monument()
    {
        return $this->hasMany(Monument::class); //TODO: check if this is correct
    }

    public function audiovisualSourceLanguage(){
        return $this->hasMany(AudiovisualSourceLanguage::class, 'audiovisual_source_id', 'id');
    }
}
