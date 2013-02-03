<?php

/**
 * XmlToArray
 *
 * Parse XML to corrseponding Array structure.
 *
 * @package Symphony\Extensions\APIPage
 * @version 1.3
 * @author Thomas Appel <thomas@soario.com>
 * @license MIT
 */
class XmlToArray implements InterfaceParser
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

        $children = $xml->children(null, false);
        $attributes = (array)$xml->attributes();
        $text = trim((string)$xml);



        foreach ($children as $name => $child) {

            if (isset($result[$name])) {
                continue;
            }

            $siblings = $child->xpath(sprintf("following-sibling::*[name() = '%s']", $name));

            if (empty($siblings)) {
                $result[$name] = $this->parseXMLOBJ($child);
                continue;
            }

            $result[$name][] = $this->parseXMLOBJ($child);

            foreach ($siblings as $sibling) {
                $result[$name][] = $this->parseXMLOBJ($sibling);
            }
            continue;
        }


        if (strlen($text) === 0) {
            $text = null;
        }

        if (!empty($attributes)) {
            if (!is_null($text)) {
                $result['value'] = $text;
            }
            $result = array_merge($attributes, $result);
            return $result;

        } else if (!is_null($text)) {
            $result = $text;
            return $result;
        }
        return (empty($result) && is_null($text)) ? null : $result;
    }
}
