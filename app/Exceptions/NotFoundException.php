<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
        protected $statusCode = 404;


    public function __construct($message = 'The requested resource was not found.')
    {
        parent::__construct($message);
    }
}
