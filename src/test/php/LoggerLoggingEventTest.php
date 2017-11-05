<?php

use Log4Php\Appenders\LoggerAppenderEcho;
use Log4Php\Appenders\LoggerAppenderNull;
use Log4Php\Layouts\LoggerLayoutPattern;
use Log4Php\Logger;
use Log4Php\LoggerLayout;
use Log4Php\LoggerLevel;
use Log4Php\LoggerLoggingEvent;
use Log4Php\LoggerThrowableInformation;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

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
class LoggerLoggingEventTestCaseAppender extends LoggerAppenderNull
{

    protected $requiresLayout = true;

    public function append(LoggerLoggingEvent $event)
    {
        $this->layout->format($event);
    }
}

class LoggerLoggingEventTestCaseLayout extends LoggerLayout
{

    public function activateOptions()
    {
        return true;
    }

    public function format(LoggerLoggingEvent $event): string
    {
        LoggerLoggingEventTest::$locationInfo = $event->getLocationInformation();
        LoggerLoggingEventTest::$throwableInfo = $event->getThrowableInformation();
        return '';
    }
}

/**
 * @group main
 */
class LoggerLoggingEventTest extends TestCase
{
    /** @var \Log4Php\LoggerLocationInfo */
    public static $locationInfo;

    public static $throwableInfo;

    private static $defaultConfiguration = [
        'appenders' => [
            'default' => [
                'class' => LoggerAppenderEcho::class,
                'layout' => [
                    'class' => LoggerLayoutPattern::class,
                    'params' => [
                        'conversionPattern' => '%file | %line | %class | %method | %msg'
                    ]
                ]
            ],
        ],
        'rootLogger' => [
            'appenders' => ['default'],
        ]
    ];

    public function testConstructWithLoggerName()
    {
        $l = LoggerLevel:: getLevelDebug();
        $e = new LoggerLoggingEvent('fqcn', 'TestLogger', $l, 'test');
        self::assertEquals($e->getLoggerName(), 'TestLogger');
    }

    public function testConstructWithTimestamp()
    {
        $l = LoggerLevel:: getLevelDebug();
        $timestamp = microtime(true);
        $e = new LoggerLoggingEvent('fqcn', 'TestLogger', $l, 'test', $timestamp);
        self::assertEquals($e->getTimestamp(), $timestamp);
    }

    public function testGetStartTime()
    {
        $time = LoggerLoggingEvent:: getStartTime();
        self::assertInternalType('float', $time);
        $time2 = LoggerLoggingEvent:: getStartTime();
        self::assertEquals($time, $time2);
    }

    public function testGetLocationInformation()
    {
        $hierarchy = Logger::getHierarchy();
        $root = $hierarchy->getRootLogger();

        $a = new LoggerLoggingEventTestCaseAppender('A1');
        $a->setLayout(new LoggerLoggingEventTestCaseLayout());
        $root->addAppender($a);

        $logger = $hierarchy->getLogger('test');

        $line = __LINE__; $logger->debug('test');
        $hierarchy->shutdown();

        $li = self::$locationInfo;

        self::assertEquals($li->getClassName(), get_class($this));
        self::assertEquals($li->getFileName(), __FILE__);
        self::assertEquals($li->getLineNumber(), $line);
        self::assertEquals($li->getMethodName(), __FUNCTION__);

    }

    public function testGetThrowableInformation1()
    {
        $hierarchy = Logger::getHierarchy();
        $root = $hierarchy->getRootLogger();

        $a = new LoggerLoggingEventTestCaseAppender('A1');
        $a->setLayout(new LoggerLoggingEventTestCaseLayout());
        $root->addAppender($a);

        $logger = $hierarchy->getLogger('test');
        $logger->debug('test');
        $hierarchy->shutdown();

        $ti = self::$throwableInfo;

        self::assertEquals($ti, null);
    }

