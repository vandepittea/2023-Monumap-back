<?php
namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\Service;
use Illuminate\Validation\Rule;
use App\Models\Dimension;

class DimensionService extends Service
{
        protected $_rules = [
            'height' => 'required|numeric',
            'width' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
        ];    

        public function __construct(Dimension $model) {
            Parent::__construct($model);
        }   

        public function getOrCreateDimensions($dimensionsData, $monument)
        {
            $this->validate($dimensionsData);
        
            if ($this->hasErrors()) {
                return;
            }
        
            $dimensionData = [
                'height' => $dimensionsData['height'],
                'width' => $dimensionsData['width'],
                'depth' => $dimensionsData['depth']
            ];
        
            $monument->dimension()->create($dimensionData);
        }        
        
        public function deleteUnusedDimensions($oldDimensionsId) {
            $this->_model->where('id', $oldDimensionsId)->whereDoesntHave('monuments')->delete();
        }
}