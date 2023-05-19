<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonumentLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location_id',
        'historical_significance',
        'year_of_construction',
        'monument_designer',
        'accessibility',
        'used_materials',
        'weight',
        'cost_to_construct',
    ];

    protected $casts = [
        'used_materials' => 'array',
        'accessibility' => 'array',
    ];
    
    protected $table = "monuments_language";

    public function monument()
    {
        return $this->belongsTo(Monument::class, "monument_id", "id");
    }

    protected $attributes = [
        'type' => 'Historical Buildings and Sites',
        'language' => 'Dutch',
    ];
}
