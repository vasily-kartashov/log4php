<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Log4Php\Layouts;

use Log4Php\Helpers\LoggerPatternParser;
use Log4Php\LoggerException;
use Log4Php\LoggerLayout;
use Log4Php\LoggerLoggingEvent;
use Log4Php\Pattern\LoggerPatternConverter;
use Log4Php\Pattern\LoggerPatternConverterClass;
use Log4Php\Pattern\LoggerPatternConverterCookie;
use Log4Php\Pattern\LoggerPatternConverterDate;
use Log4Php\Pattern\LoggerPatternConverterEnvironment;
use Log4Php\Pattern\LoggerPatternConverterFile;
use Log4Php\Pattern\LoggerPatternConverterLevel;
use Log4Php\Pattern\LoggerPatternConverterLine;
use Log4Php\Pattern\LoggerPatternConverterLocation;
use Log4Php\Pattern\LoggerPatternConverterLogger;
use Log4Php\Pattern\LoggerPatternConverterMDC;
use Log4Php\Pattern\LoggerPatternConverterMessage;
use Log4Php\Pattern\LoggerPatternConverterMethod;
use Log4Php\Pattern\LoggerPatternConverterNDC;
use Log4Php\Pattern\LoggerPatternConverterNewLine;
use Log4Php\Pattern\LoggerPatternConverterProcess;
use Log4Php\Pattern\LoggerPatternConverterRelative;
use Log4Php\Pattern\LoggerPatternConverterRequest;
use Log4Php\Pattern\LoggerPatternConverterServer;
use Log4Php\Pattern\LoggerPatternConverterSession;
use Log4Php\Pattern\LoggerPatternConverterSessionID;
use Log4Php\Pattern\LoggerPatternConverterThrowable;

/**
 * A flexible layout configurable with a pattern string.
 *
 * Configurable parameters:
 *
 * * conversionPattern - A string which controls the formatting of logging
 *   events. See docs for full specification.
 */
class LoggerLayoutPattern extends LoggerLayout
{

    /** Default conversion pattern */
    const DEFAULT_CONVERSION_PATTERN = '%date %-5level %logger %message%newline';

    /** Default conversion TTCC Pattern */
    const TTCC_CONVERSION_PATTERN = '%d [%t] %p %c %x - %m%n';

    /**
     * The conversion pattern.
     * @var string
     */
    protected $pattern = self::DEFAULT_CONVERSION_PATTERN;

    /**
     * Maps conversion keywords to the relevant converter (default implementation).
     * @var array<string,string>
     */
    protected static $defaultConverterMap = [
        'c'         => LoggerPatternConverterLogger::class,
        'lo'        => LoggerPatternConverterLogger::class,
        'logger'    => LoggerPatternConverterLogger::class,
        'C'         => LoggerPatternConverterClass::class,
        'class'     => LoggerPatternConverterClass::class,
        'cookie'    => LoggerPatternConverterCookie::class,
        'd'         => LoggerPatternConverterDate::class,
        'date'      => LoggerPatternConverterDate::class,
        'e'         => LoggerPatternConverterEnvironment::class,
        'env'       => LoggerPatternConverterEnvironment::class,
        'ex'        => LoggerPatternConverterThrowable::class,
        'exception' => LoggerPatternConverterThrowable::class,
        'throwable' => LoggerPatternConverterThrowable::class,
        'F'         => LoggerPatternConverterFile::class,
        'file'      => LoggerPatternConverterFile::class,
        'l'         => LoggerPatternConverterLocation::class,
        'location'  => LoggerPatternConverterLocation::class,
        'L'         => LoggerPatternConverterLine::class,
        'line'      => LoggerPatternConverterLine::class,
        'm'         => LoggerPatternConverterMessage::class,
        'msg'       => LoggerPatternConverterMessage::class,
        'message'   => LoggerPatternConverterMessage::class,
        'M'         => LoggerPatternConverterMethod::class,
        'method'    => LoggerPatternConverterMethod::class,
        'n'         => LoggerPatternConverterNewLine::class,
        'newline'   => LoggerPatternConverterNewLine::class,
        'p'         => LoggerPatternConverterLevel::class,
        'le'        => LoggerPatternConverterLevel::class,
        'level'     => LoggerPatternConverterLevel::class,
        'r'         => LoggerPatternConverterRelative::class,
        'relative'  => LoggerPatternConverterRelative::class,
        'req'       => LoggerPatternConverterRequest::class,
        'request'   => LoggerPatternConverterRequest::class,
        's'         => LoggerPatternConverterServer::class,
        'server'    => LoggerPatternConverterServer::class,
        'ses'       => LoggerPatternConverterSession::class,
        'session'   => LoggerPatternConverterSession::class,
        'sid'       => LoggerPatternConverterSessionID::class,
        'sessionid' => LoggerPatternConverterSessionID::class,
        't'         => LoggerPatternConverterProcess::class,
        'pid'       => LoggerPatternConverterProcess::class,
        'process'   => LoggerPatternConverterProcess::class,
        'x'         => LoggerPatternConverterNDC::class,
        'ndc'       => LoggerPatternConverterNDC::class,
        'X'         => LoggerPatternConverterMDC::class,
        'mdc'       => LoggerPatternConverterMDC::class,
    ];

    /**
     * Maps conversion keywords to the relevant converter.
     * @var array<string,string>
     */
    protected $converterMap = [];

    /**
     * Head of a chain of Converters.
     * @var LoggerPatternConverter|null
     */
    private $head;

    /**
     * Returns the default converter map.
     * @return array<string,string>
     */
    public static function getDefaultConverterMap()
    {
        return self::$defaultConverterMap;
    }

    /** Constructor. Initializes the converter map. */
    public function __construct()
    {
        $this->converterMap = self::$defaultConverterMap;
    }

    /**
     * Sets the conversionPattern option. This is the string which
     * controls formatting and consists of a mix of literal content and
     * conversion specifiers.
     * @param string $conversionPattern
     * @return void
     */
    public function setConversionPattern($conversionPattern)
    {
        $this->pattern = $conversionPattern;
    }

    /**
     * Processes the conversion pattern and creates a corresponding chain of
     * pattern converters which will be used to format logging events.
     * @return bool
     * @throws LoggerException
     */
    public function activateOptions()
    {
        if (!isset($this->pattern)) {
            throw new LoggerException("Mandatory parameter 'conversionPattern' is not set.");
        }

        $parser = new LoggerPatternParser($this->pattern, $this->converterMap);
        $this->head = $parser->parse();

        return true;
    }

    /**
     * Produces a formatted string as specified by the conversion pattern.
     *
     * @param LoggerLoggingEvent $event
     * @return string
     */
    public function format(LoggerLoggingEvent $event): string
    {
        $sbuf = '';
        $converter = $this->head;
        while ($converter !== null) {
            $converter->format($sbuf, $event);
            $converter = $converter->next;
        }
        return $sbuf;
    }
}
