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
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version    $Revision$
 * @link       http://logging.apache.org/log4php
 */

use Log4Php\Appenders\LoggerAppenderEcho;
use Log4Php\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    private $testConfig1 = [
        'rootLogger' => [
            'level' => 'ERROR',
            'appenders' => [
                'default',
            ],
        ],
        'appenders' => [
            'default' => [
                'class' => LoggerAppenderEcho::class,
            ],
        ],
        'loggers' => [
            'mylogger' => [
                'additivity' => 'false',
                'level' => 'DEBUG',
                'appenders' => [
                    'default',
                ],
            ],
        ],
    ];

    // For testing additivity
    private $testConfig2 = [
        'appenders' => [
            'default' => [
                'class' => LoggerAppenderEcho::class,
            ],
        ],
        'rootLogger' => [
            'appenders' => ['default'],
        ],
        'loggers' => [
            'foo' => [
                'appenders' => [
                    'default',
                ],
            ],
            'foo.bar' => [
                'appenders' => [
                    'default',
                ],
            ],
            'foo.bar.baz' => [
                'appenders' => [
                    'default',
                ],
            ],
        ],
    ];

    // For testing additivity
    private $testConfig3 = [
        'appenders' => [
            'default' => [
                'class' => LoggerAppenderEcho::class,
            ],
        ],
        'rootLogger' => [
            'appenders' => ['default'],
        ],
        'loggers' => [
            'foo' => [
                'appenders' => [
                    'default',
                ],
            ],
            'foo.bar' => [
                'appenders' => [
                    'default',
                ],
            ],
            'foo.bar.baz' => [
                'level' => 'ERROR',
                'appenders' => [
                    'default',
                ],
            ],
        ],
    ];

    protected function setUp()
    {
        Logger::clear();
        Logger::resetConfiguration();
    }

    protected function tearDown()
    {
        Logger::clear();
        Logger::resetConfiguration();
    }

    public function testLoggerExist()
    {
        $l = Logger::getLogger('test');
        self::assertEquals($l->getName(), 'test');
        self::assertTrue(Logger::exists('test'));
    }

    public function testCanGetRootLogger()
    {
        $l = Logger::getRootLogger();
        self::assertEquals($l->getName(), 'root');
    }

    public function testCanGetASpecificLogger()
    {
        $l = Logger::getLogger('test');
        self::assertEquals($l->getName(), 'test');
    }

    public function testCanLogToAllLevels()
    {
        Logger::configure($this->testConfig1);

        $logger = Logger::getLogger('mylogger');
        ob_start();
        $logger->info('this is an info');
        $logger->warning('this is a warning');
        $logger->error('this is an error');
        $logger->debug('this is a debug message');
        $logger->critical('this is a fatal message');
        $v = ob_get_contents();
        ob_end_clean();

        $e = 'INFO - this is an info' . PHP_EOL;
        $e .= 'WARNING - this is a warning' . PHP_EOL;
        $e .= 'ERROR - this is an error' . PHP_EOL;
        $e .= 'DEBUG - this is a debug message' . PHP_EOL;
        $e .= 'CRITICAL - this is a fatal message' . PHP_EOL;

        self::assertEquals($v, $e);
    }

    public function testIsEnabledFor()
    {
        Logger::configure($this->testConfig1);

        $logger = Logger::getLogger('mylogger');

        self::assertFalse($logger->isTraceEnabled());
        self::assertTrue($logger->isDebugEnabled());
        self::assertTrue($logger->isInfoEnabled());
        self::assertTrue($logger->isWarningEnabled());
        self::assertTrue($logger->isErrorEnabled());
        self::assertTrue($logger->isCriticalEnabled());

        $logger = Logger::getRootLogger();

        self::assertFalse($logger->isTraceEnabled());
        self::assertFalse($logger->isDebugEnabled());
        self::assertFalse($logger->isInfoEnabled());
        self::assertFalse($logger->isWarningEnabled());
        self::assertTrue($logger->isErrorEnabled());
        self::assertTrue($logger->isCriticalEnabled());
    }

    public function testGetCurrentLoggers()
    {
        Logger::clear();
        Logger::resetConfiguration();

        self::assertEquals(0, count(Logger::getCurrentLoggers()));

        Logger::configure($this->testConfig1);
        self::assertEquals(1, count(Logger::getCurrentLoggers()));
        $list = Logger::getCurrentLoggers();
        self::assertEquals('mylogger', $list[0]->getName());
    }

    public function testAdditivity()
    {
        Logger::configure($this->testConfig2);

        $logger = Logger::getLogger('foo.bar.baz');
        ob_start();
        $logger->info('test');
        $actual = ob_get_contents();
        ob_end_clean();

        // The message should get logged 4 times: once by every logger in the
        //  hierarchy (including root)
        $expected = str_repeat('INFO - test' . PHP_EOL, 4);
        self::assertSame($expected, $actual);
    }

    public function testAdditivity2()
    {
        Logger::configure($this->testConfig3);

        $logger = Logger::getLogger('foo.bar.baz');
        ob_start();
        $logger->info('test');
        $actual = ob_get_contents();
        ob_end_clean();

        // The message should get logged 3 times: once by every logger in the
        //  hierarchy, except foo.bar.baz which is set to level ERROR
        $expected = str_repeat('INFO - test' . PHP_EOL, 3);
        self::assertSame($expected, $actual);
    }
}
