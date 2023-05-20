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
        $this->validate($audiovisualSourceLanguageData);

        foreach ($audiovisualSourceLanguageData as $language => $translationData) {

            $audiovisualSourceLanguageDataResult = [
                'title' => $translationData['title'],
                'language' => $translationData['language'],
                'audiovisual_source_id' => $audiovisualSource->id,
            ];

            AudioSourceLanguage::updateOrCreate(
                ['audiovisual_source_id' => $audiovisualSource->id, 'language' => $translationData['language']],
                $audiovisualSourceLanguageDataResult
            );
        }
    }
}