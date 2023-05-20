<?php

namespace App\Modules\Core\Services;

use Illuminate\Support\Facades\Validator;

abstract class ServiceLanguages extends Service
{
    protected $_rulesTranslations = [];


    protected function validate($data)
    {
        parent::validate($data);
        $this->validateDataTranslations($data);
    }

    private function validateDataTranslations($data)
    {
        foreach ($data as $translation) {
            $validator = Validator::make($translation, $this->_rulesTranslations);
            if ($validator->fails()) {
                $this->_errors->merge($validator->errors());
            }
        }
    }

    protected function modelWithTranslations($model, $translationsField, $language = null)
    {
        $modelData = $model->toArray();

        $translations = $model->$translationsField->groupBy('language')->toArray();

        if ($language) {
            $filteredTranslations = $translations[$language] ?? [];
            $modelData[$translationsField] = $filteredTranslations;
        } else {
            $modelData[$translationsField] = $translations;
        }

        return $modelData;
    }   
}