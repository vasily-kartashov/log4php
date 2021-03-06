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

use Log4Php\Appenders\LoggerAppenderPhp;
use Log4Php\Layouts\LoggerLayoutSimple;
use Log4Php\Logger;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

function errorHandler($errno, $errstr, $errfile, $errline)
{
    Assert::assertEquals(LoggerAppenderPhpTest::$expectedError, $errno);
    Assert::assertEquals(LoggerAppenderPhpTest::$expectedMessage, $errstr);
}

class LoggerAppenderPhpTest extends TestCase
{
    public static $expectedMessage;

    public static $expectedError;

    private $config = [
        'rootLogger' => [
            'appenders' => ['default'],
            'level' => 'trace'
        ],
        'appenders' => [
            'default' => [
                'class' => LoggerAppenderPHP::class,
                'layout' => [
                    'class' => LoggerLayoutSimple::class
                ],
            ]
        ]
    ];

    /**
     * @before
     */
    protected function _setUp()
    {
        set_error_handler("errorHandler");
    }

    public function testRequiresLayout()
    {
        $appender = new LoggerAppenderPhp();
        $this->assertTrue($appender->requiresLayout());
    }

    public function testPhp()
    {
        Logger::configure($this->config);
        $logger = Logger::getRootLogger();


        self::$expectedError = E_USER_ERROR;
        self::$expectedMessage = "CRITICAL - This is a test" . PHP_EOL;
        $logger->critical("This is a test");

        self::$expectedError = E_USER_ERROR;
        self::$expectedMessage = "ERROR - This is a test" . PHP_EOL;
        $logger->error("This is a test");

        self::$expectedError = E_USER_WARNING;
        self::$expectedMessage = "WARNING - This is a test" . PHP_EOL;
        $logger->warning("This is a test");

        self::$expectedError = E_USER_NOTICE;
        self::$expectedMessage = "INFO - This is a test" . PHP_EOL;
        $logger->info("This is a test");

        self::$expectedError = E_USER_NOTICE;
        self::$expectedMessage = "DEBUG - This is a test" . PHP_EOL;
        $logger->debug("This is a test");

        self::$expectedError = E_USER_NOTICE;
        self::$expectedMessage = "TRACE - This is a test" . PHP_EOL;
        $logger->trace("This is a test");
    }

    /**
     * @after
     */
    protected function _tearDown()
    {
        restore_error_handler();
    }
}
