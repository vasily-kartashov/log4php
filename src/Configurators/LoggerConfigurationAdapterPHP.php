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

namespace Log4Php\Configurators;

use Log4Php\LoggerException;

/**
 * Converts PHP configuration files to a PHP array.
 *
 * The file should only hold the PHP config array preceded by "return".
 *
 * Example PHP config file:
 * <code>
 * <?php
 * return array(
 *   'rootLogger' => array(
 *     'level' => 'info',
 *     'appenders' => array('default')
 *   ),
 *   'appenders' => array(
 *     'default' => array(
 *       'class' => 'LoggerAppenderEcho',
 *       'layout' => array(
 *        'class' => 'LoggerLayoutSimple'
 *        )
 *     )
 *   )
 * )
 * ?>
 * </code>
 */
class LoggerConfigurationAdapterPHP implements LoggerConfigurationAdapter
{
    /**
     * @param string $url
     * @return array
     * @throws LoggerException
     */
    public function convert($url)
    {
        if (!file_exists($url)) {
            throw new LoggerException("File [$url] does not exist.");
        }

        /** @noinspection PhpIncludeInspection */
        /** @psalm-suppress UnresolvableInclude */
        $config = include($url);

        if ($config === false) {
            $error = error_get_last();
            if ($error) {
                throw new LoggerException("Error parsing configuration: " . $error['message']);
            } else {
                throw new LoggerException("Failed to include config, but no error reported");
            }
        }
        if (empty($config)) {
            throw new LoggerException("Invalid configuration: empty configuration array.");
        }
        if (!is_array($config)) {
            throw new LoggerException("Invalid configuration: not an array.");
        }
        return $config;
    }
}
