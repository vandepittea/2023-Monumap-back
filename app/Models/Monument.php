<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monument extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'historical_significance',
        'type',
        'year_of_construction',
        'monument_designer',
        'accessibility',
        'used_materials',
        'weight',
        'cost_to_construct',
        'language',
    ];

    protected $casts = [
        'used_materials' => 'array',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function dimension()
    {
        return $this->belongsTo(Dimension::class);
    }

    public function images()
    {
        return $this->belongsToMany(Image::class);
    }

    public function audiovisualSources()
    {
        return $this->belongsToMany(AudiovisualSource::class);
    }
}
