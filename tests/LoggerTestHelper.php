<?php

/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category   tests
 * @package    log4php
 * @subpackage appenders
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version    $Revision$
 * @link       http://logging.apache.org/log4php
 */
use Log4Php\Appenders\LoggerAppenderEcho;
use Log4Php\Layouts\LoggerLayoutJson;
use Log4Php\Layouts\LoggerLayoutPattern;
use Log4Php\Layouts\LoggerLayoutSimple;
use Log4Php\Logger;
use Log4Php\LoggerFilter;
use Log4Php\LoggerLevel;
use Log4Php\LoggerLoggingEvent;

/** A set of helper functions for running tests. */
class LoggerTestHelper
{

    /**
     * Returns a test logging event with level set to TRACE.
     * @return LoggerLoggingEvent
     */
    public static function getTraceEvent($message = 'test', $logger = "test")
    {
        return new LoggerLoggingEvent(__CLASS__, new Logger($logger), LoggerLevel::getLevelTrace(), $message);
    }

    /**
     * Returns a test logging event with level set to DEBUG.
     * @return LoggerLoggingEvent
     */
    public static function getDebugEvent($message = 'test', $logger = "test")
    {
        return new LoggerLoggingEvent(__CLASS__, new Logger($logger), LoggerLevel::getLevelDebug(), $message);
    }

    /**
     * Returns a test logging event with level set to INFO.
     * @return LoggerLoggingEvent
     */
    public static function getInfoEvent($message = 'test', $logger = "test")
    {
        return new LoggerLoggingEvent(__CLASS__, new Logger($logger), LoggerLevel::getLevelInfo(), $message);
    }

    /**
     * Returns a test logging event with level set to WARN.
     * @return LoggerLoggingEvent
     */
    public static function getWarnEvent($message = 'test', $logger = "test")
    {
        return new LoggerLoggingEvent(__CLASS__, new Logger($logger), LoggerLevel::getLevelWarning(), $message);
    }

    /**
     * Returns a test logging event with level set to ERROR.
     * @param string $message
     * @param string $logger
     * @return LoggerLoggingEvent
     */
    public static function getErrorEvent($message = 'test', $logger = "test")
    {
        return new LoggerLoggingEvent(__CLASS__, new Logger($logger), LoggerLevel::getLevelError(), $message);
    }

    /**
     * Returns a test logging event with level set to FATAL.
     * @return LoggerLoggingEvent
     */
    public static function getFatalEvent($message = 'test', $logger = "test")
    {
        return new LoggerLoggingEvent(__CLASS__, new Logger($logger), LoggerLevel::getLevelCritical(), $message);
    }

    /**
     * Returns an array of logging events, one for each level, sorted ascending
     * by severitiy.
     */
    public static function getAllEvents($message = 'test')
    {
        return array(
            self::getTraceEvent($message),
            self::getDebugEvent($message),
            self::getInfoEvent($message),
            self::getWarnEvent($message),
            self::getErrorEvent($message),
            self::getFatalEvent($message),
        );
    }

    /** Returns an array of all existing levels, sorted ascending by severity. */
    public static function getAllLevels()
    {
        return array(
            LoggerLevel::getLevelTrace(),
            LoggerLevel::getLevelDebug(),
            LoggerLevel::getLevelInfo(),
            LoggerLevel::getLevelWarning(),
            LoggerLevel::getLevelError(),
            LoggerLevel::getLevelCritical(),
        );
    }

    /** Returns a string representation of a filter decision. */
    public static function decisionToString($decision)
    {
        switch ($decision) {
            case LoggerFilter::ACCEPT:
                return 'ACCEPT';
            case LoggerFilter::NEUTRAL:
                return 'NEUTRAL';
            case LoggerFilter::DENY:
                return 'DENY';
        }
    }

    /** Returns a simple configuration with one echo appender tied to root logger. */
    public static function getEchoConfig()
    {
        return [
            'threshold' => 'ALL',
            'rootLogger' => [
                'level' => 'trace',
                'appenders' => ['default'],
            ],
            'appenders' => [
                'default' => [
                    'class' => LoggerAppenderEcho::class,
                    'layout' => [
                        'class' => LoggerLayoutSimple::class,
                    ],
                ],
            ],
        ];
    }

    /** Returns a simple configuration with one echo appender using the pattern layout. */
    public static function getEchoPatternConfig($pattern)
    {
        return [
            'threshold' => 'ALL',
            'rootLogger' => [
                'level' => 'trace',
                'appenders' => ['default'],
            ],
            'appenders' => [
                'default' => [
                    'class' => LoggerAppenderEcho::class,
                    'layout' => [
                        'class' => LoggerLayoutPattern::class,
                        'params' => [
                            'conversionPattern' => $pattern
                        ]
                    ],
                ],
            ],
        ];
    }

    public static function getEchoJsonConfig()
    {
        return [
            'threshold' => 'ALL',
            'rootLogger' => [
                'level' => 'trace',
                'appenders' => ['default'],
            ],
            'appenders' => [
                'default' => [
                    'class' => LoggerAppenderEcho::class,
                    'layout' => [
                        'class' => LoggerLayoutJson::class,
                    ],
                ],
            ],
        ];
    }
}
