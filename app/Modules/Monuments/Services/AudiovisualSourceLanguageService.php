<?php

namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\ServiceLanguages;
use App\Models\AudioSourceLanguage;

class AudiovisualSourceLanguageService extends ServiceLanguages
{
    protected $_rulesTranslations = [
        'audiovisual_source' => 'required',
        'title' => 'required|string',
        'language' => 'required|string'
    ];

    public function getOrCreateAudiovisualSourceLanguage($audiovisualSourceLanguageData, $audiovisualSource)
    {
        $this->checkValidation($audiovisualSourceLanguageData);

        $audiovisualSourceLanguageDataResult = [
            'audiovisual_source' => $audiovisualSourceLanguageData['audiovisual_source'],
            'title' => $audiovisualSourceLanguageData['title'],
            'language' => $audiovisualSourceLanguageData['language']
        ];

        $audiovisualSourceLanguage = AudioSourceLanguage::updateOrCreate(
            ['audiovisual_source' => $audiovisualSourceLanguageData['audiovisual_source'], 'language' => $audiovisualSourceLanguageData['language']],
            $audiovisualSourceLanguageDataResult
        );

        return $audiovisualSourceLanguage;
    }
}