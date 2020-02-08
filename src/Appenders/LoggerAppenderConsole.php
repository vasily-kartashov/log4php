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

namespace Log4Php\Appenders;

use Log4Php\LoggerAppender;
use Log4Php\LoggerLoggingEvent;

/**
 * LoggerAppenderConsole appends log events either to the standard output
 * stream (php://stdout) or the standard error stream (php://stderr).
 *
 * **Note**: Use this Appender with command-line php scripts. On web scripts
 * this appender has no effects.
 *
 * This appender uses a layout.
 *
 * ## Configurable parameters: ##
 *
 * - **target** - the target stream: "stdout" or "stderr"
 */
class LoggerAppenderConsole extends LoggerAppender
{

    /** The standard output stream.  */
    const STDOUT = 'php://stdout';

    /** The standard error stream.*/
    const STDERR = 'php://stderr';

    /**
     * The 'target' parameter.
     * @var string
     */
    protected $target = self::STDOUT;

    /**
     * Stream resource for the target stream.
     * @var resource|closed-resource|bool|null
     */
    protected $fp = null;

    public function activateOptions()
    {
        $this->fp = fopen($this->target, 'w');
        if (is_resource($this->fp) && $this->layout !== null) {
            $message = $this->layout->getHeader();
            if ($message !== null) {
                fwrite($this->fp, $message);
            }
        }
        $this->closed = is_resource($this->fp) === false;
    }

    /**
     * @return void
     */
    public function close()
    {
        if ($this->closed != true) {
            if (is_resource($this->fp) && $this->layout !== null) {
                $message = $this->layout->getFooter();
                if ($message) {
                    fwrite($this->fp, $message);
                }
                fclose($this->fp);
            }
            $this->closed = true;
        }
    }

    /**
     * @param LoggerLoggingEvent $event
     * @return void
     */
    public function append(LoggerLoggingEvent $event)
    {
        if (is_resource($this->fp) && $this->layout !== null) {
            $message = $this->layout->format($event);
            if ($message !== null) {
                fwrite($this->fp, $message);
            }
        }
    }

    /**
     * Returns the value of the 'target' parameter.
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Sets the 'target' parameter.
     * @param string $target
     * @return void
     */
    public function setTarget($target)
    {
        $value = trim($target);
        if ($value == self::STDOUT || strtoupper($value) == 'STDOUT') {
            $this->target = self::STDOUT;
        } elseif ($value == self::STDERR || strtoupper($value) == 'STDERR') {
            $this->target = self::STDERR;
        } else {
            $value = var_export($target, true);
            $this->warn("Invalid value given for 'target' property: {$value}. Property not set.");
        }
    }
}
