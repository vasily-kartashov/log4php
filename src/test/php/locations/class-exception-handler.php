<?php

use Log4Php\Logger;

class SimpleClass3
{
    public function __construct()
    {
        throw new Exception('Class exception handler');
    }
}

function constructor_call_3()
{
    new SimpleClass3();
}

try {
    constructor_call_3();
} catch (Exception $exception) {
    Logger::getLogger(__CLASS__)->info($exception->getMessage(), ['exception' => $exception]);
}
