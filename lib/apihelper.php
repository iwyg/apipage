<?php

/**
 * This File is part of the extensions\apipage\lib package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

/**
 * @class
 * @package extensions\apipage\lib
 * @version $Id$
 */
class ApiHelper
{
    private static $parser;

    public static function simpleJson($node, $select = null)
    {
        $node = current($node);
        $xml = simplexml_import_dom($node);
        $parent = $xml->getName();
        $data = array();
        $data[$parent] = json_decode(json_encode((array)$xml), true);

        if (!is_null($select)) {
            try {
                $data = static::searchKey($data, $select);
            } catch (Exception $e) {
                $data = array();
            }
        }
        return json_encode($data);
    }

    public static function toJson($node, $select = null)
    {
        $node = current($node);
        $parser = static::getParserInstance();
        $xml = simplexml_import_dom($node);
        $parser->setXml($xml);
        $result = $parser->parse();
        if (!is_null($select)) {
            $result = static::searchKey($result, $select);
        }
        return json_encode($result);
    }

    private static function searchKey(array $input, $key)
    {
        $keys  = explode('.', $key);
        $pointer = $input;
        while (count($keys)) {
            if (isset($pointer[$k = array_shift($keys)])) {
                $pointer =& $pointer[$k];
            } else {
                return $pointer;
            }
        }
        return $pointer;
    }

    /**
     * getParserInstance
     *
     * @access private
     * @return InterfaceParser
     */
    private static function getParserInstance()
    {
        if (is_null(static::$parser)) {
            static::$parser = new XmlToJsonHelper('');
        }
        return static::$parser;
    }
}
