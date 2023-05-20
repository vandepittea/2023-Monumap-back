<?php

namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\ServiceLanguages;
use App\Models\MonumentLanguage;
use Illuminate\Support\Facades\Log;

class MonumentLanguageService extends ServiceLanguages
{
    protected $_rulesTranslations = [
        'name' => 'required|string|max:50',
        'description' => 'required|string',
        'historical_significance' => 'nullable|string',
        'type' => 'required|string|in:War Memorials,Statues and Sculptures,Historical Buildings and Sites,National Monuments,Archaeological Sites,Cultural and Religious Monuments,Public Art Installations,Memorials for Historical Events,Natural Monuments,Tombs and Mausoleums',
        'accessibility' => 'nullable|array|in:wheelchair-friendly,near parking areas,low-slope ramps,power-assisted doors,elevators,accessible washrooms',
        'used_materials' => 'nullable|array',
        'language' => 'required|string|in:English,Dutch'
    ];

    public function getOrCreateMonumentLanguage($monumentLanguageData, $monument)
    {
        $this->checkValidation($monumentLanguageData);

        $monumentLanguageDataResult = [
            'language' => $monumentLanguageData['language'],
            'name' => $monumentLanguageData['name'],
            'description' => $monumentLanguageData['description'],
            'type' => $monumentLanguageData['type'],
            'accessibility' => $monumentLanguageData['accessibility'] ?? null,
            'used_materials' => $monumentLanguageData['used_materials'] ?? null
        ];

        $monument->monumentLanguage()->updateOrCreate(
            ['language' => $monumentLanguageData['language']],
            $monumentLanguageDataResult
        );
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}