<?php

namespace App\Exceptions;

use Exception;

class AlreadyExistsException extends Exception
{
<<<<<<< HEAD
    protected $statusCode = 409;
=======
    protected $_status = 409;
>>>>>>> 8f5a4acb8ce590580cd3dcff5b303d1cb4b9d4fe

    public function __construct($message = 'Resource already exists.')
    {
        parent::__construct($message);
<<<<<<< HEAD
=======
    }

    public function getStatus(){
        return $this->_status;
>>>>>>> 8f5a4acb8ce590580cd3dcff5b303d1cb4b9d4fe
    }
}