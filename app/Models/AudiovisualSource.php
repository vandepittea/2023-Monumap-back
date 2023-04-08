<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudiovisualSource extends Model
{
    use HasFactory;

    protected $table = 'audiovisual_source';

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
        return $this->hasOne(Monument::class);
    }
}
