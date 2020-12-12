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
use Log4Php\Configurators\LoggerConfiguratorDefault;
use Log4Php\Filters\LoggerFilterStringMatch;
use Log4Php\Layouts\LoggerLayoutPattern;
use Log4Php\Layouts\LoggerLayoutSimple;
use Log4Php\Logger;
use Log4Php\LoggerHierarchy;
use Log4Php\LoggerLevel;
use Log4Php\Renderers\LoggerRenderer;
use Log4Php\Renderers\LoggerRendererDefault;
use PHPUnit\Framework\TestCase;

class CostumDefaultRenderer implements LoggerRenderer
{
    public function render($o): string
    {
    }
}

class LoggerConfiguratorTest extends TestCase
{
    /**
     * @before
     * Reset configuration after each test.
     */
    public function _setUp()
    {
        Logger::resetConfiguration();
    }

    /**
     * @after
     * Reset configuration after each test.
     */
    public function _tearDown()
    {
        Logger::resetConfiguration();
    }

    /** Check default setup. */
    public function testDefaultConfig()
    {
        Logger::configure();

        $actual = Logger::getCurrentLoggers();
        $expected = array();
        $this->assertSame($expected, $actual);

        $appenders = Logger::getRootLogger()->getAllAppenders();
        $this->assertTrue(is_array($appenders));
        $this->assertEquals(count($appenders), 1);

        $names = array_keys($appenders);
        $this->assertSame('default', $names[0]);

        $appender = array_shift($appenders);
        $this->assertInstanceOf(LoggerAppenderEcho::class, $appender);
        $this->assertSame('default', $appender->getName());

        $layout = $appender->getLayout();
        $this->assertInstanceOf(LoggerLayoutSimple::class, $layout);

        $root = Logger::getRootLogger();
        $appenders = $root->getAllAppenders();
        $this->assertTrue(is_array($appenders));
        $this->assertEquals(count($appenders), 1);

        $actual = $root->getLevel();
        $expected = LoggerLevel::getLevelDebug();
        $this->assertEquals($expected, $actual);
    }

    public function testInputIsInteger()
    {
        $this->expectExceptionMessage("Invalid configuration param given. Reverting to default configuration.");
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        Logger::configure(12345);
    }

    public function testYAMLFile()
    {
        $this->expectExceptionMessage("log4php: Configuration failed. Unsupported configuration file extension: yml");
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        Logger::configure(PHPUNIT_CONFIG_DIR . '/config.yml');
    }

    public function testAppenderConfigNotArray()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Invalid configuration provided for appender");
        /** @var PHPUnit_Framework_MockObject_MockObject|LoggerHierarchy $hierachyMock */
        $hierachyMock = $this->createMock(LoggerHierarchy::class);

        $config = [
            'appenders' => [
                'default',
            ],
        ];

