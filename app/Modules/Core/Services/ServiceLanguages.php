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
        $array = json_decode($data["monuments_language"], true); // Decode the JSON string into an associative $arrayName = array('' => , );

        $this->validateData($array); 

          if ($translationData != null) {
            $this->validateDataTranslations($array);
            }
     }

    private function validateData($data, $rules = null)
    {
        if ($rules == null){
            $rules = $this->_rules;
        }
        Log::info("-------------------");
        Log::info("in validateData");
        log::info($data);

        $validator = Validator::make($data, $rules);


        $this->_errors = $validator->errors(); // Initialize the _errors object

        if ($validator->fails()) {   
            Log::info("validator fails");
            $this->_errors->merge($validator->errors());
            return;
        }
    }

    private function validateDataTranslations($data)
    {
        Log::info("in validateDataTranslations");
        log::info($data);
        foreach ($data as $translation) {
            Log::info("in foreach");
            Log::info($translation);

            $this->validateDataTranslation($translation);
        }
    }
    private function validateDataTranslation($data)
    {
        return $this->validateData($data, $this->_rulesTranslations);
    }

    // -- PRESENTERS --> REFACTOR --
    protected function presentAllWithTranslations($data)
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

    protected function presentDetailWithTranslations($data)
    {
        return (count($data["translations"])) ? $data["translations"][0] : null;
    }

    protected function presentListWithTranslations($data)
    {
        foreach ($data["data"] as $index => $record) {
            $data["data"][$index]["translations"] = $this->presentDetailWithTranslations($record);
        }

        return $data;
    }
   
}

