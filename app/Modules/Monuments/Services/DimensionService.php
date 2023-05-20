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

        public function getOrCreateDimensions($dimensionsData, $monument)
        {
            $this->checkValidation($dimensionsData);
        
            $dimensionData = [
                'height' => $dimensionsData['height'],
                'width' => $dimensionsData['width'],
                'depth' => $dimensionsData['depth']
            ];
        
            //$monument->dimensions()->create($dimensionData);
            return $monument->dimensions()->create($dimensionData); //TODO: goed zo?
        }        
        
        public function deleteUnusedDimensions($oldDimensionsId) {
            Log::info($oldDimensionsId);
            $this->_model->where('id', $oldDimensionsId)->whereDoesntHave('monuments')->delete();
        }
}