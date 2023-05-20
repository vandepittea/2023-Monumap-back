<?php

namespace App\Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\MessageBag;

class Service {
    protected $_model;
    protected $_errors;
    protected $_rules = [];

    public function __construct(Model $model)
    {
        $this->_model = $model;
        $this->_errors = new MessageBag();
    }

    protected function checkValidation($data){
        $validator = $this->validate($data);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    protected function validate($data){
        $validator = Validator::make($data, $this->_rules);
    
        if($validator->fails()){
            $this->_errors = $validator->errors();
        }
    
        return $validator;
    }

    protected function hasErrors(){
        return $this->_errors->any();
    }

    protected function getErrors(){
        return $this->_errors;
    }
}