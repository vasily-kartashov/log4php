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

use ReflectionClass;

/**
 * The internal representation of logging event.
 */
class LoggerLoggingEvent
{

    /**
     * @var float|null
     */
    private static $startTime;

    /**
     * @var string Fully Qualified Class Name of the calling category class.
     */
    private $fqcn;

    /**
     * @var Logger reference
     */
    private $logger;

    /**
     * The category (logger) name.
     * This field will be marked as private in future
     * releases. Please do not access it directly.
     * Use the {@link getLoggerName()} method instead.
     * @var string
     */
    private $loggerName;

    /**
     * Level of the logging event.
     * @var LoggerLevel
     */
    protected $level;

    /**
     * The nested diagnostic context (NDC) of logging event.
     * @var string
     */
    private $ndc;

    /**
     * Have we tried to do an NDC lookup? If we did, there is no need
     * to do it again.    Note that its value is always false when
     * serialized. Thus, a receiving SocketNode will never use it's own
     * (incorrect) NDC. See also writeObject method.
     * @var boolean
     */
    private $ndcLookupRequired = true;

    /**
     * @var mixed The application supplied message of logging event.
     */
    private $message;

    /**
     * The application supplied message rendered through the log4php
     * object rendering mechanism. At present renderedMessage == message.
     * @var string|null
     */
    private $renderedMessage;

    /**
     * The name of thread in which this logging event was generated.
     * log4php saves here the process id via {@link PHP_MANUAL#getmypid getmypid()}
     * @var mixed
     */
    private $threadName;

    /**
     * The number of seconds elapsed from 1/1/1970 until logging event
     * was created plus microseconds if available.
     * @var float
     */
    public $timeStamp;

    /**
     * @var LoggerLocationInfo Location information for the caller.
     */
    private $locationInfo;

    /**
     * @var LoggerThrowableInformation|null log4php internal representation of throwable
     */
    private $throwableInfo;

    /**
     * @var array Logging event context
     */
    private $context = [];

    /**
     * Instantiate a LoggingEvent from the supplied parameters.
     *
     * Except {@link $timeStamp} all the other fields of
     * LoggerLoggingEvent are filled when actually needed.
     *
     * @param string $fqcn name of the caller class.
     * @param mixed $logger The {@link Logger} category of this event or the logger name.
     * @param LoggerLevel $level The level of this event.
     * @param mixed $message The message of this event.
     * @param integer $timeStamp the timestamp of this logging event.
     * @param array $context Context of the event
     */
    public function __construct($fqcn, $logger, LoggerLevel $level, $message, $timeStamp = null, array $context = [])
    {
        $this->fqcn = $fqcn;
        if ($logger instanceof Logger) {
            $this->logger = $logger;
            $this->loggerName = $logger->getName();
        } else {
            $this->loggerName = strval($logger);
        }
        $this->level = $level;
        $this->message = $message;
        if ($timeStamp !== null && is_numeric($timeStamp)) {
            $this->timeStamp = $timeStamp;
        } else {
            $this->timeStamp = microtime(true);
        }

        $this->context = $context;
        if (isset($context['exception'])) {
            $this->throwableInfo = new LoggerThrowableInformation($context['exception']);
        }
    }

    /**
     * Returns the full qualified class name.
     * TODO: PHP does contain namespaces in 5.3. Those should be returned too,
     * @return string
     */
    public function getFullQualifiedClassName()
    {
        return $this->fqcn;
    }

