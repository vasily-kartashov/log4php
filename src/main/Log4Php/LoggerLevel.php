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
 */

namespace Log4Php;

/**
 * Defines the minimum set of levels recognized by the system, that is
 * <i>OFF</i>, <i>FATAL</i>, <i>ERROR</i>,
 * <i>WARN</i>, <i>INFO</i>, <i>DEBUG</i> and
 * <i>ALL</i>.
 *
 * <p>The <i>LoggerLevel</i> class may be inherited from to define a larger
 * level set.</p>
 */
class LoggerLevel
{

    const OFF       = 2147483647;
    const EMERGENCY = 70000;
    const ALERT     = 60000;
    const CRITICAL  = 50000;
    const ERROR     = 40000;
    const WARNING   = 30000;
    const NOTICE    = 25000;
    const INFO      = 20000;
    const DEBUG     = 10000;
    const TRACE     = 5000;
    const ALL       = -2147483647;

    /**
     * Integer level value.
     * @var int
     */
    private $level;

    /**
     * Contains a list of instantiated levels.
     * @var array<int,LoggerLevel>
     */
    private static $levelMap;

    /**
     * String representation of the level.
     * @var string
     */
    private $levelStr;

    /**
     * Equivalent syslog level.
     * @var int
     */
    private $syslogEquivalent;

    /**
     * Constructor
     *
     * @param integer $level
     * @param string $levelStr
     * @param integer $syslogEquivalent
     */
    private function __construct($level, $levelStr, $syslogEquivalent)
    {
        $this->level = $level;
        $this->levelStr = $levelStr;
        $this->syslogEquivalent = $syslogEquivalent;
    }

