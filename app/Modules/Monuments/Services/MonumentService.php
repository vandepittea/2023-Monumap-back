<?php
namespace App\Modules\Monuments\Services;

use App\Models\Monument;
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
use App\Models\Image;

class MonumentService extends Service
{
        protected $_rules = [
            'location' => 'required',
            'year_of_construction' => 'required|integer',
            'monument_designer' => 'required|string',
            'dimensions' => 'nullable',
            'weight' => 'nullable|numeric',
            'cost_to_construct' => 'nullable|numeric',
            'images' => 'required',
            'images.*.image_language' => 'required|array|min:2',
            'audiovisual_source' => 'nullable',
            'audiovisual_source.audiovisual_source_language' => 'nullable|array|min:2',
            'monument_language' => 'required|array|min:2',
            'monument_language.*.name' => 'required|string|max:50',
            'monument_language.*.description' => 'required|string',
            'monument_language.*.historical_significance' => 'nullable|string',
            'monument_language.*.type' => 'required|string|in:War Memorials,Statues and Sculptures,Historical Buildings and Sites,National Monuments,Archaeological Sites and Cultural and Religious Monuments,Public Art Installations,Memorials for Historical Events,Natural Monuments,Tombs and Mausoleums,Oorlogsmonumenten,Beelden en Sculpturen,Historische Gebouwen en Plaatsen,Nationale Monumenten,Archeologische Plaatsen,Culturele en Religieuze Monumenten,Openbare Kunstinstallaties,Gedenktekens voor Historische Evenementen,Natuurmonumenten en Graven en Mausoleums',
            'monument_language.*.accessibility' => 'nullable|array|in:wheelchair-friendly,near parking areas,low-slope ramps,power-assisted doors and elevators,accessible washrooms,rolstoelvriendelijk,in de buurt van parkeerterreinen,laaghellende opritten,elektrisch ondersteunde deuren en liften,toegankelijke toiletten',
            'monument_language.*.used_materials' => 'nullable|array',
            'monument_language.*.language' => 'required|string|in:English,Dutch',
            'location.latitude' => 'required|numeric|between:-90,90',
            'location.longitude' => 'required|numeric|between:-180,180',
            'location.street' => 'nullable|string|max:50',
            'location.number' => 'nullable|numeric|max:99999',
            'location.city' => 'required|string|max:50',
            'images.*.url' => 'required|url',
            'images.*.image_language.*.caption' => 'required|string|max:50',
            'dimensions.height' => 'nullable|numeric',
            'dimensions.width' => 'nullable|numeric',
            'dimensions.depth' => 'nullable|numeric',
            'audiovisual_source.url' => 'nullable|url',
            'audiovisual_source.type' => 'nullable|string|in:audio,video',
            'audiovisual_source.*.title' => 'nullable|string',
            'audiovisual_source.*.language' => 'nullable|string'
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

        public function getAllMonuments($perPage, $page, $name = null, $type = null, $year = null, $designer = null, $cost = null, $language = null)
        {
            $query = $this->_model->with('location', 'dimensions', 'images', 'audiovisualSource', 'monumentLanguage', 'images.imageLanguage', 'audiovisualSource.audiovisualSourceLanguage');

            if ($name) {
                $query->OfName($name);
            }

            if ($type) {
                $query->OfType($type);
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

            if ($language) {
                $query->OfLanguage($language);
            }
                                       
            $paginator = $query->paginate($perPage, ['*'], 'page', $page)->appends(request()->query());
       
            return $paginator;
        }

        public function addMonument($data)
        {
            $this->validate($data);

            $englishMonument = $this->getEnglishMonumentName($data['monument_language']);
            $this->checkIfMonumentAlreadyExists('English', $englishMonument);
        
            DB::beginTransaction();
        
            try {
                $location = $this->_locationService->getOrCreateLocation($data['location']);
        
                $monumentData = $this->getMonumentData($data, $location->id);
                $monument = $this->createMonument($monumentData);
        
                if (isset($data['dimensions'])) {
                    $dimensions = $this->_dimensionService->getOrCreateDimensions($data['dimensions']);
                    $this->updateMonumentDimensions($monument, $dimensions);
                }
                if (isset($data['audiovisual_source'])) {
                    $audiovisualSource = $this->_audiovisualSourceService->getOrCreateAudiovisualSource($data['audiovisual_source']);
                    $this->updateMonumentAudiovisualSource($monument, $audiovisualSource);
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

        public function getOneMonument($id, $language)
        {
            $monument = $this->checkIfMonumentExists($id);
            
            $monument->load([
                'location',
                'dimensions',
                'images',               
                'audiovisualSource',
                'monumentLanguage' => function ($query) use ($language) {
                    if ($language) {
                        $query->where('language', $language);
                    }
                },
                'images.imageLanguage' => function ($query) use ($language) {
                    if ($language) {
                        $query->where('language', $language);
                    }
                },
                'audiovisualSource.audiovisualSourceLanguage' => function ($query) use ($language) {
                    if ($language) {
                        $query->where('language', $language);
                    }
                },
            ]);
        
            return ["data" => $monument];
        }                             

        public function updateMonument($id, $data)
        {
            $this->validate($data); 

            $monument = $this->checkIfMonumentExists($id);

            DB::beginTransaction();

            try {            
                $newLocation = $this->_locationService->getOrCreateLocation($data['location']);
            
                $monumentData = $this->getMonumentData($data, $newLocation->id);
                $this->updateMonumentData($monument, $monumentData); 

                if (isset($data['dimensions'])) {
                    $dimensions = $this->_dimensionService->getOrCreateDimensions($data['dimensions']);
                    $this->updateMonumentDimensions($monument, $dimensions);
                }
                if (isset($data['audiovisual_source'])) {
                    $audiovisualSource = $this->_audiovisualSourceService->getOrCreateAudiovisualSource($data['audiovisual_source']);
                    $this->updateMonumentAudiovisualSource($monument, $audiovisualSource);
                }
    
                $this->_imageService->deleteImages($id);
                $this->_imageService->createImages($data['images'], $monument);

                $this->_locationService->deleteUnusedObjects();
                $this->_dimensionService->deleteUnusedObjects();
                $this->_audiovisualSourceService->deleteUnusedObjects();
    
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

            $this->_locationService->deleteUnusedObjects();
            $this->_dimensionService->deleteUnusedObjects();
            $this->_audiovisualSourceService->deleteUnusedObjects();
        }

        public function deleteMultipleMonuments($ids) {
            $monuments = $this->_model->whereIn('id', $ids)->get();

            if ($monuments->count() !== count($ids)) {
                throw new NotFoundException('One or more monuments not found.');
            }

            $this->_model->destroy($ids);

            $this->_locationService->deleteUnusedObjects();
            $this->_dimensionService->deleteUnusedObjects();
            $this->_audiovisualSourceService->deleteUnusedObjects();
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
                'monument_language'
            ]));
        
            $monumentData['location_id'] = $locationId;
        
            return $monumentData;
        }
        
        private function createMonument($monumentData)
        {
            $monument = $this->_model->create($monumentData);
            $this->_monumentLanguageService->getOrCreateMonumentLanguage($monumentData['monument_language'], $monument);
            return $monument;
        }        
        
        private function updateMonumentData($monument, $monumentData) {
            $monument->update($monumentData);
            $this->_monumentLanguageService->getOrCreateMonumentLanguage($monumentData['monument_language'], $monument);
        }
        
        private function checkIfMonumentExists($id){
            $monument = $this->_model->find($id);
            if (!$monument) {
                throw new NotFoundException('Monument not found.');
            }
        
            return $monument;
        }

        private function getEnglishMonumentName($monumentLanguages)
        {
            foreach ($monumentLanguages as $language) {
                if ($language['language'] === 'English') {
                    return $language['name'];
                }
            }

            throw new \Exception('Monument name not found for English language.');
        }

        private function checkIfMonumentAlreadyExists($language, $name)
        {
            $monument = $this->_model->whereHas('monumentLanguage', function ($query) use ($language, $name) {
                $query->where('language', $language)->where('name', $name);
            })->exists();
        
            if ($monument) {
                throw new AlreadyExistsException('Monument already exists.');
            }
        }        

        private function updateMonumentDimensions($monument, $dimensions)
        {
            $monument->dimensions_id = $dimensions->id;
            $monument->save();
        }

        private function updateMonumentaudiovisualSource($monument, $audiovisualSource)
        {
            $monument->audiovisual_source_id = $audiovisualSource->id;
            $monument->save();
        }
}