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

    protected $xmlErrors;

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
        if (!$xmlObj = $this->loadXml($this->xml)) {
            return $this->xmlErrors;
        }

        $root = $xmlObj->getName();
        $data = $this->parseXMLOBJ($xmlObj);
        return array($root => $data);
    }

    protected function loadXml($xml)
    {
        $usedInternalErrors = libxml_use_internal_errors(true);
        $externalEntitiesDisabled = libxml_disable_entity_loader(false);
        libxml_clear_errors();

        set_error_handler(array($this, 'handleXmlErrors'));

        if (!$xmlObj = simplexml_load_string($this->xml, null, LIBXML_NONET|LIBXML_DTDATTR)) {
            $this->resetErrors($usedInternalErrors, $externalEntitiesDisabled);
            return $this->handleXmlErrors(libxml_get_errors());
        }

        restore_error_handler();

        return $xmlObj;
    }

    /**
     * resetErrors
     *
     * @param mixed $usedInternalErrors
     * @param mixed $externalEntitiesDisabled
     *
     * @access protected
     * @return mixed
     */
    protected function resetErrors($usedInternalErrors, $externalEntitiesDisabled)
    {
        libxml_use_internal_errors($usedInternalErrors);
        libxml_disable_entity_loader($externalEntitiesDisabled);
    }

    /**
     * handleXmlErrors
     *
     * @param array $errors
     *
     * @access protected
     * @return mixed
     */
    protected function handleXmlErrors(array $errors)
    {
        $err = array();

        foreach ($errors as $i => $error) {
            $err[$i] = $error->message;
        }

        $this->xmlErrors = array('error' => 'xml error');
        return false;
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
                $result['text'] = $text;
            }
            $result = array_merge($attributes, $result);
            return $result;

        } elseif (!is_null($text)) {
            if (!empty($result)) {
                $result['text'] = $text;
            } else {
                $result = $this->getValue($text);
            }
            return $result;
        }
        return (empty($result) && is_null($text)) ? null : $result;
    }

    private function getValue($value)
    {
        if (is_numeric($value)) {
            if (false !== strpos($value, '.')) {
                return (float)$value;
            }
            return (int)$value;
        } elseif (in_array(strtolower($v = $value), array('true', 'false'))) {
            return 'false' === $v ? false : true;
        }
        return $value;
    }
}