        $configurator = new LoggerConfiguratorDefault();
        $configurator->configure($hierachyMock, $config);
    }

    public function testNoAppenderClassSet()
    {
        $this->expectExceptionMessage("No class given for appender");
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        Logger::configure(PHPUNIT_CONFIG_DIR . '/appenders/config_no_class.xml');
    }

    public function testNotExistingAppenderClassSet()
    {
        $this->expectExceptionMessage("Invalid class [unknownClass] given for appender [foo]. Class does not exist. Skipping appender definition.");
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        Logger::configure(PHPUNIT_CONFIG_DIR . '/appenders/config_not_existing_class.xml');
    }

    public function testInvalidAppenderClassSet()
    {
        $this->expectExceptionMessage("Invalid class [stdClass] given for appender [foo]. Not a valid LoggerAppender class. Skipping appender definition.");
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        Logger::configure(PHPUNIT_CONFIG_DIR . '/appenders/config_invalid_appender_class.xml');
    }

    public function testNotExistingAppenderFilterClassSet()
    {
        $this->expectExceptionMessage("Unknown filter class [Foo] specified on appender [foo]. Skipping filter definition.");
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        Logger::configure(PHPUNIT_CONFIG_DIR . '/appenders/config_not_existing_filter_class.xml');
    }

    public function testInvalidAppenderFilterParameter()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Unknown option [fooParameter] specified on [Log4Php\Filters\LoggerFilterStringMatch]. Skipping.");
        Logger::configure(PHPUNIT_CONFIG_DIR . '/appenders/config_invalid_filter_parameters.xml');
    }

    public function testInvalidAppenderFilterClassSet()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Invalid filter class [stdClass] specified on appender [foo]. Skipping filter definition.");
        Logger::configure(PHPUNIT_CONFIG_DIR . '/appenders/config_invalid_filter_class.xml');
    }

    public function testNotExistingAppenderLayoutClassSet()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Unknown layout class [Foo] specified for appender [foo]. Reverting to default layout.");
        Logger::configure(PHPUNIT_CONFIG_DIR . '/appenders/config_not_existing_layout_class.xml');
    }

    public function testInvalidAppenderLayoutClassSet()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Invalid layout class [stdClass] specified for appender [foo]. Reverting to default layout.");
        Logger::configure(PHPUNIT_CONFIG_DIR . '/appenders/config_invalid_layout_class.xml');
    }

    public function testNoAppenderLayoutClassSet()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Layout class not specified for appender [foo]. Reverting to default layout.");
        Logger::configure(PHPUNIT_CONFIG_DIR . '/appenders/config_no_layout_class.xml');
    }

    public function testInvalidRenderingClassSet()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Failed adding renderer. Rendering class [stdClass] does not implement the LoggerRenderer interface.");
        Logger::configure(PHPUNIT_CONFIG_DIR . '/renderers/config_invalid_rendering_class.xml');
    }

    public function testNoRenderingClassSet()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Rendering class not specified. Skipping renderer definition.");
        Logger::configure(PHPUNIT_CONFIG_DIR . '/renderers/config_no_rendering_class.xml');
    }

    public function testNoRenderedClassSet()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Rendered class not specified. Skipping renderer definition.");
        Logger::configure(PHPUNIT_CONFIG_DIR . '/renderers/config_no_rendered_class.xml');
    }

    public function testNotExistingRenderingClassSet()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Failed adding renderer. Rendering class [DoesNotExistRenderer] not found.");
        Logger::configure(PHPUNIT_CONFIG_DIR . '/renderers/config_not_existing_rendering_class.xml');
    }

    public function testInvalidLoggerAddivity()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Invalid additivity value [4711] specified for logger [myLogger].");
        Logger::configure(PHPUNIT_CONFIG_DIR . '/loggers/config_invalid_additivity.xml');
    }

    public function testNotExistingLoggerAppendersClass()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Unknown appender [unknownAppender] linked to logger [myLogger].");
        Logger::configure(PHPUNIT_CONFIG_DIR . '/loggers/config_not_existing_appenders.xml');
    }

    /**
     * Test that an error is reported when config file is not found.
     *
     *
     */
    public function testNonexistantFile()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("log4php: Configuration failed. File not found");
        Logger::configure('hopefully/this/path/doesnt/exist/config.xml');

    }

    /** Test correct fallback to the default configuration. */
    public function testNonexistantFileFallback()
    {
        @Logger::configure('hopefully/this/path/doesnt/exist/config.xml');
        $this->testDefaultConfig();
    }

    public function testAppendersWithLayout()
    {
        Logger::configure([
            'rootLogger' => [
                'appenders' => ['app1', 'app2']
            ],
            'loggers' => [
                'myLogger' => [
                    'appenders' => ['app1'],
                    'additivity' => true
                ]
            ],
            'renderers' => [
                ['renderedClass' => 'stdClass', 'renderingClass' => LoggerRendererDefault::class]
            ],
            'appenders' => [
                'app1' => [
                    'class' => LoggerAppenderEcho::class,
                    'layout' => [
                        'class' => LoggerLayoutSimple::class
                    ],
                    'params' => [
                        'htmlLineBreaks' => false
                    ]
                ],
                'app2' => [
                    'class' => LoggerAppenderEcho::class,
                    'layout' => [
                        'class' => LoggerLayoutPattern::class,
                        'params' => [
                            'conversionPattern' => 'message: %m%n'
                        ]
                    ],
                    'filters' => [
                        [
                            'class' => LoggerFilterStringMatch::class,
                            'params' => [
                                'stringToMatch' => 'foo',
                                'acceptOnMatch' => false
                            ]
                        ]
                    ]
                ],
            ]
        ]);

        ob_start();
        Logger::getRootLogger()->info('info');
        $actual = ob_get_contents();
        ob_end_clean();

        $expected = "INFO - info" . PHP_EOL . "message: info" . PHP_EOL;
        $this->assertSame($expected, $actual);
    }

    public function testThreshold()
    {
        Logger::configure([
            'threshold' => 'WARN',
            'rootLogger' => [
                'appenders' => ['default']
            ],
            'appenders' => [
                'default' => [
                    'class' => LoggerAppenderEcho::class,
                ],
            ]
        ]);

        $actual = Logger::getHierarchy()->getThreshold();
        $expected = LoggerLevel::getLevelWarning();

        self::assertSame($expected, $actual);
    }

    public function testInvalidThreshold()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Invalid threshold value ['FOO'] specified. Ignoring threshold definition.");
        Logger::configure([
            'threshold' => 'FOO',
            'rootLogger' => [
                'appenders' => ['default']
            ],
            'appenders' => [
                'default' => [
                    'class' => LoggerAppenderEcho::class,
                ],
            ]
        ]);
    }

    public function testAppenderThreshold()
    {
        Logger::configure([
            'rootLogger' => [
                'appenders' => ['default']
            ],
            'appenders' => [
                'default' => [
                    'class' => LoggerAppenderEcho::class,
                    'threshold' => 'INFO'
                ],
            ]
        ]);

        $actual = Logger::getRootLogger()->getAppender('default')->getThreshold();
        $expected = LoggerLevel::getLevelInfo();

        self::assertSame($expected, $actual);
    }

    public function testAppenderInvalidThreshold()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Invalid threshold value [FOO] specified for appender [default]. Ignoring threshold definition.");
        Logger::configure([
            'rootLogger' => [
                'appenders' => ['default']
            ],
            'appenders' => [
                'default' => [
                    'class' => LoggerAppenderEcho::class,
                    'threshold' => 'FOO'
                ],
            ]
        ]);
    }

    public function testLoggerThreshold()
    {
        Logger::configure([
            'rootLogger' => [
                'appenders' => ['default'],
                'level' => 'ERROR'
            ],
            'loggers' => [
                'default' => [
                    'appenders' => ['default'],
                    'level' => 'WARN'
                ]
            ],
            'appenders' => [
                'default' => [
                    'class' => LoggerAppenderEcho::class,
                ],
            ]
        ]);

        // Check root logger
        $actual = Logger::getRootLogger()->getLevel();
        $expected = LoggerLevel::getLevelError();
        self::assertSame($expected, $actual);

        // Check default logger
        $actual = Logger::getLogger('default')->getLevel();
        $expected = LoggerLevel::getLevelWarning();
        self::assertSame($expected, $actual);
    }

    public function testInvalidLoggerThreshold()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Invalid level value [FOO] specified for logger [default]. Ignoring level definition.");
        Logger::configure([
            'loggers' => [
                'default' => [
                    'appenders' => ['default'],
                    'level' => 'FOO'
                ]
            ],
            'appenders' => [
                'default' => [
                    'class' => LoggerAppenderEcho::class,
                ],
            ]
        ]);
    }

    public function testInvalidRootLoggerThreshold()
    {
        $this->expectException(\PHPUnit\Framework\Error\Error::class);
        $this->expectExceptionMessage("Invalid level value [FOO] specified for logger [root]. Ignoring level definition.");
        Logger::configure([
            'rootLogger' => [
                'appenders' => ['default'],
                'level' => 'FOO'
            ],
            'appenders' => [
                'default' => [
                    'class' => LoggerAppenderEcho::class,
                ],
            ]
        ]);
    }
}
