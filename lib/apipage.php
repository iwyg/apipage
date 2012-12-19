<?php

/**
 * APIPage
 *
 * @package Symphony\Extensions\Lib
 * @version 1.0
 * @copyright 2012-2015 Soario Inc. <http://soario.com>
 * @author Thomas Appel <thomas@soario.com>
 * @license MIT
 */
class APIPage
{
    /**
     * page
     *
     * @var Page
     * @access protected
     */
    protected $page;

    /**
     * conf
     *
     * @var Array
     * @access protected
     */
    protected $conf;

    /**
     * trigger
     *
     * @var Boolean
     * @access public
     */
    public $trigger = false;

    /**
     * Content Type Map
     *
     * @var Array
     * @static
     * @access protected
     */
    protected static $mime = array(
        'xml'  => 'application/xml',
        'json' => 'application/json',
    );

    /**
     * __construct
     *
     * @param FrontendPage $FrontendPage
     * @param Mixed $page
     * @access public
     * @return void
     */
    public function __construct(FrontendPage $page, array $conf)
    {
        $this->page = $page;
        $this->conf = $conf;
    }

    /**
     * render
     *
     * @param InterfaceParser $parser
     * @param String $xml
     * @access public
     * @return string
     */
    public function parse(InterfaceParser $parser)
    {
        return $parser->parse();
    }

    /**
     * setOutput
     *
     * @param Mixed $format
     * @param Mixed $param
     * @access public
     * @return void
     */
    public function setOutput(Closure $errorHandler)
    {
        $params = $this->page->Params();

        if (in_array('API', $params['page-types'])) {

            $param   = $this->conf['param-selector'];
            $default = $this->conf['default-format'];

            $format  = isset($params[$param]) ? strtolower($params[$param]) : $default;

            if (!isset(static::$mime[$format])) {
                return $errorHandler();
            }

            $this->page->addHeaderToPage('Content-Type', self::$mime[$format]);

            if (strtolower($format) !== 'xml') {
                $this->trigger = true;
            }
        }
    }
}
