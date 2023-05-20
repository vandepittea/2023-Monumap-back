<?php

namespace App\Modules\Core\Services;

use Exception;
use Hamcrest\Core\HasToString;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Log; //TODO: remove

abstract class ServiceLanguages extends Service
{
    protected $_rulesTranslations = [];

    // -- VALIDATOR --> REFACTOR --
    protected function validate($data)
    {   
        $translationData = isset($data["monuments_language"]) ? $data["monuments_language"] : null;  

        $this->validateData($data); 

          if ($translationData != null) {
            $this->validateDataTranslations($data);
            }
     }

    private function validateData($data, $rules = null)
    {
        if ($rules == null){
            $rules = $this->_rules;
        }

        $validator = Validator::make($data, $rules);


        $this->_errors = $validator->errors(); // Initialize the _errors object

        if ($validator->fails()) {   
            $this->_errors->merge($validator->errors());
            return;
        }
    }

    private function validateDataTranslations($data)
    {
        foreach ($data as $translation) {
            $this->validateData($data, $this->_rulesTranslations);
        }
    }

    // -- PRESENTERS --> REFACTOR --
    protected function presentAllWithTranslations($data) //TODO: toont nu jusite vertaling? 
    {
      /* foreach ($data["data"] as $record) { //TODO: terugzetten
       $data["data"] = $this->presentFindWithTranslations($record);
        }*/
        return $data;
    }

    protected function presentFindWithTranslations($data)
    {
        if (!isset($data["translations"]))
            return $data;

        $translations = [];
        foreach ($data["translations"] as  $translation) {
            $translations[$translation["language"]] = $translation;
        }
        $data["translations"] = $translations;

        return $data;
    }
}

