<?php

namespace App\Exceptions;

use Exception;

class AlreadyExistsException extends Exception
{
    protected $_status = 409;

    public function __construct($message = 'Resource already exists.')
    {
        parent::__construct($message);
    }

    public function getStatus(){
        return $this->_status;
    }
}