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
        'accessibility' => 'array',
    ];

    protected $attributes = [
        'type' => 'Historical Buildings and Sites',
        'language' => 'Dutch',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function dimensions()
    {
        return $this->belongsTo(Dimension::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function audiovisualSource()
    {
        return $this->belongsTo(AudiovisualSource::class);
    }

    public function scopeOfType($query, $type) {
        return $query->where('type', $type);
    }

    public function scopeOfYearOfConstruction($query, $year) {
        return $query->where('year_of_construction', $year);
    }
    
    public function scopeOfMonumentDesigner($query, $designer) {
        return $query->where('monument_designer', $designer);
    }

    public function scopeOfCostToConstruct($query, $cost) {
        return $query->where('cost_to_construct', $cost);
    }
    
    public function scopeOfLanguage($query, $language) {
        return $query->where('language', $language);
    }

    public function translationsMonument(){
        return $this->hasMany(MonumentLanguage::class, 'monument_id', 'id');
    }

    public function translationsSource(){
        return $this->hasMany(AudioSourceLanguage::class, 'source_id', 'id');
    }

    public function translationsImage(){
        return $this->hasMany(ImageLanguage::class, 'image_id', 'id');
    }

}
