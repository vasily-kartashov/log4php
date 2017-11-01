<?php

namespace Log4Php\Appenders;

use Log4Php\LoggerAppender;
use Log4Php\LoggerLoggingEvent;

class LoggerAppenderUnixSocket extends LoggerAppender
{
    /** @var string */
    protected $path;

    /** @var resource|bool */
    private $socket;

    /**
     * @return void
     */
    public function activateOptions()
    {
        if (empty($this->path)) {
            $this->warn("Required parameter [path] not set. Closing appender.");
            $this->closed = true;
            return;
        }
        $this->closed = false;
    }

    /**
     * @param LoggerLoggingEvent $event
     * @return void
     * @psalm-suppress TypeDoesNotContainType
     */
    protected function append(LoggerLoggingEvent $event)
    {
        if (!is_resource($this->socket)) {
            $this->socket = fsockopen('unix://' . $this->path);
            if ($this->socket === false) {
                $this->warn("Could not open socket to {$this->path}. Closing appender.");
                $this->closed = true;
                return;
            }
        }
        $status = fwrite($this->socket, $this->layout->format($event));
        if ($status === false) {
            $this->warn("Error writing to socket. Closing appender.");
            $this->closed = true;
        }
    }

    /**
     * Returns the path.
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the path.
     * @param string $path
     * @return void
     */
    public function setPath(string $path)
    {
        $this->setString('path', $path);
    }

    /**
     * @return void
     */
    public function close()
    {
        parent::close();
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }
    }
}
