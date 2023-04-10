<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    public function __construct($message = 'The requested resource was not found.')
    {
        parent::__construct($message);
        protected $statusCode = 404;
    }
}
