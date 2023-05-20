<?php

namespace App\Modules\Core\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class ServiceLanguages extends Service
{
    protected function validate($data)
    {
        foreach ($data as $translation) {
            $validator = Validator::make($translation, $this->_rules);
            if ($validator->fails()) {
                throw new ValidationException($validator);
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