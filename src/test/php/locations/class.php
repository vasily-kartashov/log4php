<?php

use Log4Php\Logger;

class SimpleClass1
{
    public function __construct()
    {
        Logger::getLogger(__FILE__)->info('Message');
    }
}

function constructor_call_1()
{
    new SimpleClass1();
}

constructor_call_1();
