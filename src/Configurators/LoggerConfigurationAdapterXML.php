<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements. See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Log4Php\Configurators;

use Log4Php\LoggerException;
use SimpleXMLElement;

/**
 * Converts XML configuration files to a PHP array.
 */
class LoggerConfigurationAdapterXML implements LoggerConfigurationAdapter
{
    /** Path to the XML schema used for validation. */
    const SCHEMA_PATH = __DIR__ . '/../xml/log4php.xsd';

    /**
     * @var array<string,mixed>
     */
    private $config = [
        'appenders' => [],
        'loggers'   => [],
        'renderers' => [],
    ];

    /**
     * @param string $input
     * @return array
     * @throws LoggerException
     */
    public function convert($input): array
    {
        $xml = $this->loadXML($input);

        $this->parseConfiguration($xml);

        // Parse the <root> node
        if (isset($xml->root)) {
            $this->parseRootLogger($xml->root[0]);
        }

        // Process <logger> nodes
        if (isset($xml->logger)) {
            foreach ($xml->logger as $logger) {
                $this->parseLogger($logger);
            }
        }

        // Process <appender> nodes
        if (isset($xml->appender)) {
            foreach ($xml->appender as $appender) {
                $this->parseAppender($appender);
            }
        }

        // Process <renderer> nodes
        if (isset($xml->renderer)) {
            foreach ($xml->renderer as $rendererNode) {
                $this->parseRenderer($rendererNode);
            }
        }

        // Process <defaultRenderer> node
        if (isset($xml->defaultRenderer)) {
            foreach ($xml->defaultRenderer as $rendererNode) {
                $this->parseDefaultRenderer($rendererNode);
            }
        }

        return $this->config;
    }

    /**
     * Loads and validates the XML.
     * @param string $url Input XML.
     * @return SimpleXMLElement
     * @throws LoggerException
     * @psalm-suppress TypeDoesNotContainType
     */
    private function loadXML($url)
    {
        if (!file_exists($url)) {
            throw new LoggerException("File [$url] does not exist.");
        }

        libxml_clear_errors();
        $oldValue = libxml_use_internal_errors(true);

        $xml = simplexml_load_file($url);
        if ($xml !== false) {
            libxml_clear_errors();
            libxml_use_internal_errors($oldValue);
            return $xml;
        } else {
            $errorStr = "";
            foreach (libxml_get_errors() as $error) {
                $errorStr .= $error->message;
            }
            throw new LoggerException("Error loading configuration file: " . trim($errorStr));
        }
    }

    /**
     * Parses the <configuration> node.
     * @param SimpleXMLElement $xml
     * @return void
     */
    private function parseConfiguration(SimpleXMLElement $xml)
    {
        $attributes = $xml->attributes();
        if (isset($attributes['threshold'])) {
            $this->config['threshold'] = (string)$attributes['threshold'];
        }
    }

    /**
     * Parses a <root> node.
     * @param SimpleXMLElement $node
     * @return void
     */
    private function parseRootLogger(SimpleXMLElement $node)
    {
        $logger = [];

        if (isset($node->level)) {
            $logger['level'] = $this->getAttributeValue($node->level[0], 'value');
        }

        $logger['appenders'] = $this->parseAppenderReferences($node);

        $this->config['rootLogger'] = $logger;
    }

    /**
     * @param SimpleXMLElement $node
     * @param string $name
     * @return null|string
     */
    private function getAttributeValue(SimpleXMLElement $node, $name)
    {
        return isset($node[$name]) ? (string)$node[$name] : null;
    }

    /**
     * Parses a <logger> node for appender references and returns them in an array.
     *
     * Previous versions supported appender-ref, as well as appender_ref so both
     * are parsed for backward compatibility.
     * @param SimpleXMLElement $node
     * @return array
     */
    private function parseAppenderReferences(SimpleXMLElement $node)
    {
        $refs = [];
        if (isset($node->appender_ref)) {
            foreach ($node->appender_ref as $ref) {
                $refs[] = $this->getAttributeValue($ref, 'ref');
            }
        }

        foreach ($node->{'appender-ref'} as $ref) {
            $refs[] = $this->getAttributeValue($ref, 'ref');
        }

        return $refs;
    }

