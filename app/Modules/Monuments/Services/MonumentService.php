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
        "audiovisualSources" => "nullable|json"
    ];

        public function __construct(Monument $model) {
            Parent::__construct($model);
        }

        public function getAllMonuments($pages = 10, $parameterName = null, $parameterValue = null) {
            $monuments = $this->_model->paginate($pages)->withQueryString();
        
            if (!is_null($parameterName) && !is_null($parameterValue)) {
                $monuments = $monuments->where($parameterName, $parameterValue);
            }
        
            return $monuments;
        }

        public function addMonument($data){
            $this->validate($data);
            
            if ($this->hasErrors()){
                return;
            }

            return $this->_model->create($data);
        }

        public function getOneMonument($id){
            return ["data" => $this->_model->find($id)];
        }

        public function updateMonument($id, $data){
            $this->validate($data);
            if($this->hasErrors()){
                return;
            }

            $monument = $this->_model->find($id);

            if ($monument) {
                $monument->update($data); 
            }
            return $monument;
        }

        public function deleteMonument($id) {
            $monument = $this->_model->find($id);

            if($monument){
                $monument->delete();
            }
        }
        
}