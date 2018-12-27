<?php

use Log4Php\Logger;

try {
    throw new Exception('File exception handler');
} catch (Exception $exception) {
    Logger::getLogger(__CLASS__)->info($exception->getMessage(), ['exception' => $exception]);
}

