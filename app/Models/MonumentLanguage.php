<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonumentLanguage extends Model
{
    use HasFactory;

    protected $table = "monuments_language";

    protected $fillable = [
        'name',
        'description',
        'historical_significance',
        'accessibility',
        'used_materials',
        'language'
    ];

    protected $casts = [
        'used_materials' => 'array',
        'accessibility' => 'array',
    ];

    public function monument()
    {
        return $this->belongsTo(Monument::class, "monument_id", "id");
    }

    protected $attributes = [
        'type' => 'Historical Buildings and Sites',
        'language' => 'English',
    ];
}
