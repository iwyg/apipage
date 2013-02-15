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
 * @version 1.2
 * @copyright 2012-2015 Soario Inc. <http://soario.com>
 * @author Thomas Appel <thomas@soario.com>
 * @license MIT
 */
class Extension_APIPage extends Extension
{

    /**
     *  Default configuration
     *  @var Array
     */
    public static $defaults = array(
        'default-format' => 'json',
        'param-selector' => 'url-format',
        'jsonp-var'      => 'api_read',
        'header-override' => 'no'
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
            $output = $this->apipage->parse(new XmlToJSON($context['output']));
            $context['output'] = !is_null($this->apipage->jsonp) ?
                sprintf('var %s = %s;', $this->apipage->jsonp, $output) : $output;
        }
        if ($this->apipage) {
            header("Content-Length: " . mb_strlen($context['output'], 'latin1'));
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
        $page   = Frontend::Page();
        $params = $page->Params();

        if (in_array('API', $params['page-types'])) {

            $this->apipage = new APIPage($page, Symphony::Configuration()->get('apipage'));

            return $this->apipage->setOutput(function () {
                throw new SymphonyErrorPage('format does not exist', 'API is having issues', 'generic', array('header' => 'HTTP/1.0 406 Not Acceptable'));
            });
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


        $options = array(
            array('xml', ($selected === 'xml') ? true : false, 'xml'),
            array('json', ($selected === 'json') ? true : false, 'json'),
            array('jsonp', ($selected === 'jsonp') ? true : false, 'jsonp')
        );
        $select = Widget::Select('settings[apipage][default-format]', $options);

        $label = Widget::Label(__('default output format'), $select);
        $div->appendChild($label);

        if ($selected === 'jsonp') {

            $label = Widget::Label(__('JSONP variable name'), Widget::Input('settings[apipage][jsonp-var]',
                isset($conf['jsonp-var']) ? $conf['jsonp-var'] : 'api_read', 'text')
            );

            $div->appendChild($label);
        }

        $selector = Widget::Input('settings[apipage][param-selector]', $selector, 'text');

        $label = Widget::Label(__('format parameter'), $selector);

        $div->appendChild($label);

        $hidden = Widget::Input('settings[apipage][header-override]', 'no', 'hidden');
        $div->appendChild($hidden);

        $label = Widget::Label(__('Header override'),
            Widget::Input('settings[apipage][header-override]', 'yes', 'checkbox',
                (isset($conf['header-override']) && $conf['header-override'] === 'yes') ? array('checked' => 'checked') : array())
        );

        $help = new XMLElement('p', 'Allow HTTP Accept header to override default output format', array('class' => 'help'));

        $label->appendChild($help);

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

    /**
     * update
     *
     * @param mixed $previousVersion
     * @access public
     * @return mixed
     */
    public function update($previousVersion)
    {
        if (version_compare($previous_version, '0.1.7', '<')) {
            Symphony::Configuration()->set('jsonp-var', self::$defaults['jsonp-var'], 'apipage');
            Symphony::Configuration()->write();
        }

        if (version_compare($previous_version, '0.1.8', '<')) {
            Symphony::Configuration()->set('header-override', self::$defaults['header-override'], 'apipage');
            Symphony::Configuration()->write();
        }
        return true;
    }
}
