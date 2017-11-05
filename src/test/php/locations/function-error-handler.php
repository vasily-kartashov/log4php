<?php

use Log4Php\GenericHandler;
use Log4Php\Logger;

class ErrorHandler2 implements GenericHandler
{
    public function __invoke($code, $message, $file, $line)
    {
        Logger::getLogger(__CLASS__)->info($message);
        restore_error_handler();
        return true;
    }
}

set_error_handler(new ErrorHandler2());

function simple_function_2()
{
    $x = 2 / 0;
}

function calling_function_2()
{
    simple_function_2();
}

calling_function_2();
