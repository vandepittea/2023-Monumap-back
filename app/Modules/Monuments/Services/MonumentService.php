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
        
            $location = $this->getOrCreateLocation($data);
            $dimensions = $this->getOrCreateDimensions($data);
            $audiovisualSource = $this->getOrCreateAudiovisualSource($data);

            $monumentData = $this->getMonumentData($data, $location->id, $dimensions->id, $audiovisualSource->id);
            $monument = $this->createMonument($monumentData);

            $this->createImages($data['images_url'], $data['images_caption'], $monument->id);
        
            return $monument;
        }

        public function getOneMonument($id){
            return ["data" => $this->_model->with(['location', 'dimensions', 'images', 'audiovisualsource'])->find($id)];
        }

        public function updateMonument($id, $data){
            $this->validate($data);
        
            if($this->hasErrors()){
                return;
            }
        
            $monument = $this->_model->find($id);
            if (!$monument) {
                return;
            }
        
            $oldLocationId = $monument->location_id;
            $oldDimensionsId = $monument->dimensions_id;
            $oldAudiovisualSourceId = $monument->audiovisual_source_id;
        
            $newLocation = $this->getOrCreateLocation($data);
            $newDimensions = $this->getOrCreateDimensions($data);
            $newAudiovisualSource = $this->getOrCreateAudiovisualSource($data);
        
            $monumentData = $this->getMonumentData($data, $newLocation->id, $newDimensions->id, $newAudiovisualSource->id);
            $this->updateMonumentData($monument, $monumentData);

            $this->deleteUnusedLocations($oldLocationId);
            $this->deleteUnusedDimensions($oldDimensionsId);
            $this->deleteUnusedAudiovisualSources($oldAudiovisualSourceId);

            $this->deleteImages($id);
            $this->createImages($data['images_url'], $data['images_caption'], $id);
        }

        public function deleteMonument($id) {
            $monument = $this->_model->find($id);

            if($monument){
                $monument->delete();
            }
        }

        private function getOrCreateLocation($data)
        {
            $location = Location::firstOrCreate(
                [
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'city' => $data['city'],
                    'street' => $data['street'] ?? null,
                    'number' => $data['number'] ?? null,
                ]
            );
        
            return $location;
        }        
        
        private function getOrCreateDimensions($data)
        {
            $dimensions = Dimension::firstOrCreate([
                'height' => $data['height'],
                'width' => $data['width'],
                'depth' => $data['depth']
            ], $data);
        
            return $dimensions;
        }        
        
        private function getOrCreateAudiovisualSource($data)
        {
            $audiovisualSourceData = array_filter([
                'title' => $data['audiovisual_title'],
                'url' => $data['audiovisual_url'],
                'type' => $data['audiovisual_type']
            ]);
        
            $audiovisualSource = AudiovisualSource::firstOrCreate($audiovisualSourceData);
        
            return $audiovisualSource;
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
        
        private function updateMonumentData($monument, $monumentData) {
            $monument->update($monumentData);
        }
        
        private function deleteUnusedLocations($oldLocationId) {
            Location::where('id', $oldLocationId)->whereDoesntHave('monuments')->delete();
        }
        
        private function deleteUnusedDimensions($oldDimensionsId) {
            Dimension::where('id', $oldDimensionsId)->whereDoesntHave('monuments')->delete();
        }
        
        private function deleteUnusedAudiovisualSources($oldAudiovisualSourceId) {
            AudiovisualSource::where('id', $oldAudiovisualSourceId)->whereDoesntHave('monuments')->delete();
        }
        
        private function deleteImages($id) {
            Image::where('monument_id', $id)->delete();
        }        
}