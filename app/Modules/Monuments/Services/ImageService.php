<?php
namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\Service;
use Illuminate\Validation\Rule;
use App\Models\Image;

class ImageService extends Service
{
        protected $_rules = [
            'images.*.url' => 'required|url',
            'images.*.caption' => 'required|string|max:50',
            'images.*.monument_id' => 'required|numeric'
        ];    

        public function __construct(Image $model) {
            Parent::__construct($model);
        }  

        public function createImages($imagesUrl, $imagesCaption, $monument)
        {            
            foreach ($imagesUrl as $key => $image) {
                $imagesData[] = [
                    'url' => $image,
                    'caption' => $imagesCaption[$key],
                    'monument_id' => $monument->id
                ];

                $this->checkValidation($imagesData);
            }
            
            $monument->images()->createMany($imagesData);
        }

        public function deleteImages($id) {
            $this->_model->where('monument_id', $id)->delete();
        }     
}