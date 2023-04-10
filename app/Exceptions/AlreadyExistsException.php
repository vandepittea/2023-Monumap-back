<?php

namespace App\Exceptions;

use Exception;

class AlreadyExistsException extends Exception
{
    public function __construct($message = 'Resource already exists.')
    {
        parent::__construct($message);
        protected $statusCode = 409;
    }
}