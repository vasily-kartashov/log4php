<?php

use Log4Php\GenericHandler;
use Log4Php\Logger;

class ErrorHandler1 implements GenericHandler
{
    public function __invoke($code, $message, $file, $line)
    {
        Logger::getLogger(__CLASS__)->info($message);
        restore_error_handler();
        return true;
    }
}

set_error_handler(new ErrorHandler1());

$x = 2 / 0;
