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
 * @subpackage helpers
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @version    $Revision$
 * @link       http://logging.apache.org/log4php
 */

use Log4Php\Appenders\LoggerAppenderFile;
use Log4Php\Helpers\LoggerOptionConverter;
use PHPUnit\Framework\TestCase;

define('MY_CONSTANT_CONSTANT', 'DEFINE');
define('MY_CONSTANT_CONSTANT_OTHER', 'DEFINE_OTHER');

/**
 * @group helpers
 */
class LoggerOptionConverterTest extends TestCase
{

    public function testToBoolean()
    {
        self::assertTrue(LoggerOptionConverter::toBooleanEx(1));
        self::assertTrue(LoggerOptionConverter::toBooleanEx("1"));
        self::assertTrue(LoggerOptionConverter::toBooleanEx(true));
        self::assertTrue(LoggerOptionConverter::toBooleanEx("true"));
        self::assertTrue(LoggerOptionConverter::toBooleanEx("on"));
        self::assertTrue(LoggerOptionConverter::toBooleanEx("yes"));

        self::assertFalse(LoggerOptionConverter::toBooleanEx(0));
        self::assertFalse(LoggerOptionConverter::toBooleanEx("0"));
        self::assertFalse(LoggerOptionConverter::toBooleanEx(false));
        self::assertFalse(LoggerOptionConverter::toBooleanEx("false"));
        self::assertFalse(LoggerOptionConverter::toBooleanEx("off"));
        self::assertFalse(LoggerOptionConverter::toBooleanEx("no"));
    }

    /**
     * Test fail on NULL.
     */
    public function testToBooleanFailure1()
    {
        $this->expectException(Log4Php\LoggerException::class);
        $this->expectExceptionMessage("Given value [NULL] cannot be converted to boolean.");
        LoggerOptionConverter::toBooleanEx(null);
    }

    /**
     * Test fail on invalid string.
     */
    public function testToBooleanFailure2()
    {
        $this->expectException(Log4Php\LoggerException::class);
        $this->expectExceptionMessage("Given value ['foo'] cannot be converted to boolean.");
        LoggerOptionConverter::toBooleanEx('foo');
    }

    public function testToInteger()
    {
        self::assertSame(1, LoggerOptionConverter::toIntegerEx('1'));
        self::assertSame(1, LoggerOptionConverter::toIntegerEx(1));
        self::assertSame(0, LoggerOptionConverter::toIntegerEx('0'));
        self::assertSame(0, LoggerOptionConverter::toIntegerEx(0));
        self::assertSame(-1, LoggerOptionConverter::toIntegerEx('-1'));
        self::assertSame(-1, LoggerOptionConverter::toIntegerEx(-1));
    }

    /**
     * Test fail on NULL.
     */
    public function testToIntegerFailure1()
    {
        $this->expectException(Log4Php\LoggerException::class);
        $this->expectExceptionMessage("Given value [NULL] cannot be converted to integer.");
        LoggerOptionConverter::toIntegerEx(null);
    }

    /**
     * Test fail on empty string.
     */
    public function testToIntegerFailure2()
    {
        $this->expectException(Log4Php\LoggerException::class);
        $this->expectExceptionMessage("Given value [''] cannot be converted to integer.");
        LoggerOptionConverter::toIntegerEx('');
    }

    /**
     * Test fail on invalid string.
     */
    public function testToIntegerFailure3()
    {
        $this->expectException(Log4Php\LoggerException::class);
        $this->expectExceptionMessage("Given value ['foo'] cannot be converted to integer.");
        LoggerOptionConverter::toIntegerEx('foo');
    }

    /**
     * Test fail on boolean.
     */
    public function testToIntegerFailure4()
    {
        $this->expectException(Log4Php\LoggerException::class);
        $this->expectExceptionMessage("Given value [true] cannot be converted to integer.");
        LoggerOptionConverter::toIntegerEx(true);
    }

    /**
     * Test fail on boolean.
     */
    public function testToIntegerFailure5()
    {
        $this->expectException(Log4Php\LoggerException::class);
        $this->expectExceptionMessage("Given value [false] cannot be converted to integer.");
        LoggerOptionConverter::toIntegerEx(false);
    }

    public function testSubstituteConstants()
    {
        define('OTHER_CONSTANT', 'OTHER');
        define('MY_CONSTANT', 'TEST');
        define('NEXT_CONSTANT', 'NEXT');

        $result = LoggerOptionConverter::substConstants('Value of key is ${MY_CONSTANT}.');
        self::assertEquals('Value of key is TEST.', $result);

        $result = LoggerOptionConverter::substConstants('Value of key is ${MY_CONSTANT} or ${OTHER_CONSTANT}.');
        self::assertEquals('Value of key is TEST or OTHER.', $result);

        $result = LoggerOptionConverter::substConstants('Value of key is ${MY_CONSTANT_CONSTANT}.');
        self::assertEquals('Value of key is DEFINE.', $result);

        $result = LoggerOptionConverter::substConstants('Value of key is ${MY_CONSTANT_CONSTANT} or ${MY_CONSTANT_CONSTANT_OTHER}.');
        self::assertEquals('Value of key is DEFINE or DEFINE_OTHER.', $result);
    }

    public function testActualSubstituteConstants()
    {
        $a = new LoggerAppenderFile();
        $a->setFile('${PHPUNIT_TEMP_DIR}/log.txt');
        $actual = $a->getFile();
        $expected = PHPUNIT_TEMP_DIR . '/log.txt';
        self::assertSame($expected, $actual);
    }
}
