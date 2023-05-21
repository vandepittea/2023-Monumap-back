<?php

namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\ServiceLanguages;
use App\Models\ImageLanguage;

class ImageLanguageService extends ServiceLanguages
{
    protected $_rules = [
        'caption' => 'required|string|max:50',
    ];

    public function getOrCreateImageLanguage($imageLanguageData, $image)
    {
        $this->validate($imageLanguageData);
    
        foreach ($imageLanguageData as $translationData) {
            $language = $translationData['language'];
    
            $imageLanguageDataResult = [
                'caption' => $translationData['caption'],
                'language' => $language,
                'image_id' => $image->id,
            ];
    
            ImageLanguage::updateOrCreate(
                [
                    'image_id' => $image->id,
                    'language' => $language
                ],
                $imageLanguageDataResult
            );
        }
    }    
}