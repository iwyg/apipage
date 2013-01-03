<?php

require_once dirname(__FILE__) . '/lib/interfaceparser.php';
require_once dirname(__FILE__) . '/lib/xmltoarray.php';
require_once dirname(__FILE__) . '/lib/xmltojson.php';
require_once dirname(__FILE__) . '/lib/apipage.php';

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

    public static $defaults = array(
        'default-format' => 'json',
        'param-selector' => 'url-format',
    );
    /**
     * apipage
     *
     * @var APIPage
     * @access protected
     */
    protected $apipage;

    /**
     * getSubscribedDelegates
     *
     * @see Toolkit\Extension::getSubscribedDelegates()
     * @access public
     * @return void
     */
    public function getSubscribedDelegates()
    {
        return array(
            array(
                'page' => '/frontend/',
                'delegate' => 'FrontendOutputPostGenerate',
                'callback' => 'parseXML'
            ),
            array(
                'page' => '/frontend/',
                'delegate' => 'FrontendPreRenderHeaders',
                'callback' => 'setOutputTrigger'
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
     * install
     *
     * @access public
     * @return void
     */
    public function install()
    {
        Symphony::Configuration()->setArray(array('apipage' => self::$defaults));
        return Symphony::Configuration()->write();
    }

    /**
     * uninstall
     *
     * @access public
     * @return void
     */
    public function uninstall()
    {
        Symphony::Configuration()->remove('apipage');
        return Symphony::Configuration()->write();
    }

    /**
     * parseXML
     *
     * @param Mixed $context
     * @access public
     * @return void
     */
    public function parseXML($context)
    {
        if ($this->apipage && $this->apipage->trigger) {
            $context['output'] = $this->apipage->parse(new XmlToJSON($context['output']));
        }
    }

    /**
     * setOutputTrigger
     *
     * @param Mixed $context
     * @access public
     * @return void
     */
    public function setOutputTrigger($context)
    {
        $this->apipage = new APIPage(Frontend::Page(), Symphony::Configuration()->get('apipage'));

        return $this->apipage->setOutput(function () {
            throw new SymphonyErrorPage('format does not exist', 'API is having issues', 'generic', array('header' => 'HTTP/1.0 404 Not Found'));
        });
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
