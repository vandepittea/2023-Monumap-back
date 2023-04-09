<?php
namespace App\Modules\Monuments\Services;

use App\Models\Monument;
use App\Modules\Core\Services\Service;
use App\Modules\Core\Services\ServiceLanguages;
use App\Modules\Monuments\Services\LocationService;
use App\Modules\Monuments\Services\DimensionService;
use App\Modules\Monuments\Services\AudiovisualSourceService;
use App\Modules\Monuments\Services\ImageService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class MonumentService extends Service
{
        protected $_rules = [
            'id' => 'required',
            'name' => 'required|string|max:50',
            'description' => 'required|string',
            'location' => 'required',
            'historical_significance' => 'nullable|string',
            'type' => 'required|string|in:War Memorials,Statues and Sculptures,Historical Buildings and Sites,National Monuments,Archaeological Sites,Cultural and Religious Monuments,Public Art Installations,Memorials for Historical Events,Natural Monuments,Tombs and Mausoleums',
            'year_of_construction' => 'required|integer|max:2023',
            'monument_designer' => 'required|string|max:50',
            'accessibility' => 'nullable|array|in:wheelchair-friendly,near parking areas,low-slope ramps,power-assisted doors,elevators,accessible washrooms',
            'used_materials' => 'nullable|array',
            'dimensions' => 'nullable',
            'weight' => 'nullable|numeric',
            'cost_to_construct' => 'nullable|numeric',
            'images' => 'required',
            'audiovisual_source' => 'nullable',
            'languages' => 'required|string'
        ];    

        private $_locationService;
        private $_dimensionService;
        private $_audiovisualSourceService;
        private $_imageService;

        public function __construct(Monument $model, LocationService $locationService, DimensionService $dimensionService, AudiovisualSourceService $audiovisualSourceService, ImageService $imageService) {
            Parent::__construct($model);
            $this->_locationService = $locationService;
            $this->_dimensionService = $dimensionService;
            $this->_audiovisualSourceService = $audiovisualSourceService;
            $this->_imageService = $imageService;
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
                $location = $this->_locationService->getOrCreateLocation($data['location']);
                $dimensions = $this->_dimensionService->getOrCreateDimensions($data['dimensions']);
                $audiovisualSource = $this->_audiovisualSourceService->getOrCreateAudiovisualSource($data['audiovisual_source']);
        
                $monumentData = $this->getMonumentData($data, $location->id, $dimensions->id, $audiovisualSource->id);
                $monument = $this->createMonument($monumentData);
        
                $this->_imageService->createImages($data['images']['urls'], $data['images']['captions'], $monument);
        
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
            
                $newLocation = $this->_locationService->getOrCreateLocation($data['location']);
                $newDimensions = $this->_dimensionService->getOrCreateDimensions($data['dimensions']);
                $newAudiovisualSource = $this->_audiovisualSourceService->getOrCreateAudiovisualSource($data['audiovisual_source']);
            
                $monumentData = $this->getMonumentData($data, $newLocation->id, $newDimensions->id, $newAudiovisualSource->id);
                $this->updateMonumentData($monument, $monumentData);
    
                $this->_locationService->deleteUnusedLocations($oldLocationId);
                $this->_dimensionService->deleteUnusedDimensions($oldDimensionsId);
                $this->_audiovisualSourceService->deleteUnusedAudiovisualSources($oldAudiovisualSourceId);
    
                $this->_imageService->deleteImages($id);
                $this->_imageService->createImages($data['images_url'], $data['images_caption'], $id);
    
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
        
        private function updateMonumentData($monument, $monumentData) {
            $monument->update($monumentData);
        }   
}