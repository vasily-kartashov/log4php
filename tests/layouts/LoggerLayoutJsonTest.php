<?php

use Log4Php\Logger;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class LoggerLayoutJsonTest extends TestCase
{
    public function testJsonFormat()
    {
        $config = LoggerTestHelper::getEchoJsonConfig();
        Logger::configure($config);

        $e = new Exception('exception');
        $log = Logger::getLogger('LoggerTest');
        $log->addContextResolver(function() {
            return [
                'userId' => 22,
                'request' => [
                    'page' => 'home',
                    'ip' => '127.0.0.1'
                ]
            ];
        });

        ob_start();
        $log->error("my message for {name} {userId}", ['exception' => $e, 'name' => 'you']); $line = __LINE__;
        $actual = ob_get_contents();
        ob_end_clean();

        $entry = json_decode($actual, true);
        Assert::assertArrayHasKey('level', $entry);
        Assert::assertEquals(22, $entry['context']['userId']);
        Assert::assertEquals('my message for you 22', $entry['message']);
    }
}
