<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements. See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Log4Php\Appenders;

use Log4Php\LoggerAppender;
use Log4Php\LoggerLevel;
use Log4Php\LoggerLoggingEvent;

/**
 * LoggerAppenderPhp logs events by creating a PHP user-level message using
 * the PHP's trigger_error()function.
 *
 * This appender has no configurable parameters.
 *
 * Levels are mapped as follows:
 *
 * - <b>level < WARN</b> mapped to E_USER_NOTICE
 * - <b>WARN <= level < ERROR</b> mapped to E_USER_WARNING
 * - <b>level >= ERROR</b> mapped to E_USER_ERROR
 */
class LoggerAppenderPhp extends LoggerAppender
{
    /**
     * @param LoggerLoggingEvent $event
     * @return void
     */
    public function append(LoggerLoggingEvent $event)
    {
        $message = $this->layout->format($event);
        if ($message !== null) {
            $level = $event->getLevel();
            if ($level->isGreaterOrEqual(LoggerLevel::getLevelError())) {
                trigger_error($message, E_USER_ERROR);
            } elseif ($level->isGreaterOrEqual(LoggerLevel::getLevelWarning())) {
                trigger_error($message, E_USER_WARNING);
            } else {
                trigger_error($message, E_USER_NOTICE);
            }
        }
    }
}
