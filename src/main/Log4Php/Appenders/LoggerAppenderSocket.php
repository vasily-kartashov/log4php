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

use Log4Php\Layouts\LoggerLayoutSerialized;
use Log4Php\LoggerAppender;
use Log4Php\LoggerLoggingEvent;

/**
 * LoggerAppenderSocket appends to a network socket.
 *
 * ## Configurable parameters: ##
 *
 * - **remoteHost** - Target remote host.
 * - **port** - Target port (optional, defaults to 4446).
 * - **timeout** - Connection timeout in seconds (optional, defaults to
 *     'default_socket_timeout' from php.ini)
 *
 * The socket will by default be opened in blocking mode.
 */
class LoggerAppenderSocket extends LoggerAppender
{
    /**
     * Target host.
     * @see http://php.net/manual/en/function.fsockopen.php
     * @var string|null
     */
    protected $remoteHost;

    /**
     * Target port
     * @var int
     */
    protected $port = 4446;

    /**
     * Connection timeout in ms.
     * @var int|null
     */
    protected $timeout;

    // ******************************************
    // *** Appender methods                   ***
    // ******************************************

    /**
     * Override the default layout to use serialized.
     * @return LoggerLayoutSerialized
     */
    public function getDefaultLayout(): LoggerLayoutSerialized
    {
        return new LoggerLayoutSerialized();
    }

    /**
     * @return void
     */
    public function activateOptions()
    {
        if (empty($this->remoteHost)) {
            $this->warn("Required parameter [remoteHost] not set. Closing appender.");
            $this->closed = true;
            return;
        }

        if (empty($this->timeout)) {
            $timeout = ini_get("default_socket_timeout");
            if (!empty($timeout)) {
                $this->timeout = (int) $timeout;
            }
        }

        $this->closed = false;
    }

    /**
     * @param LoggerLoggingEvent $event
     * @return void
     */
    public function append(LoggerLoggingEvent $event)
    {
        $socket = fsockopen($this->remoteHost, $this->port, $errno, $errstr, $this->timeout);
        if ($socket === false) {
            $this->warn("Could not open socket to {$this->remoteHost}:{$this->port}. Closing appender.");
            $this->closed = true;
            return;
        }

        if (false === fwrite($socket, $this->layout->format($event))) {
            $this->warn("Error writing to socket. Closing appender.");
            $this->closed = true;
        }
        fclose($socket);
    }

    // ******************************************
    // *** Accessor methods                   ***
    // ******************************************

    /**
     * Returns the target host.
     * @return string
     */
    public function getRemoteHost()
    {
        return $this->getRemoteHost();
    }

    /**
     * Sets the target host.
     * @param string $hostname
     * @return void
     */
    public function setRemoteHost(string $hostname)
    {
        $this->setString('remoteHost', $hostname);
    }

    /**
     * Returns the target port.
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Sets the target port
     * @param int $port
     * @return void
     */
    public function setPort(int $port)
    {
        $this->setPositiveInteger('port', $port);
    }

    /**
     * Returns the timeout
     * @return int|null
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Sets the timeout.
     * @param int $timeout
     * @return void
     */
    public function setTimeout(int $timeout)
    {
        $this->setPositiveInteger('timeout', $timeout);
    }
}
