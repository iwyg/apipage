<?php

/**
 * XmltoJSON
 *
 * @uses XmlToArray
 * @package Symphony\Extensions\APIPage
 * @version 1.0
 * @author Thomas Appel <thomas@soario.com>
 * @license MIT
 */
class XmltoJSON extends XmlToArray
{
    /**
     * parse
     *
     * @access public
     * @return void
     */
    public function parse()
    {
        return json_encode(parent::parse());
    }
}
