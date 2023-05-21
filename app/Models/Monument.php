<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monument extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'dimensions_id',
        'audiovisual_source_id',
        'year_of_construction',
        'monument_designer',
        'weight',
        'cost_to_construct',
    ];


    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function dimensions()
    {
        return $this->belongsTo(Dimensions::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function audiovisualSource()
    {
        return $this->belongsTo(AudiovisualSource::class);
    }

    public function monumentLanguage(){
        return $this->hasMany(MonumentLanguage::class, 'monument_id', 'id');
    }

    public function scopeOfYearOfConstruction($query, $year) {
        return $query->where('year_of_construction', $year);
    }
    
    public function scopeOfMonumentDesigner($query, $designer)
    {
        return $query->where('monument_designer', 'LIKE', '%' . $designer . '%');
    }    

    public function scopeOfCostToConstruct($query, $cost) {
        return $query->where('cost_to_construct', $cost);
    }

    public function scopeOfName($query, $name)
    {
        return $query->whereHas('monumentLanguage', function ($query) use ($name) {
            $query->where('name', 'LIKE', "%$name%");
        });
    }

    public function scopeOfType($query, $type)
    {
        return $query->whereHas('monumentLanguage', function ($query) use ($type) {
            $query->where('type', $type);
        });
    }

    public function scopeOfLanguage($query, $language)
    {
        return $query->whereHas('MonumentLanguage', function ($query) use ($language) {
            $query->where('language', $language);
        })->with(['images.imageLanguage' => function ($query) use ($language) {
            $query->where('language', $language);
        }, 'audiovisualSource.audiovisualSourceLanguage' => function ($query) use ($language) {
            $query->where('language', $language);
        },
        'monumentLanguage' => function ($query) use ($language) {
            $query->where('language', $language);
        }]);
    }
}
