<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AudioSourceLanguage extends Model
{
    use HasFactory;
    protected $table = "audiovisual_sources_language";

    protected $fillable = [
        'audiovisual_source_id',
        'title',
        'language'
    ];
}