    /**
     * Set the location information for this logging event. The collected
     * information is cached for future use.
     *
     * <p>This method uses {@link PHP_MANUAL#debug_backtrace debug_backtrace()} function (if exists)
     * to collect information about caller.</p>
     * <p>It only recognize information generated by {@link Logger} and its subclasses.</p>
     * @return LoggerLocationInfo
     */
    public function getLocationInformation()
    {
        if ($this->locationInfo !== null) {
            return $this->locationInfo;
        }

        if (isset($this->context['exception'])) {
            /** @var \Throwable $throwable */
            $throwable = $this->context['exception'];
            $trace = $throwable->getTrace();
            $hop = $trace[0] ?? null;
            $this->locationInfo = new LoggerLocationInfo([
                'line'     => $throwable->getLine(),
                'file'     => $throwable->getFile(),
                'function' => $hop['function'] ?? 'main',
                'class'    => $hop['class'] ?? 'main',
            ]);
            return $this->locationInfo;
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $prevHop = null;
        $hop = array_pop($trace);

        while ($hop !== null) {
            /** @psalm-suppress RedundantCondition */
            if (isset($hop['class'])) {
                $interfaces = class_implements($hop['class']);
                if (isset($interfaces[GenericHandler::class]) || isset($interfaces[GenericLogger::class])) {
                    break;
                }
            }
            $prevHop = $hop;
            $hop = array_pop($trace);
        }

        $this->locationInfo = new LoggerLocationInfo([
            'line'     => $hop['line'] ?? null,
            'file'     => $hop['file'] ?? null,
            'function' => $prevHop['function'] ?? 'main',
            'class'    => $prevHop['class'] ?? 'main'
        ]);
        return $this->locationInfo;
    }

    /**
     * Return the level of this event. Use this form instead of directly
     * accessing the {@link $level} field.
     * @return LoggerLevel
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Returns the logger which created the event.
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Return the name of the logger. Use this form instead of directly
     * accessing the {@link $loggerName} field.
     * @return string
     */
    public function getLoggerName()
    {
        return $this->loggerName;
    }

    /**
     * Return the message for this logging event.
     * @return mixed
     * @todo maybe cacheing, but i doubt it
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * This method returns the NDC for this event. It will return the
     * correct content even if the event was generated in a different
     * thread or even on a different machine. The {@link LoggerNDC::get()} method
     * should <b>never</b> be called directly.
     * @return string
     */
    public function getNDC()
    {
        if ($this->ndcLookupRequired) {
            $this->ndcLookupRequired = false;
            $this->ndc = LoggerNDC::get();
        }
        return $this->ndc;
    }

    /**
     * Returns the the context corresponding to the <code>key</code> parameter.
     * @param string $key
     * @return string
     */
    public function getMDC($key)
    {
        return LoggerMDC::get($key);
    }

    /**
     * Returns the entire MDC context.
     * @return array
     */
    public function getMDCMap()
    {
        return LoggerMDC::getMap();
    }

    /**
     * Return event's context
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Render message.
     * @return string|null
     */
    public function getRenderedMessage()
    {
        if ($this->renderedMessage === null && $this->message !== null) {
            if (is_string($this->message)) {
                $pairs = [];
                foreach ($this->context as $key => $val) {
                    if (is_array($val)) {
                        $val = json_encode($val);
                    }
                    $pairs['{' . $key . '}'] = $val;
                }
                $this->renderedMessage = strtr($this->message, $pairs);
            } else {
                $rendererMap = Logger::getHierarchy()->getRendererMap();
                $this->renderedMessage = $rendererMap->findAndRender($this->message);
            }
        }
        return $this->renderedMessage;
    }

    /**
     * Returns the time when the application started, as a UNIX timestamp
     * with microseconds.
     * @return float
     * @psalm-suppress MixedInferredReturnType
     * @psalm-suppress MixedReturnStatement
     */
    public static function getStartTime()
    {
        if (!isset(self::$startTime)) {
            self::$startTime = microtime(true);
        }
        return self::$startTime;
    }

    /**
     * @return float
     */
    public function getTimestamp()
    {
        return $this->timeStamp;
    }

    /**
     * Returns the time in seconds passed from the beginning of execution to
     * the time the event was constructed.
     *
     * @return float Seconds with microseconds in decimals.
     */
    public function getRelativeTime()
    {
        return $this->timeStamp - self::getStartTime();
    }

    /**
     * Returns the time in milliseconds passed from the beginning of execution
     * to the time the event was constructed.
     *
     * @deprecated This method has been replaced by getRelativeTime which
     *        does not perform unnecessary multiplication and formatting.
     *
     * @return integer
     */
    public function getTime()
    {
        $eventTime = $this->getTimestamp();
        $eventStartTime = LoggerLoggingEvent::getStartTime();
        return (int) number_format(($eventTime - $eventStartTime) * 1000, 0, '', '');
    }

    /**
     * @return mixed
     */
    public function getThreadName()
    {
        if ($this->threadName === null) {
            $this->threadName = (string)getmypid();
        }
        return $this->threadName;
    }

    /**
     * @return LoggerThrowableInformation|null
     */
    public function getThrowableInformation()
    {
        return $this->throwableInfo;
    }

    /**
     * Serialize this object
     * @return string
     */
    public function toString()
    {
        return serialize($this);
    }

    /**
     * Avoid serialization of the {@link $logger} object
     */
    public function __sleep()
    {
        return [
            'fqcn',
            'loggerName',
            'level',
            'ndc',
            'ndcLookupRequired',
            'message',
            'renderedMessage',
            'threadName',
            'timeStamp',
            'locationInfo',
        ];
    }
}

LoggerLoggingEvent::getStartTime();
