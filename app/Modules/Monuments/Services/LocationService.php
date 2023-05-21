<?php
namespace App\Modules\Monuments\Services;

use App\Modules\Core\Services\Service;
use App\Models\Location;

class LocationService extends Service
{
        protected $_rules = [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'street' => 'nullable|string|max:50',
            'number' => 'nullable|numeric|max:99999',
            'city' => 'required|string|max:50',
        ];    

        public function __construct(Location $model) {
            Parent::__construct($model);
        }   
        
        public function getOrCreateLocation($locationData)
        {
            $this->validate($locationData);

            $location = $this->_model->firstOrCreate(
                [
                    'latitude' => $locationData['latitude'],
                    'longitude' => $locationData['longitude'],
                    'city' => $locationData['city'],
                    'street' => $locationData['street'] ?? null,
                    'number' => $locationData['number'] ?? null,
                ]
            );
            
            return $location;
        }       
}