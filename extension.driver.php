<?php

require_once dirname(__FILE__) . '/lib/xmltoarray.php';
require_once dirname(__FILE__) . '/lib/xmltojson.php';

/**
 * Extension_JSONPage
 *
 * @uses Extension
 * @package Symphony\Extensions\APIPage
 * @version 1.0
 * @copyright 2012-2015 Soario Inc. <http://soario.com>
 * @author Thomas Appel <thomas@soario.com>
 * @license MIT
 */
class Extension_APIPage extends Extension
{

    /**
     * trigger
     *
     * @var Boolean
     * @access protected
     */
    protected $trigger = false;


    /**
     *
     */
    protected static $mime = array(
        'xml'  => 'application/xml',
        'json' => 'application/json',
    );

    /**
     * getSubscribedDelegates
     *
     * @access public
     * @return void
     */
    public function getSubscribedDelegates()
    {
        return array(
            array(
                'page' => '/frontend/',
                'delegate' => 'FrontendOutputPostGenerate',
                'callback' => 'xmlTojson'
            ),
            array(
                'page' => '/frontend/',
                'delegate' => 'FrontendPreRenderHeaders',
                'callback' => 'setContentType'
            ),
            array(
                'page' => '/system/preferences/',
                'delegate' => 'AddCustomPreferenceFieldsets',
                'callback' => 'appendPreferences'
            ),
            array(
                'page' => '/system/preferences/',
                'delegate' => 'Save',
                'callback' => 'savePreferences'
            ),
        );
    }

    /**
     * xmlToJSON
     *
     * @param Mixed $context
     * @access public
     * @return void
     */
    public function xmlToJSON($context)
    {
        if ($this->trigger) {
            $parser = new XmlToJSON($context['output']);
            $context['output'] = $parser->parse();
        }
    }

    /**
     * setContentType
     *
     * @param Mixed $context
     * @access public
     * @return void
     */
    public function setContentType($context)
    {

        $params = Frontend::Page()->Params();

        if (in_array('API', $params['page-types'])) {

            $param = Symphony::Configuration()->get('param-selector', 'apipage');
            $default = Symphony::Configuration()->get('default-format', 'apipage');

            $format = isset($params[$param]) ? strtolower($params[$param]) : $default;

            if (!isset(self::$mime[$format])) {
                throw new SymphonyErrorPage('format does not exist', 'API is having issues', 'generic', array('header' => 'HTTP/1.0 404 Not Found'));
            }

            Frontend::Page()->addHeaderToPage('Content-Type', self::$mime[$format]);

            if (strtolower($format) === 'json') {
                $this->trigger = true;
            }
        }
    }

    /**
     * appendPreferences
     *
     * @param Mixed $context
     * @access public
     * @return void
     */
    public function appendPreferences($context)
    {
        extract($context);

        $fieldset = new XMLElement('fieldset', null, array(
            'class' => 'settings',
            'id' => $this->name
        ));

        $legend = new XMLElement('legend', 'API Page');
        $fieldset->appendChild($legend);

        $div = new XMLElement('div', null, array(
            'class' => 'contents'
        ));

        $conf = Symphony::Configuration()->get('apipage');

        $selected = isset($conf['default-format']) ? $conf['default-format'] : 'json';
        $selector = isset($conf['param-selector']) ? $conf['param-selector'] : 'url-format';

        $options = array(array('xml', ($selected === 'xml') ? true : false, 'xml'),
            array('json', ($selected === 'json') ? true : false, 'json')
        );
        $select = Widget::Select('settings[apipage][default-format]', $options);

        $label = Widget::Label(__('default output format'), $select);

        $div->appendChild($label);

        $selector = Widget::Input('settings[apipage][param-selector]', $selector, 'text');

        $label = Widget::Label(__('format parameter'), $selector);

        $div->appendChild($label);

        $fieldset->appendChild($div);
        $wrapper->appendChild($fieldset);
    }

    /**
     * savePreferences
     *
     * @param Mixed $context
     * @param Mixed $override
     * @access public
     * @return void
     */
    public function savePreferences($context, $override = false)
    {
        foreach ($context['settings']['apipage'] as $key => $val) {
            Symphony::Configuration()->set($key, $val, 'apipage');
        }
        Symphony::Configuration()->write();
    }
}
