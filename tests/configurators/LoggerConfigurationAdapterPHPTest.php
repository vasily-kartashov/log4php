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
 *
 * @category   tests
 * @package       log4php
 * @subpackage configurators
 * @license       http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version    $Revision$
 * @link       http://logging.apache.org/log4php
 */

use Log4Php\Appenders\LoggerAppenderEcho;
use Log4Php\Configurators\LoggerConfigurationAdapterPHP;
use Log4Php\Layouts\LoggerLayoutSimple;
use PHPUnit\Framework\TestCase;

class LoggerConfigurationAdapterPHPTest extends TestCase
{
    private $expected1 = [
        'rootLogger' => [
            'level' => 'info',
            'appenders' => ['default']
        ],
        'appenders' => [
            'default' => [
                'class' => LoggerAppenderEcho::class,
                'layout' => [
                    'class' => LoggerLayoutSimple::class
                ]
            ]
        ]
    ];

    public function testConfig()
    {
        $url = PHPUNIT_CONFIG_DIR . '/adapters/php/config_valid.php';
        $adapter = new LoggerConfigurationAdapterPHP();
        $actual = $adapter->convert($url);

        $this->assertSame($this->expected1, $actual);
    }

    /**
     * Test exception is thrown when file cannot be found.
     * @expectedException Log4Php\LoggerException
     * @expectedExceptionMessage File [you/will/never/find/me.conf] does not exist.
     */
    public function testNonExistantFileWarning()
    {
        $adapter = new LoggerConfigurationAdapterPHP();
        $adapter->convert('you/will/never/find/me.conf');
    }

    /**
     * Test exception is thrown when file is not valid.
     * @expectedException ParseError
     * @expectedExceptionMessage syntax error, unexpected end of file, expecting ')'
     */
    public function testInvalidFileWarning()
    {
        $url = PHPUNIT_CONFIG_DIR . '/adapters/php/config_invalid_syntax.php';
        $adapter = new LoggerConfigurationAdapterPHP();
        $adapter->convert($url);
    }

    /**
     * Test exception is thrown when the configuration is empty.
     * @expectedException Throwable
     */
    public function testEmptyConfigWarning()
    {
        $url = PHPUNIT_CONFIG_DIR . '/adapters/php/config_empty.php';
        $adapter = new LoggerConfigurationAdapterPHP();
        $adapter->convert($url);
    }

    /**
     * Test exception is thrown when the configuration does not contain an array.
     * @expectedException Log4Php\LoggerException
     * @expectedExceptionMessage Invalid configuration: not an array.
     */
    public function testInvalidConfigWarning()
    {
        $url = PHPUNIT_CONFIG_DIR . '/adapters/php/config_not_an_array.php';
        $adapter = new LoggerConfigurationAdapterPHP();
        $adapter->convert($url);
    }
}