    /**
     * Compares two logger levels.
     *
     * @param LoggerLevel $other
     * @return boolean
     */
    public function equals($other)
    {
        if ($other instanceof LoggerLevel) {
            if ($this->level == $other->level) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns an Off Level
     * @return LoggerLevel
     */
    public static function getLevelOff()
    {
        if (!isset(self::$levelMap[LoggerLevel::OFF])) {
            self::$levelMap[LoggerLevel::OFF] = new LoggerLevel(LoggerLevel::OFF, 'OFF', LOG_EMERG);
        }
        return self::$levelMap[LoggerLevel::OFF];
    }

    /**
     * Returns a Emergency Level
     * @return LoggerLevel
     */
    public static function getLevelEmergency()
    {
        if (!isset(self::$levelMap[LoggerLevel::EMERGENCY])) {
            self::$levelMap[LoggerLevel::EMERGENCY] = new LoggerLevel(LoggerLevel::EMERGENCY, 'EMERGENCY', LOG_EMERG);
        }
        return self::$levelMap[LoggerLevel::EMERGENCY];
    }

    /**
     * Returns a Alert Level
     * @return LoggerLevel
     */
    public static function getLevelAlert()
    {
        if (!isset(self::$levelMap[LoggerLevel::ALERT])) {
            self::$levelMap[LoggerLevel::ALERT] = new LoggerLevel(LoggerLevel::CRITICAL, 'ALERT', LOG_ALERT);
        }
        return self::$levelMap[LoggerLevel::ALERT];
    }

    /**
     * Returns a Critical Level
     * @return LoggerLevel
     */
    public static function getLevelCritical()
    {
        if (!isset(self::$levelMap[LoggerLevel::CRITICAL])) {
            self::$levelMap[LoggerLevel::CRITICAL] = new LoggerLevel(LoggerLevel::CRITICAL, 'CRITICAL', LOG_CRIT);
        }
        return self::$levelMap[LoggerLevel::CRITICAL];
    }

    /**
     * Returns an Error Level
     * @return LoggerLevel
     */
    public static function getLevelError()
    {
        if (!isset(self::$levelMap[LoggerLevel::ERROR])) {
            self::$levelMap[LoggerLevel::ERROR] = new LoggerLevel(LoggerLevel::ERROR, 'ERROR', LOG_ERR);
        }
        return self::$levelMap[LoggerLevel::ERROR];
    }

    /**
     * Returns a Warn Level
     * @return LoggerLevel
     */
    public static function getLevelWarning()
    {
        if (!isset(self::$levelMap[LoggerLevel::WARNING])) {
            self::$levelMap[LoggerLevel::WARNING] = new LoggerLevel(LoggerLevel::WARNING, 'WARNING', LOG_WARNING);
        }
        return self::$levelMap[LoggerLevel::WARNING];
    }

    /**
     * Returns a Notice Level
     * @return LoggerLevel
     */
    public static function getLevelNotice()
    {
        if (!isset(self::$levelMap[LoggerLevel::NOTICE])) {
            self::$levelMap[LoggerLevel::NOTICE] = new LoggerLevel(LoggerLevel::NOTICE, 'NOTICE', LOG_NOTICE);
        }
        return self::$levelMap[LoggerLevel::NOTICE];
    }

    /**
     * Returns an Info Level
     * @return LoggerLevel
     */
    public static function getLevelInfo()
    {
        if (!isset(self::$levelMap[LoggerLevel::INFO])) {
            self::$levelMap[LoggerLevel::INFO] = new LoggerLevel(LoggerLevel::INFO, 'INFO', LOG_INFO);
        }
        return self::$levelMap[LoggerLevel::INFO];
    }

    /**
     * Returns a Debug Level
     * @return LoggerLevel
     */
    public static function getLevelDebug()
    {
        if (!isset(self::$levelMap[LoggerLevel::DEBUG])) {
            self::$levelMap[LoggerLevel::DEBUG] = new LoggerLevel(LoggerLevel::DEBUG, 'DEBUG', LOG_DEBUG);
        }
        return self::$levelMap[LoggerLevel::DEBUG];
    }

    /**
     * Returns a Trace Level
     * @return LoggerLevel
     */
    public static function getLevelTrace()
    {
        if (!isset(self::$levelMap[LoggerLevel::TRACE])) {
            self::$levelMap[LoggerLevel::TRACE] = new LoggerLevel(LoggerLevel::TRACE, 'TRACE', LOG_DEBUG);
        }
        return self::$levelMap[LoggerLevel::TRACE];
    }

    /**
     * Returns an All Level
     * @return LoggerLevel
     */
    public static function getLevelAll()
    {
        if (!isset(self::$levelMap[LoggerLevel::ALL])) {
            self::$levelMap[LoggerLevel::ALL] = new LoggerLevel(LoggerLevel::ALL, 'ALL', LOG_DEBUG);
        }
        return self::$levelMap[LoggerLevel::ALL];
    }

    /**
     * Return the syslog equivalent of this level as an integer.
     * @return integer
     */
    public function getSyslogEquivalent()
    {
        return $this->syslogEquivalent;
    }

    /**
     * Returns <i>true</i> if this level has a higher or equal
     * level than the level passed as argument, <i>false</i>
     * otherwise.
     *
     * @param LoggerLevel $other
     * @return bool
     */
    public function isGreaterOrEqual($other)
    {
        return $this->level >= $other->level;
    }

    /**
     * Returns the string representation of this level.
     * @return string
     */
    public function toString()
    {
        return $this->levelStr;
    }

    /**
     * Returns the string representation of this level.
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Returns the integer representation of this level.
     * @return integer
     */
    public function toInt()
    {
        return $this->level;
    }

    /**
     * Convert the input argument to a level. If the conversion fails, then
     * this method returns the provided default level.
     *
     * @param mixed $arg The value to convert to level.
     * @param LoggerLevel|null $defaultLevel Value to return if conversion is not possible
     * @return LoggerLevel|null
     */
    public static function toLevel($arg, $defaultLevel = null)
    {
        if (is_int($arg)) {
            switch ($arg) {
                case self::ALL:
                    return self::getLevelAll();
                case self::TRACE:
                    return self::getLevelTrace();
                case self::DEBUG:
                    return self::getLevelDebug();
                case self::INFO:
                    return self::getLevelInfo();
                case self::NOTICE:
                    return self::getLevelNotice();
                case self::WARNING:
                    return self::getLevelWarning();
                case self::ERROR:
                    return self::getLevelError();
                case self::CRITICAL:
                    return self::getLevelCritical();
                case self::ALERT:
                    return self::getLevelAlert();
                case self::EMERGENCY:
                    return self::getLevelEmergency();
                case self::OFF:
                    return self::getLevelOff();
            }
        }
        if (is_string($arg)) {
            switch (strtoupper($arg)) {
                case 'ALL':
                    return self::getLevelAll();
                case 'TRACE':
                    return self::getLevelTrace();
                case 'DEBUG':
                    return self::getLevelDebug();
                case 'INFO':
                    return self::getLevelInfo();
                case 'NOTICE':
                    return self::getLevelNotice();
                case 'WARN':
                case 'WARNING':
                    return self::getLevelWarning();
                case 'ERROR':
                    return self::getLevelError();
                case 'CRITICAL':
                    return self::getLevelCritical();
                case 'ALERT':
                    return self::getLevelAlert();
                case 'EMERGENCY':
                    return self::getLevelEmergency();
                case 'OFF':
                    return self::getLevelOff();
            }
        }
        return $defaultLevel;
    }
}
