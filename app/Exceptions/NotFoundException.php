<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
<<<<<<< HEAD
        protected $statusCode = 404;

=======
    protected $_status;
>>>>>>> 8f5a4acb8ce590580cd3dcff5b303d1cb4b9d4fe

    public function __construct($message = 'The requested resource was not found.')
    {
        parent::__construct($message);
<<<<<<< HEAD
=======
        $this->_status = 404;
>>>>>>> 8f5a4acb8ce590580cd3dcff5b303d1cb4b9d4fe
    }
}
