<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    protected $_status;

    public function __construct($message = 'The requested resource was not found.')
    {
        parent::__construct($message);
        $this->_status = 404;
    }

    public function getStatus()
    {
        return $this->_status;
    }
}