    /**
     * Parses a <logger> node.
     * @param SimpleXMLElement $node
     * @return void
     */
    private function parseLogger(SimpleXMLElement $node)
    {
        $logger = [];

        $name = $this->getAttributeValue($node, 'name');
        if (empty($name)) {
            /** @noinspection HtmlUnknownTag */
            $this->warn("A <logger> node is missing the required 'name' attribute. Skipping logger definition.");
            return;
        }

        if (isset($node->level)) {
            $logger['level'] = $this->getAttributeValue($node->level[0], 'value');
        }

        if (isset($node['additivity'])) {
            $logger['additivity'] = $this->getAttributeValue($node, 'additivity');
        }

        $logger['appenders'] = $this->parseAppenderReferences($node);

        // Check for duplicate loggers
        if (isset($this->config['loggers'][$name])) {
            $this->warn("Duplicate logger definition [$name]. Overwriting.");
        }

        $this->config['loggers'][$name] = $logger;
    }

    /**
     * @param string $message
     * @return void
     */
    private function warn($message)
    {
        trigger_error("log4php: " . $message, E_USER_WARNING);
    }

    /**
     * Parses an <appender> node.
     * @param SimpleXMLElement $node
     * @return void
     */
    private function parseAppender(SimpleXMLElement $node)
    {
        $name = $this->getAttributeValue($node, 'name');
        if (empty($name)) {
            /** @noinspection HtmlUnknownTag */
            $this->warn("An <appender> node is missing the required 'name' attribute. Skipping appender definition.");
            return;
        }

        $appender = [];
        $appender['class'] = $this->getAttributeValue($node, 'class');

        if (isset($node['threshold'])) {
            $appender['threshold'] = $this->getAttributeValue($node, 'threshold');
        }

        if (isset($node->layout)) {
            $appender['layout'] = $this->parseLayout($node->layout[0]);
        }

        if (isset($node->param)) {
            if (count($node->param) > 0) {
                $appender['params'] = $this->parseParameters($node);
            }
        }

        if (isset($node->filter)) {
            foreach ($node->filter as $filterNode) {
                $appender['filters'][] = $this->parseFilter($filterNode);
            }
        }

        $this->config['appenders'][$name] = $appender;
    }

    /**
     * Parses a <layout> node.
     * @param SimpleXMLElement $node
     * @return array
     */
    private function parseLayout(SimpleXMLElement $node)
    {
        $layout = [];
        $layout['class'] = $this->getAttributeValue($node, 'class');
        if (isset($node->param) && count($node->param) > 0) {
            $layout['params'] = $this->parseParameters($node);
        }
        return $layout;
    }

    /**
     * Parses any <param> child nodes returning them in an array.
     * @param SimpleXMLElement $paramsNode
     * @return array<string,string|null>
     */
    private function parseParameters($paramsNode)
    {
        $params = [];

        foreach ($paramsNode->param as $paramNode) {
            if (!isset($paramNode['name']) || empty($paramNode['name'])) {
                /** @noinspection RequiredAttributes */
                $this->warn("A <param> node is missing the required 'name' attribute. Skipping parameter.");
                continue;
            }

            /** @var SimpleXMLElement $paramNode */
            $name = $this->getAttributeValue($paramNode, 'name');
            $value = $this->getAttributeValue($paramNode, 'value');

            if ($name !== null) {
                $params[$name] = $value;
            }
        }

        return $params;
    }

    /**
     * Parses a <filter> node.
     * @param mixed $filterNode
     * @return array
     */
    private function parseFilter($filterNode)
    {
        $filter = [];
        $filter['class'] = $this->getAttributeValue($filterNode, 'class');

        if (count($filterNode->param) > 0) {
            $filter['params'] = $this->parseParameters($filterNode);
        }

        return $filter;
    }

    // ******************************************
    // ** Helper methods                       **
    // ******************************************

    /**
     * Parses a <renderer> node.
     * @param SimpleXMLElement $node
     * @return void
     */
    private function parseRenderer(SimpleXMLElement $node)
    {
        $renderedClass = $this->getAttributeValue($node, 'renderedClass');
        $renderingClass = $this->getAttributeValue($node, 'renderingClass');

        $this->config['renderers'][] = compact('renderedClass', 'renderingClass');
    }

    /**
     * Parses a <defaultRenderer> node
     * @param SimpleXMLElement $node
     * @return void
     */
    private function parseDefaultRenderer(SimpleXMLElement $node)
    {
        $renderingClass = $this->getAttributeValue($node, 'renderingClass');

        // Warn on duplicates
        if (isset($this->config['defaultRenderer'])) {
            /** @noinspection HtmlUnknownTag */
            $this->warn("Duplicate <defaultRenderer> node. Overwriting.");
        }

        $this->config['defaultRenderer'] = $renderingClass;
    }
}
