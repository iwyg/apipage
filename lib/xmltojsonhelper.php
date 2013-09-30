<?php

/**
 * This File is part of the apipage\lib package
 *
 * (c) Thomas Appel <mail@thomas-appel.com>
 *
 * For full copyright and license information, please refer to the LICENSE file
 * that was distributed with this package.
 */

/**
 * @class XmlToJsonHelper extends XmlToArray xmltojsonhelper
 * @see XmlToArray
 *
 * @package extensions\apipage\lib
 * @version $Id$
 * @author Thomas Appel <mail@thomas-appel.com>
 * @license MIT
 */
class XmlToJsonHelper extends XmlToArray
{
    /**
     * setXml
     *
     * @param SimpleXMLElement $element
     *
     * @access public
     * @return void
     */
    public function setXml(SimpleXMLElement $element)
    {
        $this->xml = $element;
    }

    /**
     * loadXml
     *
     * @param mixed $xml
     *
     * @access protected
     * @return void
     */
    protected function loadXml($xml)
    {
        return $this->xml;
    }
}
