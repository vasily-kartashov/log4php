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

namespace Log4Php\Pattern;

use DateTime;
use Log4Php\LoggerException;
use Log4Php\LoggerLoggingEvent;

/**
 * Returns the date/time of the logging request.
 *
 * Option: the datetime format, as used by the date() function. If
 * the option is not given, the default format 'c' will be used.
 *
 * There are several "special" values which can be given for this option:
 * 'ISO8601', 'ABSOLUTE' and 'DATE'.
 */
class LoggerPatternConverterDate extends LoggerPatternConverter
{
    const DATE_FORMAT_ISO8601 = 'c';
    const DATE_FORMAT_ABSOLUTE = 'H:i:s';
    const DATE_FORMAT_DATE = 'd M Y H:i:s.u';

    /** @var string */
    private $format = self::DATE_FORMAT_ISO8601;

    /**
     * @var array
     * @psalm-var array<string,string>
     */
    private $specials = [
        'ISO8601'  => self::DATE_FORMAT_ISO8601,
        'ABSOLUTE' => self::DATE_FORMAT_ABSOLUTE,
        'DATE'     => self::DATE_FORMAT_DATE,
    ];

    /**
     * @return void
     */
    public function activateOptions()
    {
        // Parse the option (date format)
        if (!empty($this->option)) {
            if (isset($this->specials[$this->option])) {
                $this->format = $this->specials[$this->option];
            } else {
                $this->format = $this->option;
            }
        }
    }

    /**
     * @param LoggerLoggingEvent $event
     * @return string
     * @throws LoggerException
     */
    public function convert(LoggerLoggingEvent $event)
    {
        $time = DateTime::createFromFormat('U.u', number_format($event->getTimestamp(), 6, '.', ''));
        if ($time === false) {
            throw new LoggerException('Invalid date format');
        }
        return $time->format($this->format);
    }
}
