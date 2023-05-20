<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageLanguage extends Model
{
    use HasFactory;

    protected $table = "images_language";

    protected $fillable = [
        'image_id',
        'caption',
        'language'
    ];

    public function image()
    {
        return $this->belongsTo(Image::class, "image_id", "id");
    }
}
