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
            'monument_id' => 'required|numeric'
        ];    

        public function __construct(Image $model) {
            Parent::__construct($model);
        }  

        public function createImages($imagesUrl, $imagesCaption, $monument)
        {
            $images = json_decode($imagesUrl, true);
            $captions = json_decode($imagesCaption, true);
            
            foreach ($images as $key => $image) {
                $imagesData[] = [
                    'url' => $image,
                    'caption' => $captions[$key],
                    'monument_id' => $monument->id
                ];

                $this->validate($imagesData);

                if ($this->hasErrors()) {
                    return;
                }
            }
            
            $monument->images()->createMany($imagesData);
        }

        public function deleteImages($id) {
            $this->_model->where('monument_id', $id)->delete();
        }     
}