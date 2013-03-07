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
     * trigger
     *
     * @var string|null
     */
    public $jsonp;

    /**
     * accept
     *
     * @var Mixed
     * @access protected
     */
    protected $accept;

    /**
     * callback
     *
     * @var string
     */
    protected $callback;

    /**
     * Content Type Map
     *
     * @var Array
     * @static
     * @access protected
     */
    protected static $mime = array(
        'xml'   => 'application/xml',
        'json'  => 'application/json',
        'jsonp' => 'application/javascript',
    );

    /**
     * map
     *
     * @var array
     */
    protected static $map = array(
        'xml'        => 'xml',
        'json'       => 'json',
        'jsonp'      => 'jsonp',
        'javascript' => 'jsonp'
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
     * @throws ErrorExeption
     */
    public function setOutput(Closure $errorHandler)
    {
        $params = $this->page->Params();

        $param   = $this->conf['param-selector'];
        $default = $this->conf['default-format'];

        $format  = isset($params[$param]) ? strtolower($params[$param]) : ($this->acceptHeader() ? $this->acceptHeader() : $default);

        if (!isset(static::$mime[$format])) {
            return $errorHandler();
        }


        if (strtolower($format) !== 'xml') {
            $this->trigger = true;
            $this->jsonp = $format === 'jsonp' ? $this->conf['jsonp-var'] : null;

            if (isset($_REQUEST['callback'])) {
                $this->callback = sprintf('%s', trim($_REQUEST['callback']));
                $format = 'jsonp';
            }
        }

        $this->page->addHeaderToPage('Content-Type', self::$mime[$format]);

    }

    /**
     * hasCallback
     *
     * @return boolean
     */
    public function hasCallback()
    {
        return !is_null($this->callback);
    }

    /**
     * getCallback
     *
     * @return mixed|string
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * getAcceptFormat
     *
     * @access public
     * @return boolean
     */
    public function getAcceptFormat()
    {
        return $this->accept;
    }

    protected function acceptHeader()
    {
        if (isset($this->conf['header-override']) && $this->conf['header-override'] === 'no') {
            return false;
        }

        if (isset($this->accept)) {
            return $this->accept;
        }
        $header = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;

        if (is_null($header)) {
            return false;
        }
        $accept = explode(',', preg_replace('/(;.*|\w+\/)/', null, $header));

        if (!empty($accept)) {
            foreach ($accept as $format) {
                if (array_key_exists($format, static::$map)) {
                    $this->accept = static::$map[$format];
                    return true;
                }
            }
        }
        return false;
    }

}
