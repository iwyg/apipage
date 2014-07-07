<?php

require_once dirname(__FILE__) . '/lib/parserinterface.php';
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
        'default-format'         => 'json',
        'param-selector'         => 'url-format',
        'jsonp-var'              => 'api_read',
        'jsonp-callback'         => 'api_page',
        'header-override'        => 'no',
        'disable-content-length' => 'no'
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
                'callback' => 'process'
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
            array(
                'page' => '/frontend/',
                'delegate' => 'FrontendOutputPreGenerate',
                'callback' => 'registerHelper'
            ),
        );
    }

    /**
     * registerHelper
     *
     * @param mixed $context
     *
     * @access public
     * @return mixed
     */
    public function registerHelper($context)
    {
        require_once dirname(__FILE__).'/lib/xmltojsonhelper.php';
        require_once dirname(__FILE__).'/lib/apihelper.php';
        $context['page']->registerPHPFunction('ApiHelper::simpleJson');
        $context['page']->registerPHPFunction('ApiHelper::toJson');
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
     * process
     *
     * @param Mixed $context
     * @access public
     * @return void
     */
    public function process($context)
    {

        if (!$this->apipage) {
            return;
        }
        
        $output = '';
    
        if (false !== $this->apipage->trigger) {
            $output = $this->apipage->parse(new XmlToJSON((string)$context['output']));
            $context['output'] = !is_null($this->apipage->jsonp) ?
                sprintf('var %s = %s;', $this->apipage->jsonp, $output) :
                (
                    $this->apipage->hasCallback() ? sprintf("%s(%s);", $this->apipage->getCallback(), $output ) : $output
                );
        }

        $this->renderHeaders($output);
    }
    
    /**
     * @param string $etag
     * @return void
     */
    protected function renderHeaders($output = '')
    {
        $etag = (0 < strlen($output)) ? hash('md5', $output) : null;
        
        if ('no' === Symphony::Configuration()->get('disable-content-length', 'apipage') && true !== $this->apipage->isDebugging()) {
            header("Content-Length: " . mb_strlen($context['output'], 'latin1'));
        }
        
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) and $etag === $_SERVER['HTTP_IF_NONE_MATCH']) {
            header('Status: 304 Not Modified', true, 304)
            exit(0);
        }
        
        if (null !== $etag) {
            // set ETag for caching of static api data
            header('ETag: '. $etag);    
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

            $this->apipage = new APIPage($page, Symphony::Configuration()->get('apipage'), isset($_GET['debug']));

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


        $label = Widget::Label(__('JSONP variable name'), Widget::Input('settings[apipage][jsonp-var]',
            isset($conf['jsonp-var']) ? $conf['jsonp-var'] : static::$defaults['jsonp-var'], 'text')
        );

        $div->appendChild($label);

        $selector = Widget::Input('settings[apipage][param-selector]', $selector, 'text');

        $label = Widget::Label(__('format parameter'), $selector);

        $div->appendChild($label);

        $hidden = Widget::Input('settings[apipage][header-override]', 'no', 'hidden');
        $div->appendChild($hidden);

        $label = Widget::Label(null,
            Widget::Input('settings[apipage][header-override]', 'yes', 'checkbox',
                (isset($conf['header-override']) && $conf['header-override'] === 'yes') ? array('checked' => 'checked') : array())
        );

        $label->setValue(__('Header override'), false);

        $help = new XMLElement('p', __('Allow HTTP Accept header to override default output format'), array('class' => 'help'));
        $div->appendChild($label);
        $div->appendChild($help);


        $hidden = Widget::Input('settings[apipage][disable-content-length]', 'no', 'hidden');
        $div->appendChild($hidden);
        $label = Widget::Label(null,
            Widget::Input('settings[apipage][disable-content-length]', 'yes', 'checkbox',
                (isset($conf['disable-content-length']) && $conf['disable-content-length'] === 'yes') ? array('checked' => 'checked') : array())
            );
        $label->setValue(__('Disable Content Length output'), false);


        $div->appendChild($label);
        $help = new XMLElement('p', __('Disable content length on the response header'), array('class' => 'help'));
        $div->appendChild($help);

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
