<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monument extends Model
{
    use HasFactory;

    protected $filleable = [
        'id', 
        'description', 
        'location',
        'historical_significance', 
        'type', 
        'year_of_construction', 
        'monument_designer', 
        'accessibility', 
        'used_materials', 
        'dimensions', 
        'weight', 
        'cost_to_construct', 
        'images', 
        'audiovisual_sources'
    ];
}
