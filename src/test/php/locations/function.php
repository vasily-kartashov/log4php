<?php

use Log4Php\Logger;

function simple_function_1()
{
    Logger::getLogger(__FILE__)->info('Message');
}

function calling_function_1()
{
    simple_function_1();
}

calling_function_1();
