<?php
namespace App\Modules\Monuments\Services;

use App\Models\Monument;
use App\Modules\Core\Services\Service;
use App\Modules\Monuments\Services\LocationService;
use App\Modules\Monuments\Services\DimensionService;
use App\Modules\Monuments\Services\AudiovisualSourceService;
use App\Modules\Monuments\Services\ImageService;
use Illuminate\Support\Facades\DB;
use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Modules\Core\Services\ServiceLanguages;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class MonumentService extends ServiceLanguages
{
        protected $_rules = [
            'location' => 'required',
            'historical_significance' => 'nullable|string',
            'year_of_construction' => 'required|integer|max:2023',
            'monument_designer' => 'required|string|max:50',
            'dimensions' => 'nullable',
            'weight' => 'nullable|numeric',
            'cost_to_construct' => 'nullable|numeric',
            'images' => 'required',
            'audiovisual_source' => 'nullable',
        ];    

        protected $_rulesTranslations = [
            'name' => 'required|string|max:50',
            'description' => 'required|string',
            'type' => 'required|string|in:War Memorials,Statues and Sculptures,Historical Buildings and Sites,National Monuments,Archaeological Sites,Cultural and Religious Monuments,Public Art Installations,Memorials for Historical Events,Natural Monuments,Tombs and Mausoleums','Oorlogsmonumenten','Beelden en sculpturen','Historische gebouwen en plaatsen','Nationale Monumenten','Archeologische sites','Culturele en religieuze monumenten','Openbare Kunstinstallaties','Herdenkingen voor historische gebeurtenissen','Natuurmonumenten,Graven en Mausolea',
            'accessibility' => 'nullable|array|in:wheelchair-friendly,near parking areas,low-slope ramps,power-assisted doors,elevators,accessible washrooms','rolstoelvriendelijk','dichtbij parkeerplaatsen','hellingen met lage helling','elektrisch bediende deuren','liften', 'toegankelijke toiletten',
            'used_materials' => 'nullable|array',
            'language' => 'required|string'
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

        public function getAllMonuments($pages, $type = null, $year = null, $designer = null, $cost = null, $language = null) {
            $monuments = $this->_model->with(['location', 'dimensions', 'audiovisualSource', 'images'])
                                        ->with('translation')
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
                                       ->paginate($pages)->appends(request()->query());

            $monuments = $this->presentAllWithTranslations($monuments->toArray());

            return $monuments;
        }

        public function addMonument($data)
        {
            $this->checkValidation($data);

            $this->checkIfMonumentAlreadyExists($data['name']);
        
            DB::beginTransaction();
        
            try {
                $location = $this->_locationService->getOrCreateLocation($data['location']);
        
                $monumentData = $this->getMonumentData($data, $location->id);
                $monument = $this->createMonument($monumentData);
        
                if (isset($data['dimensions'])) {
                    $this->_dimensionService->getOrCreateDimensions($data['dimensions'], $monument);
                }
                if (isset($data['audiovisual_source'])) {
                    $this->_audiovisualSourceService->getOrCreateAudiovisualSource($data['audiovisual_source'], $monument);
                }
                
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
            $monument = $this->checkIfMonumentExists($id);

            return ["data" => $monument->with(['location', 'dimensions', 'images', 'audiovisualSource'])];
        }

        public function updateMonument($id, $data)
        {
            $this->checkValidation($data);

            $monument = $this->checkIfMonumentExists($id);

            DB::beginTransaction();

            try {
                $oldLocationId = $monument->location_id;
                $oldDimensionsId = $monument->dimensions_id;
                $oldAudiovisualSourceId = $monument->audiovisual_source_id;
            
                $newLocation = $this->_locationService->getOrCreateLocation($data['location']);
            
                $monumentData = $this->getMonumentData($data, $newLocation->id);
                $this->updateMonumentData($monument, $monumentData);

                if (isset($data['dimensions'])) {
                    $this->_dimensionService->getOrCreateDimensions($data['dimensions'], $monument);
                }
                if (isset($data['audiovisual_source'])) {
                    $this->_audiovisualSourceService->getOrCreateAudiovisualSource($data['audiovisual_source'], $monument);
                }
    
                $this->_imageService->deleteImages($id);
                $this->_imageService->createImages($data['images_url'], $data['images_caption'], $id);

                $this->_locationService->deleteUnusedLocations($oldLocationId);
                $this->_dimensionService->deleteUnusedDimensions($oldDimensionsId);
                $this->_audiovisualSourceService->deleteUnusedAudiovisualSources($oldAudiovisualSourceId);
    
                DB::commit();

                return $monument;
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } 

        public function deleteMonument($id) {
            $monument = $this->checkIfMonumentExists($id);

            if($monument){
                $monument->delete();
            }
        }

        public function deleteMultipleMonuments($ids) {
            $monuments = $this->_model->whereIn('id', $ids)->get();
        
            if ($monuments->count() !== count($ids)) {
                throw new NotFoundException('One or more monuments not found.');
            }
        
            $this->_model->destroy($ids);
        }                          
        
        private function getMonumentData($data, $locationId)
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
        
            return $monumentData;
        }
        
        private function createMonument($monumentData)
        {
            return $this->_model->create($monumentData);
        }        
        
        private function updateMonumentData($monument, $monumentData) {
            $monument->update($monumentData);
        }  
        
        private function checkIfMonumentExists($id){
            $monument = $this->_model->find($id);
            if (!$monument) {
                throw new NotFoundException('Monument not found.');
            }
        
            return $monument;
        }

        private function checkIfMonumentAlreadyExists($name){
            $monument = $this->_model->where('name', $name)->first();
            if ($monument) {
                throw new AlreadyExistsException('Monument already exists.');
            }
        }
}