    public function testGetThrowableInformation2()
    {
        $hierarchy = Logger::getHierarchy();
        $root = $hierarchy->getRootLogger();

        $a = new LoggerLoggingEventTestCaseAppender('A1');
        $a->setLayout(new LoggerLoggingEventTestCaseLayout());
        $root->addAppender($a);

        $ex = new Exception('Message1');
        $logger = $hierarchy->getLogger('test');
        $logger->debug('test', ['exception' => $ex]);
        $hierarchy->shutdown();

        /** @var LoggerThrowableInformation $ti */
        $ti = self::$throwableInfo;

        self::assertTrue($ti instanceof LoggerThrowableInformation);

        $result = $ti->getStringRepresentation();
        self::assertInternalType('array', $result);
    }

    public function testFileLocationInfo()
    {
        Logger::configure(self::$defaultConfiguration);

        $path = realpath(__DIR__ . '/locations/file.php');
        ob_start();
        include_once($path);
        $output = ob_get_contents();
        ob_end_clean();

        Assert::assertEquals($path . ' | 5 | main | main | Message', $output);
    }

    public function testFileLocationInfoWithErrorHandler()
    {
        Logger::configure(self::$defaultConfiguration);

        $path = realpath(__DIR__ . '/locations/file-error-handler.php');
        ob_start();
        include_once($path);
        $output = ob_get_contents();
        ob_end_clean();

        Assert::assertEquals($path . ' | 18 | main | main | Division by zero', $output);
    }

    public function testFileLocationInfoWithExceptionHandler()
    {
        Logger::configure(self::$defaultConfiguration);

        $path = realpath(__DIR__ . '/locations/file-exception-handler.php');
        ob_start();
        include_once($path);
        $output = ob_get_contents();
        ob_end_clean();

        Assert::assertEquals($path . ' | 6 | main | main | File exception handler', $output);
    }

    public function testFunctionLocationInfo()
    {
        Logger::configure(self::$defaultConfiguration);

        $path = realpath(__DIR__ . '/locations/function.php');
        ob_start();
        include_once($path);
        $output = ob_get_contents();
        ob_end_clean();

        Assert::assertEquals($path . ' | 7 | main | simple_function_1 | Message', $output);
    }

    public function testFunctionLocationInfoWithErrorHandler()
    {
        Logger::configure(self::$defaultConfiguration);

        $path = realpath(__DIR__ . '/locations/function-error-handler.php');
        ob_start();
        include_once($path);
        $output = ob_get_contents();
        ob_end_clean();

        Assert::assertEquals($path . ' | 20 | main | simple_function_2 | Division by zero', $output);
    }

    public function testFunctionLocationInfoWithExceptionHandler()
    {
        Logger::configure(self::$defaultConfiguration);

        $path = realpath(__DIR__ . '/locations/function-exception-handler.php');
        ob_start();
        include_once($path);
        $output = ob_get_contents();
        ob_end_clean();

        Assert::assertEquals($path . ' | 7 | main | simple_function_3 | Function exception handler', $output);
    }

    public function testClassLocationInfo()
    {
        Logger::configure(self::$defaultConfiguration);

        $path = realpath(__DIR__ . '/locations/class.php');
        ob_start();
        include_once($path);
        $output = ob_get_contents();
        ob_end_clean();

        Assert::assertEquals($path . ' | 9 | SimpleClass1 | __construct | Message', $output);
    }

    public function testClassLocationInfoWithErrorHandler()
    {
        Logger::configure(self::$defaultConfiguration);

        $path = realpath(__DIR__ . '/locations/class-error-handler.php');
        ob_start();
        include_once($path);
        $output = ob_get_contents();
        ob_end_clean();

        Assert::assertEquals($path . ' | 22 | SimpleClass2 | __construct | Division by zero', $output);
    }

    public function testClassLocationInfoWithExceptionHandler()
    {
        Logger::configure(self::$defaultConfiguration);

        $path = realpath(__DIR__ . '/locations/class-exception-handler.php');
        ob_start();
        include_once($path);
        $output = ob_get_contents();
        ob_end_clean();

        Assert::assertEquals($path . ' | 9 | SimpleClass3 | __construct | Class exception handler', $output);
    }
}
