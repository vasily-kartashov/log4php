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

use Log4Php\Renderers\LoggerRendererException;
use Throwable;

/**
 * The internal representation of throwable objects.
 */
class LoggerThrowableInformation
{
    /**
     * @var Throwable Throwable to log
     */
    private $throwable;

    /**
     * @var array Array of throwable messages
     */
    private $throwableArray;

    /**
     * Create a new instance
     * @param Throwable $throwable a throwable as a exception
     */
    public function __construct(Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    /**
     * Return source exception
     * @return Throwable
     */
    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }

    /**
     * Returns string representation of throwable
     * @return array
     */
    public function getStringRepresentation()
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (!is_array($this->throwableArray)) {
            $renderer = new LoggerRendererException();
            $this->throwableArray = explode("\n", $renderer->render($this->throwable));
        }
        return $this->throwableArray;
    }
}
