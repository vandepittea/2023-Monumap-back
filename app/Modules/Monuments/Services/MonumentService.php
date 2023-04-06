<?php
namespace App\Modules\Movies\Services;


class MonumentService extends Services
{
    protected $_rules = [
        "name" => "required|string|max:50",
        "description" => "required|text",
        "location" => "required|json",
        "historicalSignificance" => "nullable|text";
        "type" => "required|enum";
        "yearOfContstruction" => "required|year";
        "monumentDesigner" => "required|string";
        "accessibility" => "required|json";
        "usedMaterials" => "nullable|json";
        "dimensions" => "nullable|json";
        "weight" => "nullable|integer";
        "costToConstruct" => "nullable|integer";
        "images" => "required|json";
        "audiovisualSources" => "nullable|json" ];

        public function __construct(Monument $model) {
            parent::__construct($model);
        }

        public function getAllMonuments($pages = 10){
            return $this->_model->paginate($pages)->withQueryString();
        }
}