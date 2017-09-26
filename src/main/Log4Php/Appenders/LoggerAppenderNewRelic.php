<?php

namespace Log4Php\Appenders;

use Log4Php\LoggerAppender;
use Log4Php\LoggerLoggingEvent;

class LoggerAppenderNewRelic extends LoggerAppender
{
    protected function append(LoggerLoggingEvent $event)
    {
        $context = $event->getContext() + $event->getLogger()->resolveExtendedContext();
        $message = $event->getRenderedMessage() . PHP_EOL . json_encode(array_filter($context));

        if (extension_loaded('newrelic')) {
            if (isset($context['exception'])) {
                newrelic_notice_error($message, $context['exception']);
            } else {
                newrelic_notice_error($message);
            }
        }
    }
}
