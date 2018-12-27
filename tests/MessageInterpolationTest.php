<?php

use Log4Php\Appenders\LoggerAppenderEcho;
use Log4Php\Layouts\LoggerLayoutSimple;
use Log4Php\Logger;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class MessageInterpolationTest extends TestCase
{
    public function testMessageInterpolation()
    {
        Logger::configure([
            'rootLogger' => [
                'appenders' => ['default'],
            ],
            'appenders' => [
                'default' => [
                    'class' => LoggerAppenderEcho::class,
                    'layout' => [
                        'class' => LoggerLayoutSimple::class
                    ],
                ]
            ]
        ]);

        $logger = Logger::getLogger(self::class);

        ob_start();
        $logger->info('Message interpolation {status}', ['status' => 'successful']);
        $actual = trim(ob_get_clean());

        Assert::assertEquals('INFO - Message interpolation successful', $actual);
    }
}