<?php

use Log4Php\GenericHandler;
use Log4Php\Logger;

class ErrorHandler3 implements GenericHandler
{
    public function __invoke($code, $message, $file, $line)
    {
        Logger::getLogger(__CLASS__)->info($message);
        restore_error_handler();
        return true;
    }
}

set_error_handler(new ErrorHandler3());

class SimpleClass2
{
    public function __construct()
    {
        $x = 2 / 0;
    }
}

function constructor_call_2()
{
    new SimpleClass2();
}

constructor_call_2();
