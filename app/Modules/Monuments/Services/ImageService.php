<?php

namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\Service;
use App\Modules\Monuments\Services\ImageLanguageService;
use App\Models\Image;

class ImageService extends Service
{
    protected $_rules = [
        'images.*.url' => 'required|url',
        'images.*.monument_id' => 'required|numeric'
    ];

    protected $imageLanguageService;

    public function __construct(Image $model, ImageLanguageService $imageLanguageService)
    {
        parent::__construct($model);
        $this->imageLanguageService = $imageLanguageService;
    }

    public function createImages($imagesUrl, $imagesCaption, $monument)
    {
        $imagesData = [];

        foreach ($imagesUrl as $key => $image) {
            $imagesData[] = [
                'url' => $image,
                'monument_id' => $monument->id,
                'translations' => [
                    [
                        'caption' => $imagesCaption['en'][$key],
                        'language' => 'en',
                    ],
                    [
                        'caption' => $imagesCaption['nl'][$key],
                        'language' => 'nl',
                    ],
                ],
            ];
        }

        $this->checkValidation($imagesData);

        foreach ($imagesData as $imageData) {
            $image = $monument->images()->create(['url' => $imageData['url']]);

            $this->getOrCreateImageTranslations($imageData['translations'], $image);
        }
    }

    public function deleteImages($monumentId)
    {
        $this->_model->where('monument_id', $monumentId)->delete();
    }

    protected function getOrCreateImageTranslations($translations, $image)
    {
        foreach ($translations as $translation) {
            $this->imageLanguageService->getOrCreateImageLanguage($translation, $image);
        }
    }
}