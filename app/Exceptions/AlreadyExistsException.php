<?php

namespace App\Exceptions;

use Exception;

class AlreadyExistsException extends Exception
{
    protected $statusCode = 409;

    public function __construct($message = 'Resource already exists.')
    {
        parent::__construct($message);
    }
}