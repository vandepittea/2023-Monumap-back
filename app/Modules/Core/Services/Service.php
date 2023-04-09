<?php
namespace App\Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

use App\Model\Monument;

class Service {
    protected $_model;
    protected $_errors;

    protected $_rules = [];

    public function __construct(Model $model)
    {
        $this->_model = $model;
        $this->_errors = new MessageBag();
    }

    protected function validate($data){
        $validator = Validator::make($data, $this->_rules);
        if($validator->fails()){
            $this->_errors = $validator->errors();
        }

        return $validator;
    }

    public function hasErrors(){
        return $this->_errors->any();
    }

    public function getErrors(){
        return $this->_errors;
    }

    

}