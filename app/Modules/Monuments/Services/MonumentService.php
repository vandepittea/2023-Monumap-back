<?php
namespace App\Modules\Monuments\Services;

use App\Models\Monument;
use App\Modules\Core\Services\Service;
use App\Modules\Core\Services\ServiceLanguages;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\Location;
use App\Models\Dimension;
use App\Models\Image;
use App\Models\AudiovisualSource;

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
            'type' => 'required|string|in:War Memorials,Statues and Sculptures,Historical Buildings and Sites,National Monuments,Archaeological Sites,Cultural and Religious Monuments,Public Art Installations,Memorials for Historical Events,Natural Monuments,Tombs and Mausoleums',
            'year_of_construction' => 'required|integer|max:2023',
            'monument_designer' => 'required|string|max:50',
            'accessibility' => 'required|array|in:wheelchair-friendly,near parking areas,low-slope ramps,power-assisted doors,elevators,accessible washrooms',
            'used_materials' => 'nullable|array',
            'dimensions.height' => 'nullable|numeric',
            'dimensions.width' => 'nullable|numeric',
            'dimensions.depth' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'cost_to_construct' => 'nullable|numeric',
            'images.*.url' => 'required|url',
            'images.*.caption' => 'nullable|string|max:50',
            'audiovisual_source.title' => 'nullable|string',
            'audiovisual_source.url' => 'nullable|url',
            'audiovisual_source.type' => 'nullable|string|in:audio,video',
        ];    

        public function __construct(Monument $model) {
            Parent::__construct($model);
        }

        public function getAllMonuments($pages = 10, $type = null, $year = null, $designer = null, $cost = null, $language = null) {
            $monuments = $this->_model->with(['location', 'dimensions', 'audiovisualSource', 'images'])
                                       ->when($type, function ($query, $type) {
                                            return $query->ofType($type);
                                        })
                                       ->when($year, function ($query, $year) {
                                            return $query->ofOfYearOfConstruction($year);
                                        })
                                       ->when($designer, function ($query, $designer) {
                                            return $query->OfMonumentDesigner($designer);
                                        })
                                       ->when($cost, function ($query, $cost) {
                                            return $query->ofOfCostToConstruct($cost);
                                        })
                                       ->when($language, function ($query, $language) {
                                            return $query->ofLanguage($language);
                                        })
                                       ->paginate($pages)->withQueryString();
            return $monuments;
        }

        public function addMonument($data)
        {
            $validation = $this->validate($data);

            if ($validation->fails()) {
                return $validation->errors();
            }
        
            DB::beginTransaction();
        
            try {
                $location = $this->getOrCreateLocation($data['location']);
                $dimensions = $this->getOrCreateDimensions($data['dimensions']);
                $audiovisualSource = $this->getOrCreateAudiovisualSource($data['audiovisual_source']);
        
                $monumentData = $this->getMonumentData($data, $location->id, $dimensions->id, $audiovisualSource->id);
                $monument = $this->createMonument($monumentData);
        
                $this->createImages($data['images']['urls'], $data['images']['captions'], $monument);
        
                DB::commit();
        
                return $monument;
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        }

        public function addMultipleMonuments(array $monuments): void
        {
            DB::beginTransaction();
    
            try {
                foreach ($monuments as $monumentData) {
                    $this->addMonument($monumentData);
                }
    
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollback();
                throw $e;
            }
        }

        public function getOneMonument($id){
            return ["data" => $this->_model->with(['location', 'dimensions', 'images', 'audiovisualSource'])->find($id)];
        }

        public function updateMonument($id, $data)
        {
            $this->validate($data);

            if ($this->hasErrors()) {
                return;
            }

            $monument = $this->_model->find($id);
            if (!$monument) {
                return;
            }

            DB::beginTransaction();

            try {
                $oldLocationId = $monument->location_id;
                $oldDimensionsId = $monument->dimensions_id;
                $oldAudiovisualSourceId = $monument->audiovisual_source_id;
            
                $newLocation = $this->getOrCreateLocation($data['location']);
                $newDimensions = $this->getOrCreateDimensions($data['dimensions']);
                $newAudiovisualSource = $this->getOrCreateAudiovisualSource($data['audiovisual_source']);
            
                $monumentData = $this->getMonumentData($data, $newLocation->id, $newDimensions->id, $newAudiovisualSource->id);
                $this->updateMonumentData($monument, $monumentData);
    
                $this->deleteUnusedLocations($oldLocationId);
                $this->deleteUnusedDimensions($oldDimensionsId);
                $this->deleteUnusedAudiovisualSources($oldAudiovisualSourceId);
    
                $this->deleteImages($id);
                $this->createImages($data['images_url'], $data['images_caption'], $id);
    
                DB::commit();

                return $monument;
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } 

        public function deleteMonument($id) {
            $monument = $this->_model->find($id);

            if($monument){
                $monument->delete();
            }
        }

        public function deleteMultipleMonuments($ids) {
            $this->_model->destroy($ids);
        }

        private function getOrCreateLocation($locationData)
        {
            $location = Location::firstOrCreate(
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
        
        private function getOrCreateDimensions($dimensionsData)
        {
            $dimensions = Dimension::firstOrCreate([
                    'height' => $dimensionsData['height'],
                    'width' => $dimensionsData['width'],
                    'depth' => $dimensionsData['depth']
                ]
            );
        
            return $dimensions;
        }        
        
        private function getOrCreateAudiovisualSource($audiovisualSourceData)
        {
            $audiovisualSourceResult = array_filter([
                'title' => $audiovisualSourceData['title'],
                'url' => $audiovisualSourceData['url'],
                'type' => $audiovisualSourceData['type']
            ]);
        
            $audiovisualSource = AudiovisualSource::firstOrCreate($audiovisualSourceResult);
        
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
            
            foreach ($images as $key => $image) {
                $imagesData[] = [
                    'url' => $image,
                    'caption' => $captions[$key],
                    'monument_id' => $monument->id
                ];
            }
            
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