<?php
namespace App\Modules\Movies\Services;

use App\Models\Monument;
use App\Modules\Core\Services\Service;
use App\Modules\Core\Services\ServiceLanguages;



class MonumentService extends Service
{
    protected $_rules = [
        "name" => "required|string|max:50",
        "description" => "required|text",
        "location" => "required|json",
        "historicalSignificance" => "nullable|text",
        "type" => "required|enum",
        "yearOfContstruction" => "required|year",
        "monumentDesigner" => "required|string",
        "accessibility" => "required|json",
        "usedMaterials" => "nullable|json",
        "dimensions" => "nullable|json",
        "weight" => "nullable|integer",
        "costToConstruct" => "nullable|integer",
        "images" => "required|json",
        "audiovisualSources" => "nullable|json" ];

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
        
}