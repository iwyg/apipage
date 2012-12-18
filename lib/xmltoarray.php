<?php

/**
 * XmlToArray
 *
 * Parse XML to corrseponding Array structure.
 *
 * @package Symphony\Extensions\APIPage
 * @version 1.0
 * @author Thomas Appel <thomas@soario.com>
 * @license MIT
 */
class XmlToArray
{

    /**
     * xml
     *
     * @var String
     * @access protected
     */
    protected $xml;

    /**
     * __construct
     *
     * @param Mixed $xml
     * @access public
     */
    public function __construct($xml)
    {
        $this->xml = $xml;
    }

    /**
     * parse
     *
     * @access public
     * @return void
     */
    public function parse()
    {
        $xmlObj = simplexml_load_string($this->xml, null, LIBXML_DTDATTR);
        $root = $xmlObj->getName();
        $data = $this->parseXMLOBJ($xmlObj);
        return array($root => $data);
    }

    /**
     * parseXMLOBJ
     *
     * @param SimpleXMLElement $xml
     * @param array $result
     * @access protected
     * @return void
     */
    protected function parseXMLOBJ(SimpleXMLElement $xml, array &$result = array())
    {

        $attributes = (array)$xml->attributes(null, true);

        $children = $xml->children(null, true);

        foreach ($children as $name => $child) {

            if ($sibling = $child->xpath('preceding-sibling::* | following-sibling::*')) {

                if (current($sibling)->getName() === $name) {
                    $result[$name][] = $this->parseXMLOBJ($child);
                    continue;
                }
            }

            $result[$name] = $this->parseXMLOBJ($child);

        }

        $text = trim((string)$xml);

        if (empty($text)) {
            $text = null;
        }

        if (!empty($attributes)) {
            if (!is_null($text)) {
                $result['value'] = $text;
                $result = array_merge($attributes, $result);
            }
            return $result;
        } else if (!is_null($text)) {
            $result = $text;
            return $result;
        }
        return $result;
    }
}
