<?php
namespace App\Modules\Movies\Services;

use App\Models\Monument;
use App\Modules\Core\Services\Service;
use App\Modules\Core\Services\ServiceLanguages;
use Illuminate\Validation\Rule;




class MonumentService extends Service
{
    protected $_rules = [
        "id" => "required",
        "name" => "required|string|max:50",
        "description" => "required|string",
        "location" => "required|json",
        "historicalSignificance" => "nullable|string",
        "type" => [
            "required", "string",
            Rule::in([
                'War Memorials',
                'Statues and Sculptures',
                'Historical Buildings and Sites',
                'National Monuments',
                'Archaeological Sites',
                'Cultural and Religious Monuments',
                'Public Art Installations',
                'Memorials for Historical Events',
                'Natural Monuments,Tombs and Mausoleums'
            ])
        ],
        "yearOfContstruction" => "required|year",
        "monumentDesigner" => "required|string",
        "accessibility" => [
            "required",
            "array",
            Rule::in([
                'wheelchair-friendly',
                'near parking areas',
                'low-slope ramps',
                'power-assisted doors',
                'elevators, accessible washrooms'
            ])
        ],
        "usedMaterials" => "nullable|array",
        "dimensions" => "nullable|json",
        "weight" => "nullable|integer",
        "costToConstruct" => "nullable|numeric",
        "images" => "required|json",
        "audiovisualSource" => "nullable|json"
    ];

        public function __construct(Monument $model) {
            Parent::__construct($model);
        }

        public function addMonument($data)
        {
            $this->validate($data);
        
            if ($this->hasErrors()) {
                return;
            }
        
            $location = $this->getLocation($data);
            $dimensions = $this->getDimensions($data);
            $audiovisualSource = $this->getAudiovisualSource($data);

            $monumentData = $this->getMonumentData($data, $location->id, $dimensions->id, $audiovisualSource->id);
            $monument = $this->createMonument($monumentData);
            
            $this->createImages($data['images_url'], $data['images_caption'], $monument->id);
        
            return $monument;
        }
        
        private function getLocation($data)
        {
            $locationQuery = Location::where('latitude', $data['latitude'])
                ->where('longitude', $data['longitude'])
                ->where('city', $data['city']);
        
            if (isset($data['street'])) {
                $locationQuery->where('street', $data['street']);
            }
        
            if (isset($data['number'])) {
                $locationQuery->where('number', $data['number']);
            }
        
            $location = $locationQuery->first();
        
            if (!$location) {
                $location = Location::create($data);
            }
        
            return $location;
        }
        
        private function getDimensions($data)
        {
            $dimensionsQuery = Dimension::where('height', $data['height'])
                ->where('width', $data['width'])
                ->where('depth', $data['depth']);
        
            $dimensions = $dimensionsQuery->first();
        
            if (!$dimensions) {
                $dimensions = Dimension::create($data);
            }
        
            return $dimensions;
        }
        
        private function getAudiovisualSource($data)
        {
            $audiovisualSourceData = array_filter([
                'title' => $data['audiovisual_title'],
                'url' => $data['audiovisual_url'],
                'type' => $data['audiovisual_type']
            ]);
        
            return AudiovisualSource::create($audiovisualSourceData);
        }
        
        private function getMonumentData($data, $locationId, $dimensionsId, $audiovisualSourceId)
        {
            $monumentData = array_intersect_key($data, array_flip([
                'name',
                'description',
                'historical_significance',
                'type',
                'year_of_construction',
                'monument_designer',
                'accessibility',
                'used_materials',
                'weight',
                'cost_to_construct',
                'language'
            ]));
        
            $monumentData['location_id'] = $locationId;
            $monumentData['dimensions_id'] = $dimensionsId;
            $monumentData['audiovisual_source_id'] = $audiovisualSourceId;
        
            return $monumentData;
        }
        
        private function createMonument($monumentData)
        {
            return $this->_model->create($monumentData);
        }
        
        private function createImages($imagesUrl, $imagesCaption, $monumentId)
        {
            $images = json_decode($imagesUrl, true);
            $captions = json_decode($imagesCaption, true);
        
            foreach ($images as $key => $image) {
                Image::create([
                    'monument_id' => $monumentId,
                    'url' => $image,
                    'caption' => $captions[$key],
                ]);
            }
        }                       

        public function getOneMonument($id){
            return ["data" => $this->_model->find($id)];
        }

        public function updateMonument($id, $data){
            $this->validate($data);
            if($this->hasErrors()){
                return;
            }

            return $this->_model->where("id", $id)->update($data);
        }

        public function deleteMonument($id) {
            $monument = $this->_model->find($id);

            if($monument){
                $monument->delete();
            }
        }
        
}