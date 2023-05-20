<?php
namespace App\Modules\Monuments\Services;

use App\Models\Monument;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Core\Services\Service;
use App\Modules\Monuments\Services\LocationService;
use App\Modules\Monuments\Services\DimensionService;
use App\Modules\Monuments\Services\AudiovisualSourceService;
use App\Modules\Monuments\Services\MonumentLanguageService;
use App\Modules\Monuments\Services\ImageService;
use Illuminate\Support\Facades\DB;
use App\Exceptions\AlreadyExistsException;
use App\Exceptions\NotFoundException;
use App\Models\AudioSourceLanguage;
use App\Models\AudiovisualSource;
use App\Models\ImageLanguage;
use App\Models\MonumentLanguage;
use App\Models\Image;
use App\Modules\Core\Services\ServiceLanguages;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class MonumentService extends Service
{
        protected $_rules = [
            'location' => 'required',
            'year_of_construction' => 'required|integer|max:2023',
            'monument_designer' => 'required|string|max:50',
            'dimensions' => 'nullable',
            'weight' => 'nullable|numeric',
            'cost_to_construct' => 'nullable|numeric',
            'images' => 'required',
            'audiovisual_source' => 'nullable',
        ];    

        private $_locationService;
        private $_dimensionService;
        private $_audiovisualSourceService;
        private $_monumentLanguageService;
        private $_imageService;

        public function __construct(Monument $model, LocationService $locationService, DimensionService $dimensionService) {
            Parent::__construct($model);
            $this->_locationService = $locationService;
            $this->_dimensionService = $dimensionService;
            $this->_audiovisualSourceService = new AudioVisualSourceService(new AudiovisualSource(), new AudioVisualSourceLanguageService(new AudioSourceLanguage()));
            $this->_imageService = new ImageService(new Image(), new ImageLanguageService(new ImageLanguage()));
            $this->_monumentLanguageService = new monumentLanguageService($model);
        }

        public function getAllMonuments($perPage, $page, $type = null, $year = null, $designer = null, $cost = null, $language = null)
        {
            $query = $this->_model->with(['location', 'dimensions', 'images']);

            if ($type) {
                $query->whereHas('MonumentLanguage', function ($query) use ($type) {
                    $query->where('type', $type);
                });
            }    

            if ($year) {
                $query->OfYearOfConstruction($year);
            }
            
            if ($designer) {
                $query->OfMonumentDesigner($designer);
            }

            if ($cost) {
                $query->OfCostToConstruct($cost);
            }

           /* if ($language) { //TODO: wegdoen
                $query->OfLanguage($language);
            }*/

            $paginator = $query->paginate($perPage, ['*'], 'page', $page)->appends(request()->query());
       
            return $paginator;
        }

        public function addMonument($data)
        {
            $this->checkValidation($data);

            $this->checkIfMonumentAlreadyExists('en', $data['translations']);
        
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
                
                $this->_imageService->createImages($data['images'], $monument);
        
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
            
            $monument->load(['location', 'dimensions', 'images', 'audiovisualSource', 'MonumentLanguage', 'translationsSource', 'translationsImage']);
            return ["data" => $monument];
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
                $this->_imageService->createImages($data['images']['urls'], $data['images']['captions']['en'], $data['images']['captions']['nl'], $monument);

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
                'translations'
            ]));
        
            $monumentData['location_id'] = $locationId;
        
            return $monumentData;
        }
        
        private function createMonument($monumentData)
        {
            $monument = $this->_model->create($monumentData);
            $this->_monumentLanguageService->getOrCreateMonumentLanguage($monumentData['translations'], $monument);
            return $monument;
        }        
        
        private function updateMonumentData($monument, $monumentData) {
            $monument->update($monumentData);
            $this->_monumentLanguageService->getOrCreateMonumentLanguage($monumentData['translations'], $monument);
        }
        
        private function checkIfMonumentExists($id){
            $monument = $this->_model->find($id);
            if (!$monument) {
                throw new NotFoundException('Monument not found.');
            }
        
            return $monument;
        }

        private function checkIfMonumentAlreadyExists($language, $name)
        {
            $monument = $this->_model->whereHas('monumentLanguage', function ($query) use ($language, $name) {
                $query->where('language', $language)->where('name', $name);
            })->first();

            if ($monument) {
                throw new AlreadyExistsException('Monument already exists.');
            }
        }
}