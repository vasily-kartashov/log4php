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

use Log4Php\Appenders\LoggerAppenderDailyFile;
use Log4Php\Appenders\LoggerAppenderEcho;
use Log4Php\Configurators\LoggerConfigurationAdapterXML;
use Log4Php\Filters\LoggerFilterDenyAll;
use Log4Php\Filters\LoggerFilterLevelRange;
use Log4Php\Layouts\LoggerLayoutPattern;
use Log4Php\Logger;
use PHPUnit\Framework\TestCase;

class LoggerConfigurationAdapterXMLTest extends TestCase
{

    /** Expected output of parsing config1.xml.*/
    private $expected1 = [
        'appenders' => [
            'default' => [
                'class' => LoggerAppenderEcho::class,
                'layout' => [
                    'class' => LoggerLayoutPattern::class,
                ],
                'filters' => [
                    [
                        'class' => LoggerFilterLevelRange::class,
                        'params' => [
                            'levelMin' => 'ERROR',
                            'levelMax' => 'FATAL',
                            'acceptOnMatch' => 'false',
                        ],
                    ],
                    [
                        'class' => LoggerFilterDenyAll::class,
                    ],
                ],
            ],
            'file' => [
                'class' => LoggerAppenderDailyFile::class,
                'layout' => [
                    'class' => LoggerLayoutPattern::class,
                    'params' => [
                        'conversionPattern' => '%d{ISO8601} [%p] %c: %m (at %F line %L)%n',
                    ],
                ],
                'params' => [
                    'datePattern' => 'Ymd',
                    'file' => 'target/examples/daily_%s.log',
                ],
                'threshold' => 'warn'
            ],
        ],
        'loggers' => [
            'foo.bar.baz' => [
                'level' => 'trace',
                'additivity' => 'false',
                'appenders' => ['default'],
            ],
            'foo.bar' => [
                'level' => 'debug',
                'additivity' => 'true',
                'appenders' => ['file'],
            ],
            'foo' => [
                'level' => 'warn',
                'appenders' => ['default', 'file'],
            ],
        ],
        'renderers' => [
            [
                'renderedClass' => 'Fruit',
                'renderingClass' => 'FruitRenderer',
            ],
            [
                'renderedClass' => 'Beer',
                'renderingClass' => 'BeerRenderer',
            ],
        ],
        'threshold' => 'debug',
        'rootLogger' => [
            'level' => 'DEBUG',
            'appenders' => ['default'],
        ],
    ];

    /**
     * @before
     */
    public function _setUp()
    {
        Logger::resetConfiguration();
    }

    /**
     * @after
     */
    public function _tearDown()
    {
        Logger::resetConfiguration();
    }

    public function testConversion()
    {
        $url = PHPUNIT_CONFIG_DIR . '/adapters/xml/config_valid.xml';
        $adapter = new LoggerConfigurationAdapterXML();
        $actual = $adapter->convert($url);
        $this->assertEquals($this->expected1, $actual);
    }

    public function testConversion2()
    {
        $url = PHPUNIT_CONFIG_DIR . '/adapters/xml/config_valid_underscore.xml';
        $adapter = new LoggerConfigurationAdapterXML();
        $actual = $adapter->convert($url);

        $this->assertEquals($this->expected1, $actual);
    }

    /**
     * Test exception is thrown when file cannot be found.
     */
    public function testNonExistantFile()
    {
        $this->expectException(Log4Php\LoggerException::class);
        $this->expectExceptionMessage("File [you/will/never/find/me.conf] does not exist.");
        $adapter = new LoggerConfigurationAdapterXML();
        $adapter->convert('you/will/never/find/me.conf');
    }

    /**
     * Test exception is thrown when file contains invalid XML.
     */
    public function testInvalidXMLFile()
    {
        $this->expectException(Log4Php\LoggerException::class);
        $this->expectExceptionMessage("Error loading configuration file: Premature end of data in tag configuration line");
        $url = PHPUNIT_CONFIG_DIR . '/adapters/xml/config_invalid_syntax.xml';
        $adapter = new LoggerConfigurationAdapterXML();
        $adapter->convert($url);
    }

    /**
     * Test that a warning is triggered when two loggers with the same name
     * are defined.
     */
    public function testDuplicateLoggerWarning()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("log4php: Duplicate logger definition [foo]. Overwriting");
        $url = PHPUNIT_CONFIG_DIR . '/adapters/xml/config_duplicate_logger.xml';
        $adapter = new LoggerConfigurationAdapterXML();
        $adapter->convert($url);
    }


    /**
     * Test that when two loggers with the same name are defined, the second
     * one will overwrite the first.
     */
    public function testDuplicateLoggerConfig()
    {
        $url = PHPUNIT_CONFIG_DIR . '/adapters/xml/config_duplicate_logger.xml';
        $adapter = new LoggerConfigurationAdapterXML();

        // Supress the warning so that test can continue
        $config = @$adapter->convert($url);

        // Second definition of foo has level set to warn (the first to info)
        $this->assertEquals('warn', $config['loggers']['foo']['level']);
    }
}
