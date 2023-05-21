<?php

namespace App\Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Service {
    protected $_model;
    protected $_rules = [];

    public function __construct(Model $model)
    {
        $this->_model = $model;
    }

    protected function validate($data){
        $validator = Validator::make($data, $this->_rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function deleteUnusedObjects()
    {
        $this->_model->doesntHave('monuments')->delete();
    }
}