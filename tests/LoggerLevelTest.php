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

use Log4Php\LoggerLevel;
use PHPUnit\Framework\TestCase;

class LoggerLevelTest extends TestCase
{

    protected function doTestLevel(LoggerLevel $level, $code, $str, $syslog)
    {
        self::assertEquals($level->toInt(), $code);
        self::assertEquals($level->toString(), $str);
        self::assertEquals($level->getSyslogEquivalent(), $syslog);
    }

    public function testLevelOff()
    {
        $this->doTestLevel(LoggerLevel::getLevelOff(), LoggerLevel::OFF, 'OFF', LOG_EMERG);
        $this->doTestLevel(LoggerLevel::toLevel(LoggerLevel::OFF), LoggerLevel::OFF, 'OFF', LOG_EMERG);
        $this->doTestLevel(LoggerLevel::toLevel('OFF'), LoggerLevel::OFF, 'OFF', LOG_EMERG);
    }

    public function testLevelFatal()
    {
        $this->doTestLevel(LoggerLevel::getLevelCritical(), LoggerLevel::CRITICAL, 'CRITICAL', LOG_CRIT);
        $this->doTestLevel(LoggerLevel::toLevel(LoggerLevel::CRITICAL), LoggerLevel::CRITICAL, 'CRITICAL', LOG_CRIT);
        $this->doTestLevel(LoggerLevel::toLevel('CRITICAL'), LoggerLevel::CRITICAL, 'CRITICAL', LOG_CRIT);
    }

    public function testLevelError()
    {
        $this->doTestLevel(LoggerLevel::getLevelError(), LoggerLevel::ERROR, 'ERROR', LOG_ERR);
        $this->doTestLevel(LoggerLevel::toLevel(LoggerLevel::ERROR), LoggerLevel::ERROR, 'ERROR', LOG_ERR);
        $this->doTestLevel(LoggerLevel::toLevel('ERROR'), LoggerLevel::ERROR, 'ERROR', LOG_ERR);
    }

    public function testLevelWarn()
    {
        $this->doTestLevel(LoggerLevel::getLevelWarning(), LoggerLevel::WARNING, 'WARNING', LOG_WARNING);
        $this->doTestLevel(LoggerLevel::toLevel(LoggerLevel::WARNING), LoggerLevel::WARNING, 'WARNING', LOG_WARNING);
        $this->doTestLevel(LoggerLevel::toLevel('WARNING'), LoggerLevel::WARNING, 'WARNING', LOG_WARNING);
    }

    public function testLevelInfo()
    {
        $this->doTestLevel(LoggerLevel::getLevelInfo(), LoggerLevel::INFO, 'INFO', LOG_INFO);
        $this->doTestLevel(LoggerLevel::toLevel(LoggerLevel::INFO), LoggerLevel::INFO, 'INFO', LOG_INFO);
        $this->doTestLevel(LoggerLevel::toLevel('INFO'), LoggerLevel::INFO, 'INFO', LOG_INFO);
    }

    public function testLevelDebug()
    {
        $this->doTestLevel(LoggerLevel::getLevelDebug(), LoggerLevel::DEBUG, 'DEBUG', LOG_DEBUG);
        $this->doTestLevel(LoggerLevel::toLevel(LoggerLevel::DEBUG), LoggerLevel::DEBUG, 'DEBUG', LOG_DEBUG);
        $this->doTestLevel(LoggerLevel::toLevel('DEBUG'), LoggerLevel::DEBUG, 'DEBUG', LOG_DEBUG);
    }

    public function testLevelTrace()
    {
        $this->doTestLevel(LoggerLevel::getLevelTrace(), LoggerLevel::TRACE, 'TRACE', LOG_DEBUG);
        $this->doTestLevel(LoggerLevel::toLevel(LoggerLevel::TRACE), LoggerLevel::TRACE, 'TRACE', LOG_DEBUG);
        $this->doTestLevel(LoggerLevel::toLevel('TRACE'), LoggerLevel::TRACE, 'TRACE', LOG_DEBUG);
    }

    public function testLevelAll()
    {
        $this->doTestLevel(LoggerLevel::getLevelAll(), LoggerLevel::ALL, 'ALL', LOG_DEBUG);
        $this->doTestLevel(LoggerLevel::toLevel(LoggerLevel::ALL), LoggerLevel::ALL, 'ALL', LOG_DEBUG);
        $this->doTestLevel(LoggerLevel::toLevel('ALL'), LoggerLevel::ALL, 'ALL', LOG_DEBUG);
    }
}
