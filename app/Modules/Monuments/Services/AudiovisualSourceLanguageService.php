<?php

namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\ServiceLanguages;
use App\Models\AudioSourceLanguage;

class AudiovisualSourceLanguageService extends ServiceLanguages
{
    protected $_rules = [
        'title' => 'required|string',
        'language' => 'required|string'
    ];

    public function getOrCreateAudiovisualSourceLanguage($audiovisualSourceLanguageData, $audiovisualSource)
    {
        $this->validate($audiovisualSourceLanguageData);
    
        foreach ($audiovisualSourceLanguageData as $translationData) {
            $language = $translationData['language'];
    
            $audiovisualSourceLanguageDataResult = [
                'title' => $translationData['title'],
                'language' => $language,
                'audiovisual_source_id' => $audiovisualSource->id,
            ];
    
            AudioSourceLanguage::updateOrCreate(
                [
                    'audiovisual_source_id' => $audiovisualSource->id,
                    'language' => $language
                ],
                $audiovisualSourceLanguageDataResult
            );
        }
    }    
}