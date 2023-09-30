<?php

namespace App\Exception;

use Exception;


class DBException extends Exception
{

}

// NB: I can choose to use this in my CustomErrorHandler.php and also use it for my 
// DB error exception message for \Exception.
