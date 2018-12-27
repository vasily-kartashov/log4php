<?php

use Log4Php\Logger;

function simple_function_3()
{
    throw new Exception('Function exception handler');
}

function calling_function_3()
{
    simple_function_3();
}

try {
    calling_function_3();
} catch (Exception $exception) {
    Logger::getLogger(__CLASS__)->info($exception->getMessage(), ['exception' => $exception]);
}

