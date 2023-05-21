<?php
namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\Service;
use App\Models\Dimensions;
use Illuminate\Support\Facades\Log;

class DimensionService extends Service
{
        protected $_rules = [
            'height' => 'required|numeric',
            'width' => 'nullable|numeric',
            'depth' => 'nullable|numeric',
        ];    

        public function __construct(Dimensions $model) {
            Parent::__construct($model);
        }   

        public function getOrCreateDimensions($dimensionsData)
        {
            $this->validate($dimensionsData);
        
            $dimensionData = [
                'height' => $dimensionsData['height'],
                'width' => $dimensionsData['width'],
                'depth' => $dimensionsData['depth']
            ];
        
            $dimensions = $this->_model->firstOrCreate($dimensionData);

            return $dimensions;
        }        
}