<?php
namespace App\Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\MessageBag;

use App\Model\Monument;

class Service {
    protected $_model;
    protected $_validationErrors;

    protected $_rules = [];

    public function __construct(Model $model)
    {
        $this->_model = $model;
        $this->_errors = new MessageBag();
    }

    protected function checkValidation($data){
        $validator = $this->validate($data);

        if ($this->hasErrors()) {
            throw new ValidationException($validator);
        }
    }

    private function validate($data){
        $validator = Validator::make($data, $this->_rules);

        if($validator->fails()){
            $this->_validationErrors = $validator->errors();
        }

        return $validator;
    }

    private function hasErrors(){
        return $this->_validationErrors->any();
    }
}