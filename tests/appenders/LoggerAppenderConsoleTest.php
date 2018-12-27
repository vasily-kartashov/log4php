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

use Log4Php\Appenders\LoggerAppenderConsole;
use Log4Php\Layouts\LoggerLayoutPattern;
use Log4Php\Logger;
use PHPUnit\Framework\TestCase;

class LoggerAppenderConsoleTest extends TestCase
{
    private $config = [
        'rootLogger' => [
            'appenders' => ['default'],
        ],
        'appenders' => [
            'default' => [
                'class' => LoggerAppenderConsole::class,
                'layout' => [
                    'class' => LoggerLayoutPattern::class,
                    'params' => [
                        // Intentionally blank so output doesn't clutter phpunit output
                        'conversionPattern' => ''
                    ]
                ],
            ]
        ]
    ];

    public function testRequiresLayout()
    {
        $appender = new LoggerAppenderConsole();
        self::assertTrue($appender->requiresLayout());
    }

    public function testAppendDefault()
    {
        Logger::configure($this->config);
        $log = Logger::getRootLogger();

        /** @var LoggerAppenderConsole $defaultAppender */
        $defaultAppender = $log->getAppender('default');
        $this->assertSame(LoggerAppenderConsole::STDOUT, $defaultAppender->getTarget());

        $log->info("hello");
    }

    public function testAppendStdout()
    {
        $this->config['appenders']['default']['params']['target'] = 'stdout';

        Logger::configure($this->config);
        $log = Logger::getRootLogger();

        /** @var LoggerAppenderConsole $defaultAppender */
        $defaultAppender = $log->getAppender('default');
        $this->assertSame(LoggerAppenderConsole::STDOUT, $defaultAppender->getTarget());

        $log->info("hello");
    }

    public function testAppendStderr()
    {
        $this->config['appenders']['default']['params']['target'] = 'stderr';

        Logger::configure($this->config);
        $log = Logger::getRootLogger();

        /** @var LoggerAppenderConsole $defaultAppender */
        $defaultAppender = $log->getAppender('default');
        $this->assertSame(LoggerAppenderConsole::STDERR, $defaultAppender->getTarget());

        $log->info("hello");
    }
}
