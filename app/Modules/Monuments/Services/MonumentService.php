<?php
namespace App\Modules\Movies\Services;

use App\Models\Monument;
use App\Modules\Core\Services\Service;
use App\Modules\Core\Services\ServiceLanguages;
use Illuminate\Validation\Rule;




class MonumentService extends Service
{
        protected $_rules = [
            'id' => 'required',
            'name' => 'required|string|max:50',
            'description' => 'required|string',
            'location.latitude' => 'required|numeric|between:-90,90',
            'location.longitude' => 'required|numeric|between:-180,180',
            'location.street' => 'required|string|max:50',
            'location.number' => 'required|numeric|max:99999',
            'location.city' => 'required|string|max:50',
            'historical_significance' => 'nullable|string',
            'type' => [
                'required',
                'string',
                Rule::in([
                    'War Memorials',
                    'Statues and Sculptures',
                    'Historical Buildings and Sites',
                    'National Monuments',
                    'Archaeological Sites',
                    'Cultural and Religious Monuments',
                    'Public Art Installations',
                    'Memorials for Historical Events',
                    'Natural Monuments,Tombs and Mausoleums',
                ]),
            ],
            'year_of_construction' => 'required|integer|max:2023',
            'monument_designer' => 'required|string|max:50',
            'accessibility' => [
                'required',
                'array',
                Rule::in([
                    'wheelchair-friendly',
                    'near parking areas',
                    'low-slope ramps',
                    'power-assisted doors',
                    'elevators',
                    'accessible washrooms',
                ]),
            ],
            'used_materials' => 'nullable|array',
            'dimensions.height' => 'nullable|numeric',
            'dimensions.width' => 'nullable|numeric',
            'dimensions.depth' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'cost_to_construct' => 'nullable|numeric',
            'images.*.url' => 'required|url',
            'images.*.caption' => 'nullable|string|max:50',
            'audiovisual_source.url' => 'nullable|url',
            'audiovisual_source.type' => [
                'nullable',
                'string',
                Rule::in([
                    'audio',
                    'video',
                ]),
            ],
        ];    

        public function __construct(Monument $model) {
            Parent::__construct($model);
        }

        public function getAllMonuments($pages = 10, $parameterName = null, $parameterValue = null) {
            $monuments = $this->_model->with(['location', 'dimensions', 'audiovisualSource', 'images'])->paginate($pages)->withQueryString();
        
            if (!is_null($parameterName) && !is_null($parameterValue)) {
                $monuments = $monuments->where($parameterName, $parameterValue);
            }
        
            return $monuments;
        }

        public function addMonument($data)
        {
            $this->validate($data);
        
            if ($this->hasErrors()) {
                return;
            }
        
            DB::beginTransaction();
        
            try {
                $location = $this->createLocation($data);
                $dimensions = $this->createDimensions($data);
                $audiovisualSource = $this->createAudiovisualSource($data);
        
                $monumentData = $this->getMonumentData($data, $location->id, $dimensions->id, $audiovisualSource->id);
                $monument = $this->createMonument($monumentData);
        
                $this->createImages($data['images_url'], $data['images_caption'], $monument->id);
        
                DB::commit();
        
                return $monument;
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
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
        
        private function createImages($imagesUrl, $imagesCaption, $monument)
        {
            $images = json_decode($imagesUrl, true);
            $captions = json_decode($imagesCaption, true);
        
            $imagesData = array_map(function ($key, $image) use ($captions, $monument) {
                return [
                    'url' => $image,
                    'caption' => $captions[$key],
                    'monument_id' => $monument->id
                ];
            }, array_keys($images), $images);
        
            $monument->images()->createMany($imagesData);
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