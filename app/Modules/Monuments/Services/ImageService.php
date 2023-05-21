<?php

namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\Service;
use App\Modules\Monuments\Services\ImageLanguageService;
use App\Models\Image;

class ImageService extends Service
{
    protected $_rules = [
        '*.url' => 'required|url'
    ];

    protected $_imageLanguageService;

    public function __construct(Image $model, ImageLanguageService $imageLanguageService)
    {
        parent::__construct($model);
        $this->_imageLanguageService = $imageLanguageService;
    }

    public function createImages($imagesData, $monument)
    {    
        foreach ($imagesData as $imageData) {
            $this->validate($imagesData);

            $image = $monument->images()->create(['url' => $imageData['url']]);
            
            $this->_imageLanguageService->getOrCreateImageLanguage($imageData['image_language'], $image);
        }
    }  

    public function deleteImages($monumentId)
    {
        $this->_model->where('monument_id', $monumentId)->delete();
    }
}