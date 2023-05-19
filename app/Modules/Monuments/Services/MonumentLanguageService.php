<?php
namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\Service;
use App\Modules\Core\Services\ServiceLanguages;
use Illuminate\Support\Facades\Log;


class MonumentLanguageService extends ServiceLanguages { //TODO: moet ik hier extenden van ServiceLanguages? 
    protected $_rulesTranslations = [
        'name' => 'required|string|max:50',
        'description' => 'required|string',
        'historical_significance' => 'nullable|string',
        'type' => 'required|string|in:War Memorials,Statues and Sculptures,Historical Buildings and Sites,National Monuments,Archaeological Sites,Cultural and Religious Monuments,Public Art Installations,Memorials for Historical Events,Natural Monuments,Tombs and Mausoleums','Oorlogsmonumenten','Beelden en sculpturen','Historische gebouwen en plaatsen','Nationale Monumenten','Archeologische sites','Culturele en religieuze monumenten','Openbare Kunstinstallaties','Herdenkingen voor historische gebeurtenissen','Natuurmonumenten,Graven en Mausolea',
        'accessibility' => 'nullable|array|in:wheelchair-friendly,near parking areas,low-slope ramps,power-assisted doors,elevators,accessible washrooms','rolstoelvriendelijk','dichtbij parkeerplaatsen','hellingen met lage helling','elektrisch bediende deuren','liften', 'toegankelijke toiletten',
        'used_materials' => 'nullable|array',
        'language' => 'required|string'
    ];

    public function getOrCreateMOnumentLanguage($monumentLanguageData, $monument)
    {
        Log::Info('MonumentLanguageService getOrCreateMonumentLanguage');

        $this->checkValidation($monumentLanguageData);
    
        $monumentLanguageDataResult = [
            'language' => $monumentLanguageData['language'],
            'name' => $monumentLanguageData['name'],
            'description' => $monumentLanguageData['description'],
            'type' => $monumentLanguageData['type'],
            'accessibility' => $monumentLanguageData['accessibility'],
            'used_materials' => $monumentLanguageData['used_materials']
        ];
    
        $monument->monumnetLanguage()->create($monumentLanguageDataResult); //TODO: is dit correct?
    }  
    
    
        public function scopeOfType($query, $type) {
        return $query->where('type', $type);
    }
    
}