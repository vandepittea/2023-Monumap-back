<?php

namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\ServiceLanguages;
use App\Models\ImageLanguage;

class ImageLanguageService extends ServiceLanguages
{
    protected $_rulesTranslations = [
        'images.*.caption' => 'required|string|max:50',
    ];

    public function getOrCreateImageLanguage($imageLanguageData, $image)
    {
        $this->checkValidation($imageLanguageData);

        $imageLanguageDataResult = [
            'caption' => $imageLanguageData['caption'],
            'language' => $imageLanguageData['language'],
        ];

        $imageLanguage = ImageLanguage::updateOrCreate(
            ['image_id' => $image->id, 'language' => $imageLanguageData['language']],
            $imageLanguageDataResult
        );

        return $imageLanguage;
    }
}